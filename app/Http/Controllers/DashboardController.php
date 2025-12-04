<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Budget;
use App\Models\Transaction;
use App\Models\Saving;
use App\Http\Controllers\AiAssistantController;

class DashboardController extends Controller
{
    public function index()
    {
        /* ============================
         * 1) Ambil data budget
         * ============================ */
        $budgets = Budget::with('category')->get();

        /* ============================
         * 2) Deteksi overspend kategori
         * ============================ */
        $overspends = [];

        foreach ($budgets as $b) {
            if ($b->spent > $b->amount) {
                $overspends[] = [
                    'category_id' => $b->category_id,   // ← WAJIB DITAMBAH
                    'category'    => $b->category->name,
                    'spent'       => $b->spent,
                    'budget'      => $b->amount,
                    'overspent'   => $b->spent - $b->amount
                ];
            }
        }

        if (!empty($overspends)) {
            session()->flash('overspend', $overspends[0]);
        }

        /* ============================
         * 3) Popup AI otomatis
         * ============================ */
        try {
            $ayuPopup = app(AiAssistantController::class)->generateAyuPopup();
            session()->flash('ayu_popup', $ayuPopup);
        } catch (\Exception $e) {
            session()->flash('ayu_popup', "Ayu lagi error, tapi kamu tetep boros 😭💸");
        }

        /* ============================
         * 4) Hitung keuangan
         * ============================ */
        $balance        = Setting::get('initial_balance', 0);
        $totalIncome    = Transaction::where('type', 'income')->sum('amount');
        $totalExpense   = Transaction::where('type', 'expense')->sum('amount');
        $currentBalance = $balance + $totalIncome - $totalExpense;

        /* ============================
         * 5) Data dashboard lainnya
         * ============================ */
        $recentTransactions = Transaction::latest()->take(5)->get();
        $savings = Saving::all();
        $totalSavings = $savings->sum('current_amount') ?? 0;

        return view('dashboard', compact(
            'currentBalance',
            'recentTransactions',
            'budgets',
            'savings',
            'totalSavings'
        ));
    }
}
