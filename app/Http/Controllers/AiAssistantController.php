<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;

class AiAssistantController extends Controller
{
    protected int $maxHistory = 20;

    /* ============================================================
     * MAIN CHAT — Natural Level 3 (tanpa keyword)
     * ============================================================ */
    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string']);
        $userMsg = trim($request->message);
        $lower   = strtolower($userMsg);

        // memory system
        $history = session()->get('ayu_memory', []);
        $history[] = ['role' => 'user', 'text' => $userMsg];
        $history = array_slice($history, -$this->maxHistory);
        session()->put('ayu_memory', $history);

        // detect finance context (improved)
        $isFinance = $this->detectFinanceIntent($lower, $history);

        if ($isFinance) {
            $reply = $this->generateFinanceReply($lower);
        } else {
            $reply = $this->generateNaturalGeneralReply($userMsg);
        }

        // save reply
        $history[] = ['role' => 'assistant', 'text' => $reply];
        session()->put('ayu_memory', $history);

        return response()->json([
            'reply' => [$reply]
        ]);
    }

    /* ============================================================
     * FINANCE DETECTION — improved dengan context & action words
     * ============================================================ */
    private function detectFinanceIntent(string $msg, array $history): bool
    {
        // Direct finance keywords
        $directKeywords = [
            'uang','keuangan','budget','anggaran','tabungan','saving','saldo',
            'gaji','pemasukan','pengeluaran','income','expense','laporan',
            'hutang','utang','bayar','transaksi','spending','belanja'
        ];

        foreach ($directKeywords as $keyword) {
            if (str_contains($msg, $keyword)) return true;
        }

        // Action words yang biasa untuk finance (ini yang kurang!)
        $actionKeywords = [
            'recap','rekomendasi','saran','analisa','cek','lihat','pantau',
            'dikurangi','hemat','irit','kontrol','jebol','overspend','over',
            'minus','aman','bahaya','boros','nabung'
        ];

        foreach ($actionKeywords as $keyword) {
            if (str_contains($msg, $keyword)) {
                // Jika ada action keyword, cek apakah recent context finance
                return $this->hasRecentFinanceContext($history);
            }
        }

        return false;
    }

    /* ============================================================
     * CEK CONTEXT PERCAKAPAN TERAKHIR
     * ============================================================ */
    private function hasRecentFinanceContext(array $history): bool
    {
        // Cek 3 pesan terakhir, apakah ada pembahasan finance
        $recentMessages = array_slice($history, -6); // 3 pairs of user-assistant
        
        $financeIndicators = [
            'pemasukan','pengeluaran','saldo','budget','anggaran','kategori',
            'overspend','jebol','limit','rp ','rp.','rupiah'
        ];

        foreach ($recentMessages as $msg) {
            $text = strtolower($msg['text']);
            foreach ($financeIndicators as $indicator) {
                if (str_contains($text, $indicator)) {
                    return true;
                }
            }
        }

        return false;
    }

    /* ============================================================
     * AI FINANCIAL RESPONSE (improved)
     * ============================================================ */
    private function generateFinanceReply(string $msg): string
    {
        $income  = Transaction::where('type','income')->sum('amount');
        $expense = Transaction::where('type','expense')->sum('amount');
        $balance = $income - $expense;

        $categories = Category::with(['transactions' => fn($q) => $q->where('type','expense')])->get();

        // Kalau diminta recap/rekomendasi langsung kasih detail
        $wantsRecap = str_contains($msg, 'recap') || str_contains($msg, 'rekomendasi') || 
                      str_contains($msg, 'saran') || str_contains($msg, 'dikurangi');

        if ($income == 0 && $expense == 0) {
            return "Ayu belum nemu data pemasukan atau pengeluaran sama sekali 😅. Coba masukin transaksi dulu ya!";
        }

        $reply = "";

        if ($wantsRecap) {
            $reply .= "Oke Ayu buatin recap-nya ya! 📊\n\n";
        } else {
            $reply .= "Oke, Ayu cek datanya dulu yaa... 🧐\n\n";
        }

        $reply .= "💰 Total pemasukan: Rp ".number_format($income,0,',','.')."\n";
        $reply .= "💸 Total pengeluaran: Rp ".number_format($expense,0,',','.')."\n";

        if ($balance < 0) {
            $reply .= "⚠️ Saldo kamu minus Rp ".number_format(abs($balance),0,',','.').". Lumayan bahaya nih 😭\n\n";
        } else {
            $reply .= "✅ Saldo tersisa Rp ".number_format($balance,0,',','.')."\n\n";
        }

        // Breakdown per kategori
        $categoryBreakdown = [];
        foreach ($categories as $cat) {
            $spent = $cat->transactions->sum('amount');
            if ($spent > 0) {
                $budget = Budget::where('category_id',$cat->id)->first();
                $limit  = $budget?->amount ?? 0;
                
                $categoryBreakdown[] = [
                    'name' => $cat->name,
                    'spent' => $spent,
                    'limit' => $limit,
                    'over' => $limit > 0 && $spent > $limit
                ];
            }
        }

        // Sort by spending
        usort($categoryBreakdown, fn($a,$b) => $b['spent'] <=> $a['spent']);

        if (count($categoryBreakdown) > 0) {
            $reply .= "📋 Breakdown Pengeluaran:\n";
            foreach ($categoryBreakdown as $cb) {
                $status = $cb['over'] ? '❌ OVER!' : '✅';
                $reply .= "• {$cb['name']}: Rp ".number_format($cb['spent'],0,',','.');
                if ($cb['limit'] > 0) {
                    $reply .= " / Rp ".number_format($cb['limit'],0,',','.');
                }
                $reply .= " {$status}\n";
            }
            $reply .= "\n";
        }

        // Rekomendasi spesifik
        $overs = array_filter($categoryBreakdown, fn($cb) => $cb['over']);
        
        if (count($overs) > 0 || $balance < 0) {
            $reply .= "💡 Rekomendasi Ayu:\n";
            
            if (count($overs) > 0) {
                $reply .= "• Kategori yang harus dikurangi: ";
                $overNames = array_map(fn($o) => $o['name'], $overs);
                $reply .= implode(', ', $overNames)."\n";
            }
            
            if ($balance < 0) {
                $reply .= "• Fokus ke kategori dengan pengeluaran terbesar dulu\n";
                $reply .= "• Coba hemat di kategori non-esensial\n";
            }
            
            $reply .= "\nAyu yakin kamu bisa atur ulang kok 💪✨";
        } else {
            $reply .= "✨ Kondisi keuangan masih aman! Tetap pantau pengeluaran ya 😊";
        }

        return $reply;
    }

    /* ============================================================
     * GENERAL REPLY (tanpa keyword, full natural)
     * ============================================================ */
    protected function generateNaturalGeneralReply(string $msg): string
    {
        $trim = trim($msg);

        // Pesan sangat pendek → user bingung
        if (strlen($trim) <= 5) {
            return "Boleh cerita sedikit lebih lengkap? Ayu lagi dengerin kok 😄";
        }

        // Pesan panjang → user jelasin sesuatu
        if (strlen($trim) >= 40) {
            return "Ayu ngerti maksudnya. Ceritain bagian yang paling penting menurut kamu, biar Ayu bisa bantu lebih tepat ya 😊";
        }

        // Ada tanda tanya
        if (str_contains($trim, '?')) {
            return "Pertanyaan yang bagus! Bisa kasih sedikit konteks supaya Ayu jawabnya lebih pas? 🩵";
        }

        // Ada titik atau tanda ekspresi → komentar santai
        if (preg_match('/[.!]$/', $trim)) {
            return "Hehe, noted! Ceritain lebih jauh dong, Ayu penasaran nih 😄✨";
        }

        // fallback natural
        return "Ayu dengerin kok. Jelasin sedikit lagi ya, nanti Ayu bantu lebih tepat 💛";
    }

    /* ============================================================
     * MINI POPUP
     * ============================================================ */
    public function generateAyuPopup()
    {
        $expense = Transaction::where('type','expense')->sum('amount');

        if ($expense == 0) {
            return "Ayu lihat belum ada pengeluaran, aman banget kamu hari ini 😆✨";
        }

        $top = Transaction::selectRaw("category_id, SUM(amount) total")
            ->where('type','expense')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->first();

        if (!$top) {
            return "Ayu masih belajar pola keuangan kamu nih 👀✨";
        }

        return "Pengeluaran terbesar hari ini: {$top->category->name}. Coba dipantau yaa 💸";
    }
}