<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Saving;
use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $balance = Setting::get('initial_balance', 0);
        $totalIncome = Transaction::where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('type', 'expense')->sum('amount');
        $currentBalance = $balance + $totalIncome - $totalExpense;

        $budgets = Budget::with('category')
            ->whereDate('period_start', '<=', now())
            ->whereDate('period_end', '>=', now())
            ->get();

        $recentTransactions = Transaction::with('category')
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get();

        $savings = Saving::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $totalSavings = Saving::sum('current_amount');

        return view('dashboard', compact('currentBalance', 'budgets', 'recentTransactions', 'savings', 'totalSavings'));
    }

    public function setInitialBalance(Request $request)
    {
        $request->validate([
            'balance' => 'required|numeric|min:0'
        ]);

        Setting::set('initial_balance', $request->balance);

        return redirect()->route('dashboard')->with('success', 'Saldo awal berhasil diatur');
    }
}
