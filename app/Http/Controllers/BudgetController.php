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

    /**
     * APPLY AI RECOMMENDATION
     */
    public function applyAI(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
        ]);

        // cek apakah kategori sudah punya budget bulan ini
        $existing = Budget::where('category_id', $request->category_id)
            ->whereDate('period_start', now()->startOfMonth())
            ->whereDate('period_end', now()->endOfMonth())
            ->first();

        if ($existing) {
            // update
            $existing->update([
                'amount' => $request->amount,
            ]);
        } else {
            // create
            Budget::create([
                'category_id' => $request->category_id,
                'amount' => $request->amount,
                'period_start' => now()->startOfMonth(),
                'period_end' => now()->endOfMonth(),
                'spent' => 0,
            ]);
        }

        return redirect()
            ->route('budgets.index')
            ->with('success', 'Budget berhasil diperbarui berdasarkan rekomendasi AI!');
    }
}
