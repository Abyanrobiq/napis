<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index()
    {
        $budgets = Budget::with('category')->latest()->get();
        return view('budgets.index', compact('budgets'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('budgets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
        ]);

        Budget::create($request->all());

        return redirect()->route('budgets.index')->with('success', 'Budget berhasil dibuat');
    }

    public function edit(Budget $budget)
    {
        $categories = Category::all();
        return view('budgets.edit', compact('budget', 'categories'));
    }

    public function update(Request $request, Budget $budget)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
        ]);

        $budget->update($request->all());

        return redirect()->route('budgets.index')->with('success', 'Budget berhasil diupdate');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();
        return redirect()->route('budgets.index')->with('success', 'Budget berhasil dihapus');
    }
    public function applyAI(Request $request)
{
    Budget::create([
        'category_id' => $request->category_id,
        'amount' => $request->amount,
        'period_start' => now()->startOfMonth(),
        'period_end' => now()->endOfMonth(),
    ]);

    return redirect()->route('ai.budget-recommendation')
        ->with('success', 'AI Recommended budget applied successfully!');
}

}
