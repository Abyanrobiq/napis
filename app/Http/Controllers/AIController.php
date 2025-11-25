<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Saving;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AIController extends Controller
{
    // 1. Smart Category Suggestion
    public function suggestCategory(Request $request)
    {
        $description = strtolower($request->description);
        
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

    // 2. Pattern Analysis
    public function analyzeSpendingPattern()
    {
        $lastMonth = now()->subMonth();

        $categoryAnalysis = Transaction::select('category_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->where('type', 'expense')
            ->where('transaction_date', '>=', $lastMonth)
            ->with('category')
            ->groupBy('category_id')
            ->get();

        $anomalies = [];
        foreach ($categoryAnalysis as $analysis) {
            $avgAmount = $analysis->total / $analysis->count;
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

        return view('ai.analysis', compact(
            'categoryAnalysis',
            'anomalies',
            'trend',
            'trendPercentage',
            'thisMonthExpense',
            'lastMonthExpense'
        ));
    }

    // 3. Budget Recommendation (with overspending + 3 lifestyle modes)
    public function recommendBudget()
    {
        $lastThreeMonths = now()->subMonths(3);

        $categoryData = Transaction::select(
                'category_id',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('MIN(transaction_date) as first_date'),
                DB::raw('MAX(transaction_date) as last_date')
            )
            ->where('type', 'expense')
            ->where('transaction_date', '>=', $lastThreeMonths)
            ->with('category')
            ->groupBy('category_id')
            ->get();

        $recommendations = [];

        foreach ($categoryData as $data) {
            $overspending = false;
            $overspendAmount = 0;
            $recommendedOverspendBudget = null;


            // hitung bulan real
            $months = \Carbon\Carbon::parse($data->first_date)
                ->startOfMonth()
                ->diffInMonths(
                    \Carbon\Carbon::parse($data->last_date)->endOfMonth()
                ) + 1;

            $months = max(1, $months);

            $averageSpending = $data->total_amount / $months;

            $existingBudget = Budget::where('category_id', $data->category_id)
                ->whereDate('period_end', '>=', now())
                ->first();

            $currentMonthSpending = Transaction::where('category_id', $data->category_id)
                ->where('type', 'expense')
                ->whereMonth('transaction_date', now()->month)
                ->sum('amount');
            
            if ($overspending) {
                 return redirect()->route('ai.budget-recommendation')
                 ->with('overspend', 'Overspending detected! Consider adjusting your budget.');
}


            $overspending = false;
            $overspendAmount = 0;
            $recommendedOverspendBudget = null;

            // Hanya tampilkan overspend jika budget-nya sudah lebih besar dari average
            if ($existingBudget && $existingBudget->amount >= $averageSpending && $currentMonthSpending > $existingBudget->amount) {
                $overspending = true;
                $overspendAmount = $currentMonthSpending - $existingBudget->amount;

                // rekomendasi budget baru jika overspending
                $recommendedOverspendBudget = $averageSpending * (
                    $overspendAmount > $existingBudget->amount * 0.20 
                        ? 1.20  // overspend parah
                        : 1.10  // overspend ringan
                );
            }

            // 3 MODE GAYA HIDUP
            $superHemat = $averageSpending * 0.80;
            $hemat = $averageSpending * 1.00;
            $nyantai = $averageSpending * 1.20;

            $recommendations[] = [
                'category' => $data->category,
                'current_budget' => $existingBudget->amount ?? 0,
                'average_spending' => $averageSpending,

                // 🔥 STATUS dikembalikan ke UI lama
                'status' => $existingBudget 
                    ? ($existingBudget->amount < $averageSpending ? 'increase' : 'sufficient')
                    : 'create',

                    'has_budget' => $existingBudget ? true : false,

                    'recommended_budget' => $averageSpending * 1.10, // buffer 10%



                // 3 gaya hidup
                'super_hemat' => $superHemat,
                'hemat' => $hemat,
                'nyantai' => $nyantai,

                // overspending
                'overspending' => $overspending,
                'overspend_amount' => $overspendAmount,
                'recommended_if_overspend' => $recommendedOverspendBudget,
            ];
        }

        return view('ai.budget-recommendation', compact('recommendations'));
    }

    // 4. Smart Reminders
    public function smartReminders()
    {
        $reminders = [];

        // budget alerts
        $budgets = Budget::with('category')
            ->whereDate('period_end', '>=', now())
            ->get();

        foreach ($budgets as $budget) {
            $percentage = ($budget->spent / $budget->amount) * 100;
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
        // 2. Overspending terlalu besar (lebih dari 30% total budget)
$highOverspend = Transaction::where('type', 'expense')
    ->whereMonth('transaction_date', now()->month)
    ->sum('amount');

$activeBudgetsTotal = Budget::whereDate('period_end', '>=', now())->sum('amount');

if ($highOverspend > $activeBudgetsTotal * 1.30) {
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

    // sort by priority
usort($reminders, function ($a, $b) {
    $priority = ['high' => 3, 'medium' => 2, 'low' => 1];
    return $priority[$b['priority']] - $priority[$a['priority']];
}); 
        return view('ai.reminders', compact('reminders'));
    }
    
}
