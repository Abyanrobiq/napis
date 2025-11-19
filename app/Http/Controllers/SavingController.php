<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use Illuminate\Http\Request;

class SavingController extends Controller
{
    public function index()
    {
        $savings = Saving::orderBy('created_at', 'desc')->get();
        return view('savings.index', compact('savings'));
    }

    public function create()
    {
        return view('savings.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_amount' => 'required|numeric|min:0',
            'current_amount' => 'nullable|numeric|min:0',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'target_date' => 'nullable|date',
            'status' => 'required|in:active,completed,paused',
        ]);

        Saving::create($request->all());

        return redirect()->route('savings.index')->with('success', 'Saving goal created successfully!');
    }

    public function edit(Saving $saving)
    {
        return view('savings.edit', compact('saving'));
    }

    public function update(Request $request, Saving $saving)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_amount' => 'required|numeric|min:0',
            'current_amount' => 'nullable|numeric|min:0',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'target_date' => 'nullable|date',
            'status' => 'required|in:active,completed,paused',
        ]);

        $saving->update($request->all());

        return redirect()->route('savings.index')->with('success', 'Saving goal updated successfully!');
    }

    public function destroy(Saving $saving)
    {
        $saving->delete();
        return redirect()->route('savings.index')->with('success', 'Saving goal deleted successfully!');
    }

    // Method untuk menambah tabungan
    public function addAmount(Request $request, Saving $saving)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $saving->current_amount += $request->amount;
        
        // Auto update status jika sudah tercapai
        if ($saving->current_amount >= $saving->target_amount) {
            $saving->status = 'completed';
        }
        
        $saving->save();

        return redirect()->route('savings.index')->with('success', 'Amount added successfully!');
    }

    // Method untuk mengurangi tabungan
    public function withdrawAmount(Request $request, Saving $saving)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        if ($request->amount > $saving->current_amount) {
            return back()->withErrors(['amount' => 'Withdrawal amount cannot exceed current amount']);
        }

        $saving->current_amount -= $request->amount;
        
        // Update status jika tidak lagi completed
        if ($saving->current_amount < $saving->target_amount && $saving->status === 'completed') {
            $saving->status = 'active';
        }
        
        $saving->save();

        return redirect()->route('savings.index')->with('success', 'Amount withdrawn successfully!');
    }
}
