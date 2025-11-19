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
    // 1. Kategorisasi Transaksi Cerdas
    public function suggestCategory(Request $request)
    {
        $description = strtolower($request->description);
        
        // Keyword mapping untuk kategori
        $categoryKeywords = [
            'Makanan & Minuman' => ['makan', 'minum', 'restoran', 'cafe', 'kopi', 'nasi', 'ayam', 'burger', 'pizza', 'bakso', 'soto', 'warteg', 'food', 'lunch', 'dinner', 'breakfast'],
            'Transportasi' => ['bensin', 'grab', 'gojek', 'taxi', 'bus', 'kereta', 'parkir', 'tol', 'ojek', 'uber', 'transport', 'fuel'],
            'Belanja' => ['belanja', 'beli', 'shopping', 'toko', 'mall', 'supermarket', 'indomaret', 'alfamart', 'tokopedia', 'shopee', 'lazada'],
            'Hiburan' => ['nonton', 'film', 'bioskop', 'game', 'netflix', 'spotify', 'youtube', 'concert', 'hiburan', 'main'],
            'Tagihan' => ['listrik', 'air', 'internet', 'wifi', 'pulsa', 'token', 'pln', 'pdam', 'telkom', 'indihome', 'bill'],
            'Kesehatan' => ['dokter', 'rumah sakit', 'obat', 'apotek', 'vitamin', 'medical', 'hospital', 'clinic', 'checkup'],
            'Pendidikan' => ['sekolah', 'kuliah', 'kursus', 'buku', 'les', 'training', 'seminar', 'education', 'course'],
            'Gaji' => ['gaji', 'salary', 'income', 'bonus', 'thr', 'komisi', 'honorarium'],
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

    // 2. Analisis Pola Pengeluaran
    public function analyzeSpendingPattern()
    {
        $lastMonth = now()->subMonth();
        
        // Analisis per kategori
        $categoryAnalysis = Transaction::select('category_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->where('type', 'expense')
            ->where('transaction_date', '>=', $lastMonth)
            ->with('category')
            ->groupBy('category_id')
            ->get();

        // Deteksi anomali (pengeluaran tidak biasa)
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

        // Tren pengeluaran (naik/turun)
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

    // 3. Rekomendasi Anggaran Adaptif
    public function recommendBudget()
    {
        $lastThreeMonths = now()->subMonths(3);
        
        // Hitung rata-rata pengeluaran per kategori
        $categoryAverages = Transaction::select('category_id', DB::raw('AVG(amount) as avg_amount'), DB::raw('SUM(amount) as total_amount'))
            ->where('type', 'expense')
            ->where('transaction_date', '>=', $lastThreeMonths)
            ->with('category')
            ->groupBy('category_id')
            ->get();

        $recommendations = [];
        foreach ($categoryAverages as $avg) {
            // Cek apakah sudah ada budget
            $existingBudget = Budget::where('category_id', $avg->category_id)
                ->whereDate('period_end', '>=', now())
                ->first();

            $recommendedAmount = $avg->total_amount / 3 * 1.1; // +10% buffer

            $recommendations[] = [
                'category' => $avg->category,
                'current_budget' => $existingBudget ? $existingBudget->amount : 0,
                'recommended_budget' => $recommendedAmount,
                'average_spending' => $avg->total_amount / 3,
                'has_budget' => $existingBudget ? true : false,
                'status' => $existingBudget 
                    ? ($existingBudget->amount < $recommendedAmount ? 'increase' : 'sufficient')
                    : 'create'
            ];
        }

        return view('ai.budget-recommendation', compact('recommendations'));
    }

    // 4. Pengingat Cerdas
    public function smartReminders()
    {
        $reminders = [];

        // 1. Budget hampir habis
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
                    'message' => "Your {$budget->category->name} budget is {$percentage}% used. Remaining: Rp " . number_format($budget->remaining(), 0, ',', '.'),
                    'icon' => '⚠️',
                    'color' => 'orange'
                ];
            } elseif ($percentage >= 100) {
                $reminders[] = [
                    'type' => 'budget_exceeded',
                    'priority' => 'high',
                    'title' => 'Budget Exceeded!',
                    'message' => "Your {$budget->category->name} budget has been exceeded by Rp " . number_format(abs($budget->remaining()), 0, ',', '.'),
                    'icon' => '🚨',
                    'color' => 'red'
                ];
            }
        }

        // 2. Savings goal deadline mendekat
        $savings = Saving::where('status', 'active')
            ->whereNotNull('target_date')
            ->get();

        foreach ($savings as $saving) {
            $daysLeft = now()->diffInDays($saving->target_date, false);
            if ($daysLeft > 0 && $daysLeft <= 30 && $saving->current_amount < $saving->target_amount) {
                $reminders[] = [
                    'type' => 'saving_deadline',
                    'priority' => 'medium',
                    'title' => 'Savings Goal Deadline Approaching',
                    'message' => "{$saving->name} deadline in {$daysLeft} days. You need Rp " . number_format($saving->remainingAmount(), 0, ',', '.') . " more.",
                    'icon' => '⏰',
                    'color' => 'blue'
                ];
            }
        }

        // 3. Pengeluaran tidak biasa
        $lastWeekAvg = Transaction::where('type', 'expense')
            ->where('transaction_date', '>=', now()->subWeeks(4))
            ->where('transaction_date', '<', now()->subWeek())
            ->avg('amount');

        $thisWeekTotal = Transaction::where('type', 'expense')
            ->where('transaction_date', '>=', now()->startOfWeek())
            ->sum('amount');

        if ($thisWeekTotal > $lastWeekAvg * 7 * 1.5) {
            $reminders[] = [
                'type' => 'unusual_spending',
                'priority' => 'high',
                'title' => 'Unusual Spending Detected',
                'message' => "Your spending this week is 50% higher than usual. Total: Rp " . number_format($thisWeekTotal, 0, ',', '.'),
                'icon' => '📊',
                'color' => 'purple'
            ];
        }

        // 4. Belum ada transaksi hari ini
        $todayTransactions = Transaction::whereDate('transaction_date', now())->count();
        if ($todayTransactions === 0 && now()->hour >= 18) {
            $reminders[] = [
                'type' => 'no_transaction',
                'priority' => 'low',
                'title' => 'No Transactions Today',
                'message' => "Don't forget to record your daily transactions!",
                'icon' => '📝',
                'color' => 'gray'
            ];
        }

        // Sort by priority
        usort($reminders, function ($a, $b) {
            $priority = ['high' => 3, 'medium' => 2, 'low' => 1];
            return $priority[$b['priority']] - $priority[$a['priority']];
        });

        return view('ai.reminders', compact('reminders'));
    }
}
