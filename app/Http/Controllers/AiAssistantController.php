<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;

class AiAssistantController extends Controller
{
    protected int $maxHistory = 20; // memory 20 pesan

    /* ============================================================
     * AYU — CHAT
     * - menerima request chat dari frontend
     * - menyimpan memory di session (last N)
     * - memanggil Gemini (safe) dan mengembalikan response
     * ============================================================ */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $apiKey = env('GOOGLE_AI_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'GOOGLE_AI_KEY is missing'], 400);
        }

        $userMessage = $request->message;

        // load & update session memory
        $history = session()->get('ayu_memory', []);
        $history[] = ['role' => 'user', 'text' => $userMessage];
        $history = array_slice($history, -$this->maxHistory);
        session()->put('ayu_memory', $history);

        // personality & prompt
        $ayuPersonality = "
Kamu adalah AYU, AI keuangan super pintar, cerewet & lucu.
Ayu WAJIB:
- jawab berdasarkan DATA
- kasih rekomendasi spesifik ('kurangi makan 12%', 'tambah budget transport 50k')
- gaya: gaul, cerewet, tapi peduli
- maksimal 6 kalimat
- jangan mengulang kalimat
- selalu pakai data dari financial context di bawah.
";

        $financialSummary = $this->buildFullFinancialContext();

        $prompt = $ayuPersonality . "\n\n";
        $prompt .= "===== FINANCIAL CONTEXT =====\n" . $financialSummary . "\n\n";
        $prompt .= "===== RIWAYAT CHAT =====\n";
        foreach ($history as $h) {
            $prompt .= strtoupper($h['role']) . ": " . $h['text'] . "\n";
        }
        $prompt .= "\nUSER: {$userMessage}\nAYU:";

