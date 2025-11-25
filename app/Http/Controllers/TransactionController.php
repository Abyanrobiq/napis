<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('category', 'budget')->latest('transaction_date')->paginate(20);
        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $categories = Category::all();
        $budgets = Budget::with('category')
            ->whereDate('period_start', '<=', now())
            ->whereDate('period_end', '>=', now())
            ->get();
        return view('transactions.create', compact('categories', 'budgets'));
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'budget_id' => 'nullable|exists:budgets,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:income,expense',
            'transaction_date' => 'required|date',
        ]);

        $transaction = Transaction::create($request->all());

        // Update budget spent jika transaksi adalah expense dan ada budget
        if ($request->type === 'expense' && $request->budget_id) {
            $budget = Budget::find($request->budget_id);
            $budget->spent += $request->amount;
            $budget->save();
        }

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil ditambahkan');
    }

    public function edit(Transaction $transaction)
    {
        $categories = Category::all();
        $budgets = Budget::with('category')
            ->whereDate('period_start', '<=', now())
            ->whereDate('period_end', '>=', now())
            ->get();
        return view('transactions.edit', compact('transaction', 'categories', 'budgets'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'budget_id' => 'nullable|exists:budgets,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:income,expense',
            'transaction_date' => 'required|date',
        ]);

        // Kembalikan spent budget lama jika ada
        if ($transaction->type === 'expense' && $transaction->budget_id) {
            $oldBudget = Budget::find($transaction->budget_id);
            $oldBudget->spent -= $transaction->amount;
            $oldBudget->save();
        }

        $transaction->update($request->all());

        // Update budget spent baru
        if ($request->type === 'expense' && $request->budget_id) {
            $budget = Budget::find($request->budget_id);
            $budget->spent += $request->amount;
            $budget->save();
        }

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil diupdate');
    }

    public function destroy(Transaction $transaction)
    {
        // Kembalikan spent budget jika ada
        if ($transaction->type === 'expense' && $transaction->budget_id) {
            $budget = Budget::find($transaction->budget_id);
            $budget->spent -= $transaction->amount;
            $budget->save();
        }

        $transaction->delete();
        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dihapus');
    }
    
}
