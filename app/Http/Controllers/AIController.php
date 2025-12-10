<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Saving;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AIController extends Controller
{
    // ----------------------------
    // 1. Smart Category Suggestion
    // ----------------------------
    public function suggestCategory(Request $request)
    {
        $description = strtolower($request->description ?? '');

        $categoryKeywords = [
            'Makanan & Minuman' => ['makan','minum','restoran','cafe','kopi','nasi','ayam','burger','pizza','bakso','soto','warteg','food','lunch','dinner','breakfast'],
            'Transportasi' => ['bensin','grab','gojek','taxi','bus','kereta','parkir','tol','ojek','uber','transport','fuel'],
            'Belanja' => ['belanja','beli','shopping','toko','mall','supermarket','indomaret','alfamart','tokopedia','shopee','lazada'],
            'Hiburan' => ['nonton','film','bioskop','game','netflix','spotify','youtube','concert','hiburan','main'],
            'Tagihan' => ['listrik','air','internet','wifi','pulsa','token','pln','pdam','telkom','indihome','bill'],
            'Kesehatan' => ['dokter','rumah sakit','obat','apotek','vitamin','medical','hospital','clinic','checkup'],
            'Pendidikan' => ['sekolah','kuliah','kursus','buku','les','training','seminar','education','course'],
            'Gaji' => ['gaji','salary','income','bonus','thr','komisi','honorarium'],
        ];

        $suggestedCategory = null;
        $maxScore = 0;

        foreach ($categoryKeywords as $categoryName => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($description, $keyword)) {
                    $score++;
                }
            }
            if ($score > $maxScore) {
                $maxScore = $score;
                $category = Category::where('name', $categoryName)->first();
                if ($category) {
                    $suggestedCategory = $category;
                }
            }
        }

        return response()->json([
            'suggested_category' => $suggestedCategory,
            'confidence' => $maxScore > 0 ? min($maxScore * 20, 100) : 0
        ]);
    }

    // ---------------------------------------------------
    // 2. Analyze Spending Pattern + Prediction + Suggest
    // ---------------------------------------------------
    public function analyzeSpendingPattern()
    {
        $lastMonth = now()->subMonth();

        // CATEGORY ANALYSIS (last 30 days)
        $categoryAnalysis = Transaction::select(
                'category_id',
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->where('type', 'expense')
            ->where('transaction_date', '>=', $lastMonth)
            ->with('category')
            ->groupBy('category_id')
            ->get();

        // ANOMALY DETECTION
        $anomalies = [];
        foreach ($categoryAnalysis as $analysis) {
            $avgAmount = $analysis->total / max(1, $analysis->count);
            $recentTransactions = Transaction::where('category_id', $analysis->category_id)
                ->where('type', 'expense')
                ->where('transaction_date', '>=', now()->subDays(7))
                ->get();

            foreach ($recentTransactions as $transaction) {
                if ($transaction->amount > $avgAmount * 2) {
                    $anomalies[] = [
                        'transaction' => $transaction,
                        'average' => $avgAmount,
                        'difference' => $transaction->amount - $avgAmount
                    ];
                }
            }
        }

        // THIS & LAST MONTH totals
        $thisMonthExpense = Transaction::where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        $lastMonthExpense = Transaction::where('type', 'expense')
            ->whereMonth('transaction_date', $lastMonth->month)
            ->sum('amount');

        $trend = $thisMonthExpense > $lastMonthExpense ? 'increasing' : 'decreasing';
        $trendPercentage = $lastMonthExpense > 0
            ? (($thisMonthExpense - $lastMonthExpense) / $lastMonthExpense) * 100
            : 0;

        // -----------------------------
        // PREDICTION: linear regression from last 6 months
        // -----------------------------
        $months = [];
        $values = [];

        // label 1..6 meaning 6 months ago -> last month
        for ($i = 6; $i >= 1; $i--) {
            $target = now()->subMonths($i);
            $months[] = 7 - $i; // 1..6
            $values[] = Transaction::where('type', 'expense')
                ->whereYear('transaction_date', $target->year)
                ->whereMonth('transaction_date', $target->month)
                ->sum('amount');
        }

        if (count($months) >= 2) {
            $coef = $this->linearRegression($months, $values);
            $nextMonthPrediction = (float) round($coef['a'] * 7 + $coef['b'], 0);
            $nextMonthPrediction = max(0, $nextMonthPrediction);
        } else {
            $nextMonthPrediction = 0;
        }

        // -----------------------------
        // SUGGESTIONS: which categories to cut
        // -----------------------------
        $categoryLastMonth = Transaction::select('category_id', DB::raw('SUM(amount) as total'))
            ->where('type', 'expense')
            ->whereYear('transaction_date', $lastMonth->year)
            ->whereMonth('transaction_date', $lastMonth->month)
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->get();

        $categorySuggestions = [];
        $required_cut = 0;

        if ($nextMonthPrediction > $thisMonthExpense) {
            $required_cut = $nextMonthPrediction - $thisMonthExpense;
        }

        if ($required_cut > 0 && $categoryLastMonth->sum('total') > 0) {
            $top = $categoryLastMonth->take(5);
            $sumTop = $top->sum('total') ?: 1;

            foreach ($top as $cat) {
                $share = $cat->total / $sumTop;
                $cut_amount = (float) round($required_cut * $share);
                $percent_of_category = $cat->total > 0 ? ($cut_amount / $cat->total) * 100 : 0;

                $categorySuggestions[] = [
                    'category_id' => $cat->category_id,
                    'category_name' => $cat->category->name ?? 'Unknown',
                    'last_month_total' => (float) $cat->total,
                    'cut_amount' => $cut_amount,
                    'percent_reduction' => round($percent_of_category, 1),
                    'share_of_top' => round($share * 100, 1)
                ];
            }
        } else {
            // no required cut — show top 3 as watch list
            $top_simple = $categoryLastMonth->take(3);
            foreach ($top_simple as $cat) {
                $categorySuggestions[] = [
                    'category_id' => $cat->category_id,
                    'category_name' => $cat->category->name ?? 'Unknown',
                    'last_month_total' => (float) $cat->total,
                    'cut_amount' => 0,
                    'percent_reduction' => 0,
                    'share_of_top' => round(($cat->total / max(1, $categoryLastMonth->sum('total'))) * 100, 1)
                ];
            }
        }

        // return view with all data
        return view('ai.analysis', compact(
            'categoryAnalysis',
            'anomalies',
            'trend',
            'trendPercentage',
            'thisMonthExpense',
            'lastMonthExpense',
            'months',
            'values',
            'nextMonthPrediction',
            'categorySuggestions'
        ));
    }

    /**
     * Linear regression helper: returns ['a' => slope, 'b' => intercept]
     */
    private function linearRegression($x, $y)
    {
        $n = count($x);
        $sumX = array_sum($x);
        $sumY = array_sum($y);

        $sumXY = 0;
        $sumX2 = 0;
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
        }

        $den = ($n * $sumX2 - $sumX * $sumX);
        if ($den == 0) {
            return ['a' => 0, 'b' => $sumY / max(1, $n)];
        }

        $a = ($n * $sumXY - $sumX * $sumY) / $den;
        $b = ($sumY - $a * $sumX) / $n;

        return ['a' => $a, 'b' => $b];
    }

    // ---------------------------------------------------
    // 3. recommendBudget() and smartReminders() kept as previous
    //    (if you want I can paste them too — but they're unchanged)
    // ---------------------------------------------------
    public function recommendBudget()
    {
        $categories = Category::all();

        $lastMonth = now()->subMonth();

        $categoryData = Transaction::select(
                'category_id',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('MIN(transaction_date) as first_date'),
                DB::raw('MAX(transaction_date) as last_date')
            )
            ->where('type', 'expense')
            ->where('transaction_date', '>=', $lastMonth)
            ->with('category')
            ->groupBy('category_id')
            ->get();

        $recommendations = [];

        foreach ($categoryData as $data) {
            // Calculate average spending from last month data
            $averageSpending = $data->total_amount; // Since we're using 1 month data now
            
            // Get existing budget for this category
            $existingBudget = Budget::where('category_id', $data->category_id)
                ->whereDate('period_end', '>=', now())
                ->first();

            // Get current month spending to check overspending
            $currentMonthSpending = Transaction::where('category_id', $data->category_id)
                ->where('type', 'expense')
                ->whereMonth('transaction_date', now()->month)
                ->sum('amount');

            // Determine budget status and recommendations
            $overspending = false;
            $overspendAmount = 0;
            
            if ($existingBudget && $currentMonthSpending > $existingBudget->amount) {
                $overspending = true;
                $overspendAmount = $currentMonthSpending - $existingBudget->amount;
            }

            // Smart budget recommendations based on spending patterns
            $superHemat = round($averageSpending * 0.75); // 25% reduction for aggressive saving
            $hemat = round($averageSpending * 0.90);      // 10% reduction for moderate saving
            $normal = round($averageSpending);            // Based on actual spending
            $nyantai = round($averageSpending * 1.15);    // 15% buffer for flexibility

            // Determine recommended budget based on current situation
            $recommendedBudget = $normal;
            $budgetStatus = 'create';
            $budgetAdvice = '';

            if ($existingBudget) {
                $currentBudget = $existingBudget->amount;
                
                if ($overspending) {
                    // If overspending, recommend higher budget or suggest spending reduction
                    if ($overspendAmount > $currentBudget * 0.20) {
                        // Significant overspending (>20%), suggest budget increase
                        $recommendedBudget = $nyantai;
                        $budgetStatus = 'increase_significant';
                        $budgetAdvice = 'Overspending signifikan terdeteksi. Pertimbangkan untuk menaikkan budget atau kurangi pengeluaran.';
                    } else {
                        // Minor overspending, suggest slight increase
                        $recommendedBudget = round($averageSpending * 1.05);
                        $budgetStatus = 'increase_minor';
                        $budgetAdvice = 'Sedikit overspending. Budget dinaikkan sedikit untuk fleksibilitas.';
                    }
                } elseif ($currentBudget > $averageSpending * 1.25) {
                    // Budget too high compared to actual spending
                    $recommendedBudget = $normal;
                    $budgetStatus = 'decrease';
                    $budgetAdvice = 'Budget saat ini terlalu tinggi. Bisa dikurangi sesuai pola spending aktual.';
                } elseif ($currentBudget < $averageSpending * 0.90) {
                    // Budget too low
                    $recommendedBudget = $normal;
                    $budgetStatus = 'increase';
                    $budgetAdvice = 'Budget saat ini terlalu rendah. Disarankan dinaikkan sesuai pola spending.';
                } else {
                    // Budget is appropriate
                    $recommendedBudget = $currentBudget;
                    $budgetStatus = 'sufficient';
                    $budgetAdvice = 'Budget saat ini sudah sesuai dengan pola spending Anda.';
                }
            } else {
                $budgetAdvice = 'Belum ada budget untuk kategori ini. Disarankan membuat budget berdasarkan pola spending.';
            }

            $recommendations[] = [
                'category' => $data->category,
                'current_budget' => $existingBudget->amount ?? 0,
                'average_spending' => $averageSpending,
                'current_month_spending' => $currentMonthSpending,
                'status' => $budgetStatus,
                'has_budget' => (bool) $existingBudget,
                'recommended_budget' => $recommendedBudget,
                'super_hemat' => $superHemat,
                'hemat' => $hemat,
                'normal' => $normal,
                'nyantai' => $nyantai,
                'overspending' => $overspending,
                'overspend_amount' => $overspendAmount,
                'advice' => $budgetAdvice,
                'savings_potential' => max(0, $averageSpending - $superHemat), // Potential savings if using super hemat
            ];
        }

        return view('ai.budget-recommendation', [
            'recommendations' => $recommendations,
            'categories' => $categories
        ]);
    }

    public function smartReminders()
    {
        $reminders = [];

        $budgets = Budget::with('category')
            ->whereDate('period_end', '>=', now())
            ->get();

        foreach ($budgets as $budget) {
            $percentage = ($budget->spent / max(1, $budget->amount)) * 100;
            if ($percentage >= 80 && $percentage < 100) {
                $reminders[] = [
                    'type' => 'budget_warning',
                    'priority' => 'medium',
                    'title' => 'Budget Almost Exceeded',
                    'message' => "Your {$budget->category->name} budget is {$percentage}% used."
                ];
            } elseif ($percentage >= 100) {
                $reminders[] = [
                    'type' => 'budget_exceeded',
                    'priority' => 'high',
                    'title' => 'Budget Exceeded!',
                    'message' => "You exceeded the {$budget->category->name} budget!"
                ];
            }
        }

        $highOverspend = Transaction::where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        $activeBudgetsTotal = Budget::whereDate('period_end', '>=', now())->sum('amount');

        if ($activeBudgetsTotal > 0 && $highOverspend > $activeBudgetsTotal * 1.30) {
            $reminders[] = [
                'type' => 'overspend_alert',
                'priority' => 'high',
                'title' => 'Overspending Too Much!',
                'message' => 'Your spending has exceeded your budgets by more than 30%. Consider adjusting your budget with AI recommendations.',
                'cta' => route('ai.budget-recommendation'),
                'icon' => '🚨',
                'color' => 'red'
            ];
        }

        usort($reminders, function ($a, $b) {
            $priority = ['high' => 3, 'medium' => 2, 'low' => 1];
            return $priority[$b['priority']] - $priority[$a['priority']];
        });

        return view('ai.reminders', compact('reminders'));
    }
}