        // call Gemini safely
        $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash-001:generateContent?key={$apiKey}";
        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'temperature' => 0.85,
                'maxOutputTokens' => 150,
            ],
        ];

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Gemini API failed',
                    'status' => $response->status(),
                    'body' => $response->body()
                ], 502);
            }

            $json = $response->json();
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Request to Gemini failed: ' . $e->getMessage()
            ], 502);
        }

        // extract text safely
        $reply = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$reply || trim($reply) === '') {
            return response()->json([
                'error' => 'Gemini response invalid',
                'raw' => $json
            ], 502);
        }

        // remove duplicated sentences, save assistant memory, return
        $reply = $this->cleanRepeats($reply);

        $history[] = ['role' => 'assistant', 'text' => $reply];
        $history = array_slice($history, -$this->maxHistory);
        session()->put('ayu_memory', $history);

        return response()->json([
            'reply' => explode("\n\n", $reply)
        ]);
    }

    /* ============================================================
     * PAGE: AI Budget Recommendation (no parameter)
     * - displays categories, frontend will fetch per-category data via AJAX
     * ============================================================ */
    public function page()
    {
        $categories = Category::orderBy('name')->get();
        return view('ai.budget-recommendation', compact('categories'));
    }

    /* ============================================================
     * AJAX: get budget recommendation for chosen category
     * - returns JSON: avg, current budget, super/normal/flex
     * ============================================================ */
    public function getBudget(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id'
        ]);

        $category = Category::find($request->category_id);

        // average spending last 3 months
        $avg = Transaction::where('category_id', $category->id)
            ->where('type', 'expense')
            ->whereDate('created_at', '>=', now()->subMonths(3))
            ->avg('amount') ?? 0;

        // current budget this month
        $budget = Budget::where('category_id', $category->id)
            ->whereDate('period_start', '<=', now())
            ->whereDate('period_end', '>=', now())
            ->first();

        $current = $budget ? (int) $budget->amount : 0;

        $super = (int) floor($avg * 0.8); // -20%
        $normal = (int) floor($avg);      // avg
        $flex = (int) floor($avg * 1.2);  // +20%

        return response()->json([
            'category' => $category->name,
            'avg' => (int) floor($avg),
            'current' => $current,
            'super' => $super,
            'normal' => $normal,
            'flex' => $flex,
        ]);
    }

    /* ============================================================
     * APPLY RECOMMENDATION (AJAX)
     * - update or create budget for current month
     * ============================================================ */
    public function applyRecommendation(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0'
        ]);

        $categoryId = $request->category_id;
        $amount = (float) $request->amount;

        $budget = Budget::where('category_id', $categoryId)
            ->whereDate('period_start', '<=', now())
            ->whereDate('period_end', '>=', now())
            ->first();

        if (!$budget) {
            $budget = Budget::create([
                'category_id' => $categoryId,
                'amount' => $amount,
                'spent' => 0,
                'period_start' => now()->startOfMonth(),
                'period_end' => now()->endOfMonth(),
            ]);
        } else {
            $budget->amount = $amount;
            $budget->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Budget updated by AI',
            'budget' => $budget
        ]);
    }

    /* ============================================================
     * AYU AUTO ALERT (DASHBOARD)
     * - returns quick alerts for dashboard auto-popup
     * ============================================================ */
    public function autoAlert()
    {
        $income = Transaction::where('type', 'income')->sum('amount');
        $expense = Transaction::where('type', 'expense')->sum('amount');

        $alerts = [];

        if ($income > 0 && $expense > $income) {
            $alerts[] = "Ayu liat-liat ya... total pengeluaran kamu LEBIH BESAR dari income 🤦‍♀️. Rem belanja dulu lah!";
        }

        if ($income == 0 && $expense > 0) {
            $alerts[] = "Belum ada income tapi uang udah keluar... kamu kuat? 😭 Ayu siap bantu atur ya.";
        }

        $top = Transaction::selectRaw("category_id, SUM(amount) as total")
            ->where('type', 'expense')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->first();

        if ($top) {
            $alerts[] = "Kategori paling boros kamu: {$top->category->name} (Rp " .
                number_format($top->total, 0, ',', '.') . ")";
        }

        $budgets = Budget::with('category')->get();
        foreach ($budgets as $b) {
            if ($b->spent > $b->amount) {
                $alerts[] = "Budget kategori {$b->category->name} udah overspend📛. Mau Ayu bantu atur ulang?";
            }
        }

        return response()->json([
            'status' => $alerts ? 'WARNING' : 'OK',
            'alerts' => $alerts
        ]);
    }

    /* ============================================================
     * BUILD FULL FINANCIAL CONTEXT
     * - summarized text used in prompts
     * ============================================================ */
    private function buildFullFinancialContext(): string
    {
        $income = Transaction::where('type', 'income')->sum('amount');
        $expense = Transaction::where('type', 'expense')->sum('amount');
        $balance = $income - $expense;

        // category expenses
        $categories = Category::with(['transactions' => function ($q) {
            $q->where('type', 'expense');
        }])->get();

        $categoryDetails = '';
        foreach ($categories as $cat) {
            $total = $cat->transactions->sum('amount');
            $categoryDetails .= "{$cat->name}: Rp " . number_format($total, 0, ',', '.') . "\n";
        }

        // budgets
        $budgets = Budget::with('category')->get();
        $budgetDetails = '';
        $overspendList = '';
        foreach ($budgets as $b) {
            $budgetDetails .= "{$b->category->name}: spent Rp " .
                number_format($b->spent, 0, ',', '.') .
                " / Rp " . number_format($b->amount, 0, ',', '.') . "\n";

            if ($b->spent > $b->amount) {
                $overspendList .= "- {$b->category->name}: overshoot Rp " .
                    number_format($b->spent - $b->amount, 0, ',', '.') . "\n";
            }
        }

        // recent transactions
        $recent = Transaction::latest()->take(5)->get();
        $recentList = '';
        foreach ($recent as $t) {
            $recentList .= "{$t->description} ({$t->type}) Rp " .
                number_format($t->amount, 0, ',', '.') . "\n";
        }

        // top spender
        $top = Transaction::selectRaw("category_id, SUM(amount) as total")
            ->where('type', 'expense')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->first();

        $topSpender = $top
            ? $top->category->name . " (Rp " . number_format($top->total, 0, ',', '.') . ")"
            : "-";

        return "
TOTAL INCOME: Rp " . number_format($income, 0, ',', '.') . "
TOTAL EXPENSE: Rp " . number_format($expense, 0, ',', '.') . "
BALANCE: Rp " . number_format($balance, 0, ',', '.') . "

CATEGORY EXPENSES:
{$categoryDetails}

BUDGET STATUS:
{$budgetDetails}

OVERSPEND LIST:
" . ($overspendList ?: "Tidak ada overspend") . "

RECENT TRANSACTIONS:
{$recentList}

TOP SPENDER:
{$topSpender}
";
    }

    /* ============================================================
     * AI POPUP (single short message via Gemini)
     * - returns single-line popup text
     * ============================================================ */
    public function generateAyuPopup(): string
    {
        $apiKey = env('GOOGLE_AI_KEY');

        // safe fallback without API key
        if (!$apiKey) {
            return "Ayu lagi ngantuk nih — cek pengeluaranmu ya!";
        }

        $income = Transaction::where('type', 'income')->sum('amount');
        $expense = Transaction::where('type', 'expense')->sum('amount');
        $balance = $income - $expense;

        $top = Transaction::selectRaw("category_id, SUM(amount) as total")
            ->where('type', 'expense')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->first();

        $topCat = $top ? $top->category->name . " (Rp " . number_format($top->total, 0, ',', '.') . ")" : "-";

        $prompt = "
Kamu adalah AYU, AI cerewet tapi peduli.

Tugasmu:
- Buat pesan popup 1 kalimat (<20 kata)
- Lucu, gaul, nyolot halus
- Berdasarkan data ini:

Income: Rp {$income}
Expense: Rp {$expense}
Balance: Rp {$balance}
Kategori boros: {$topCat}

Jangan pakai markdown.
1 kalimat saja.
";

        $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash-001:generateContent?key={$apiKey}";
        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'temperature' => 1.2,
                'maxOutputTokens' => 30,
            ],
        ];

        try {
            $res = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($res->failed()) {
                return "Ayu lagi sibuk, tapi cek pengeluaran ya!";
            }

            $json = $res->json();
            $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;

            return $text ? $this->cleanRepeats($text) : "Ayu bingung nih 😭 tapi kayaknya kamu harus hemat dikit.";
        } catch (\Throwable $e) {
            return "Ayu error nih, cek dompet dulu ya!";
        }
    }

    /* ============================================================
     * CLEAN REPEATS
     * - remove exact duplicate sentences
     * ============================================================ */
    private function cleanRepeats(string $text): string
    {
        $sentences = preg_split('/(?<=[.?!])\s+/', trim($text));
        $seen = [];
        $out = [];

        foreach ($sentences as $s) {
            $norm = strtolower(trim(preg_replace('/[^a-z0-9 ]/i', '', $s)));
            if ($norm === '') continue;
            if (!in_array($norm, $seen)) {
                $seen[] = $norm;
                $out[] = $s;
            }
        }

        return implode(' ', $out);
    }
}
