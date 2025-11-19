<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Saving;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month'); // month, week, year
        $startDate = $this->getStartDate($period);
        $endDate = now();

        // Summary data
        $totalIncome = Transaction::where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $totalExpense = Transaction::where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $netIncome = $totalIncome - $totalExpense;

        // Expense by category
        $expenseByCategory = Transaction::select('category_id', DB::raw('SUM(amount) as total'))
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with('category')
            ->groupBy('category_id')
            ->orderBy('total', 'desc')
            ->get();

        // Income by category
        $incomeByCategory = Transaction::select('category_id', DB::raw('SUM(amount) as total'))
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with('category')
            ->groupBy('category_id')
            ->orderBy('total', 'desc')
            ->get();

        // Daily transactions trend
        $dailyTrend = Transaction::select(
            DB::raw('DATE(transaction_date) as date'),
            DB::raw('SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income'),
            DB::raw('SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense')
        )
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Budget performance
        $budgetPerformance = Budget::with('category')
            ->whereDate('period_start', '<=', $endDate)
            ->whereDate('period_end', '>=', $startDate)
            ->get();

        // Savings progress
        $savingsProgress = Saving::where('status', 'active')->get();

        return view('reports.index', compact(
            'totalIncome',
            'totalExpense',
            'netIncome',
            'expenseByCategory',
            'incomeByCategory',
            'dailyTrend',
            'budgetPerformance',
            'savingsProgress',
            'period'
        ));
    }

    private function getStartDate($period)
    {
        return match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
    }

    public function export(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);
        $endDate = now();

        $transactions = Transaction::with('category', 'budget')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'desc')
            ->get();

        $csv = "Date,Type,Category,Description,Amount,Budget\n";
        foreach ($transactions as $transaction) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s\n",
                $transaction->transaction_date->format('Y-m-d'),
                $transaction->type,
                $transaction->category->name,
                str_replace(',', ';', $transaction->description),
                $transaction->amount,
                $transaction->budget ? $transaction->budget->category->name : '-'
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="financial-report-' . $period . '.csv"');
    }
}
