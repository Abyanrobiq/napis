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
     * TEST API KEY
     * - test if Google AI API key is working
     * ============================================================ */
    public function testApiKey()
    {
        // Try to get API key from .env
        $apiKey = env('GOOGLE_AI_KEY') ?: 'AIzaSyA66BMp9bn4zUQYOr3seBHXHB2g1UKLrK0';
        
        if (!$apiKey || $apiKey === 'YOUR_NEW_API_KEY_HERE') {
            return response()->json([
                'status' => 'error',
                'message' => 'API key not configured',
                'debug' => [
                    'api_key_exists' => $apiKey ? 'yes' : 'no',
                    'api_key_value' => $apiKey ? substr($apiKey, 0, 10) . '...' : 'null',
                    'env_check' => env('GOOGLE_AI_KEY') ? 'found' : 'not found'
                ]
            ]);
        }
        
        // First, try to list available models
        $listUrl = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";
        
        try {
            $listResponse = Http::timeout(10)->get($listUrl);
            
            if ($listResponse->successful()) {
                $models = $listResponse->json();
                
                // Try to find a working model from the list
                if (isset($models['models']) && is_array($models['models'])) {
                    foreach ($models['models'] as $modelInfo) {
                        $modelName = $modelInfo['name'] ?? '';
                        if (strpos($modelName, 'models/') === 0) {
                            $modelName = substr($modelName, 7); // Remove 'models/' prefix
                        }
                        
                        // Try this model
                        $testUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$apiKey}";
                        $payload = [
                            'contents' => [
                                ['parts' => [['text' => 'Hello']]]
                            ]
                        ];
                        
                        try {
                            $testResponse = Http::timeout(10)
                                ->withHeaders(['Content-Type' => 'application/json'])
                                ->post($testUrl, $payload);
                                
                            if ($testResponse->successful()) {
                                return response()->json([
                                    'status' => 'success',
                                    'message' => "API key is working!",
                                    'working_model' => $modelName,
                                    'available_models' => array_column($models['models'], 'name'),
                                    'response' => $testResponse->json()
                                ]);
                            }
                        } catch (\Exception $e) {
                            // Continue to next model
                        }
                    }
                }
                
                return response()->json([
                    'status' => 'partial_success',
                    'message' => 'API key can list models but none work for generation',
                    'available_models' => $models
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot list models',
                    'status_code' => $listResponse->status(),
                    'body' => $listResponse->body()
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exception: ' . $e->getMessage()
            ]);
        }
    }

    /* ============================================================
     * AYU — NEW SESSION
     * - clear chat history and start fresh
     * ============================================================ */
    public function newSession(Request $request)
    {
        try {
            session()->forget('ayu_memory');
            
            return response()->json([
                'success' => true,
                'message' => 'Session baru dimulai! Ayu siap ngobrol lagi! 🎉'
            ], 200, ['Content-Type' => 'application/json']);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulai session baru: ' . $e->getMessage()
            ], 500, ['Content-Type' => 'application/json']);
        }
    }

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

        $userMessage = $request->message;

        // load & update session memory
        $history = session()->get('ayu_memory', []);
        $history[] = ['role' => 'user', 'text' => $userMessage];
        $history = array_slice($history, -$this->maxHistory);
        session()->put('ayu_memory', $history);

        $apiKey = env('GOOGLE_AI_KEY') ?: 'AIzaSyCnLM0gcFS67QhY05ib6ZSsoXuAkl4pZ44';
        
        // For now, use enhanced smart fallback due to API overload issues
        return $this->getSmartFallbackResponse($userMessage, $history);
        
        // Use fallback if no API key
        if (!$apiKey || $apiKey === 'YOUR_NEW_API_KEY_HERE') {
            return $this->getSmartFallbackResponse($userMessage, $history);
        }

        // Detect if question is finance-related or general
        $isFinanceQuestion = $this->isFinanceRelated($userMessage);
        
        if ($isFinanceQuestion) {
            // Financial AI personality
            $ayuPersonality = "
Kamu adalah AYU, AI assistant keuangan yang pintar dan lucu.
Untuk pertanyaan keuangan:
- Analisis data keuangan user
- Berikan rekomendasi spesifik
- Gaya: gaul, cerewet, tapi peduli
- Maksimal 6 kalimat
- Gunakan data finansial di bawah
";
            
            $financialSummary = $this->buildFullFinancialContext();
            $prompt = $ayuPersonality . "\n\n";
            $prompt .= "===== DATA KEUANGAN USER =====\n" . $financialSummary . "\n\n";
        } else {
            // General AI personality
            $ayuPersonality = "
Kamu adalah AYU, AI assistant yang pintar, lucu, dan bisa menjawab berbagai pertanyaan.
Untuk pertanyaan umum:
- Jawab dengan pengetahuan umum
- Tetap ramah dan lucu
- Gaya: gaul tapi informatif
- Maksimal 6 kalimat
- Jika tidak tahu, bilang dengan jujur
";
            
            $prompt = $ayuPersonality . "\n\n";
        }
        
        $prompt .= "===== RIWAYAT CHAT =====\n";
        foreach ($history as $h) {
            $prompt .= strtoupper($h['role']) . ": " . $h['text'] . "\n";
        }
        $prompt .= "\nUSER: {$userMessage}\nAYU:";

        // Use model that we know works from test-key
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";
        
        \Log::info("Using model: gemini-2.5-flash (may be overloaded)");
        
        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 150,
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_NONE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_NONE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_NONE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_NONE'
                ]
            ]
        ];

        \Log::info('Making API call to Gemini', [
            'url' => $url,
            'prompt_length' => strlen($prompt)
        ]);

        try {
            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            \Log::info('API Response received', [
                'status' => $response->status(),
                'success' => $response->successful()
            ]);

            if ($response->failed()) {
                // Log error for debugging
                \Log::error('Gemini API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $url
                ]);
                
                // Use smart fallback instead of generic error
                return $this->getSmartFallbackResponse($userMessage, $history);
            }

            $json = $response->json();
            \Log::info('API JSON parsed successfully');
            
        } catch (\Throwable $e) {
            // Log the actual error for debugging
            \Log::error('AI Chat Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Use smart fallback instead of generic error
            return $this->getSmartFallbackResponse($userMessage, $history);
        }

        // extract text safely
        $reply = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$reply || trim($reply) === '') {
            // Use smart fallback if no valid response
            return $this->getSmartFallbackResponse($userMessage, $history);
        }

        // remove duplicated sentences
        $reply = $this->cleanRepeats($reply);

        $history[] = ['role' => 'assistant', 'text' => $reply];
        $history = array_slice($history, -$this->maxHistory);
        session()->put('ayu_memory', $history);

        return response()->json([
            'reply' => explode("\n\n", $reply)
        ]);
        $history[] = ['role' => 'user', 'text' => $userMessage];
        $history = array_slice($history, -$this->maxHistory);
        session()->put('ayu_memory', $history);

        // Detect if question is finance-related or general
        $isFinanceQuestion = $this->isFinanceRelated($userMessage);
        
        if ($isFinanceQuestion) {
            // Financial AI personality
            $ayuPersonality = "
Kamu adalah AYU, AI assistant keuangan yang pintar dan lucu.
Untuk pertanyaan keuangan:
- Analisis data keuangan user
- Berikan rekomendasi spesifik
- Gaya: gaul, cerewet, tapi peduli
- Maksimal 6 kalimat
- Gunakan data finansial di bawah
";
            
            $financialSummary = $this->buildFullFinancialContext();
            $prompt = $ayuPersonality . "\n\n";
            $prompt .= "===== DATA KEUANGAN USER =====\n" . $financialSummary . "\n\n";
        } else {
            // General AI personality
            $ayuPersonality = "
Kamu adalah AYU, AI assistant yang pintar, lucu, dan bisa menjawab berbagai pertanyaan.
Untuk pertanyaan umum:
- Jawab dengan pengetahuan umum
- Tetap ramah dan lucu
- Gaya: gaul tapi informatif
- Maksimal 6 kalimat
- Jika tidak tahu, bilang dengan jujur
";
            
            $prompt = $ayuPersonality . "\n\n";
        }
        
        $prompt .= "===== RIWAYAT CHAT =====\n";
        foreach ($history as $h) {
            $prompt .= strtoupper($h['role']) . ": " . $h['text'] . "\n";
        }
        $prompt .= "\nUSER: {$userMessage}\nAYU:";

        // call Gemini safely - using stable model
        $url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key={$apiKey}";
        
        // Simplify prompt for better results
        $simplePrompt = "Kamu adalah Ayu, AI assistant yang lucu dan pintar. Jawab pertanyaan ini dengan gaya gaul tapi informatif (maksimal 3 kalimat): " . $userMessage;
        
        $payload = [
            'contents' => [
                ['parts' => [['text' => $simplePrompt]]]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 100,
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_NONE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_NONE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_NONE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_NONE'
                ]
            ]
        ];

        try {
            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->failed()) {
                // Log error for debugging
                \Log::error('Gemini API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $url
                ]);
                
                // Fallback response untuk API error
                $fallbackReplies = [
                    "Ayu lagi maintenance nih 🔧 Coba lagi nanti ya!",
                    "Server Ayu lagi sibuk, sabar dikit! 🛒",
                    "Ayu error 500 😵 Tapi masih bisa ngobrol kok!",
                    "Koneksi Ayu lemot nih... Sabar ya! 😂",
                    "Ayu offline sebentar, tapi balik lagi! 💻",
                    "Waduh, ada gangguan teknis. Tapi Ayu tetep semangat! ⚡"
                ];
                $reply = $fallbackReplies[array_rand($fallbackReplies)];
                
                $history[] = ['role' => 'assistant', 'text' => $reply];
                session()->put('ayu_memory', $history);
                
                return response()->json([
                    'reply' => [$reply]
                ]);
            }

            $json = $response->json();
        } catch (\Throwable $e) {
            // Log the actual error for debugging
            \Log::error('AI Chat Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Use smart fallback instead of generic error
            return $this->getSmartFallbackResponse($userMessage, $history);
        }

        // extract text safely
        $reply = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$reply || trim($reply) === '') {
            // Fallback response jika API gagal
            $fallbackReplies = [
                "Ayu lagi bingung nih 😅 Coba tanya lagi ya!",
                "Maaf ya, Ayu lagi error dikit. Tapi Ayu tetep siap bantu! 🤖",
                "Ayu lagi loading nih... Sabar ya! ⏳",
                "Error 404: Jawaban not found 😂 Tapi Ayu masih di sini kok!",
                "Ayu lagi ngantuk, tapi masih bisa ngobrol! 💤",
                "Hmm, Ayu kurang paham. Bisa jelasin lagi ga? 🤔"
            ];
            $reply = $fallbackReplies[array_rand($fallbackReplies)];
        } else {
            // remove duplicated sentences
            $reply = $this->cleanRepeats($reply);
        }

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

        // average spending last 1 month
        $avg = Transaction::where('category_id', $category->id)
            ->where('type', 'expense')
            ->whereDate('created_at', '>=', now()->subMonth())
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
        $apiKey = env('GOOGLE_AI_KEY') ?: 'AIzaSyAkEFVX1_NmwEkNSx8j38NIQMylfSYWGS4';

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

        $url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key={$apiKey}";
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
     * SMART FALLBACK RESPONSE
     * - provide intelligent responses without API
     * ============================================================ */
    private function getSmartFallbackResponse(string $userMessage, array $history)
    {
        $message = strtolower($userMessage);
        
        // Greeting responses
        if (preg_match('/\b(hai|halo|hello|hi|selamat|pagi|siang|malam)\b/', $message)) {
            $replies = [
                "Hai! Saya Ayu, AI assistant yang siap membantu Anda dengan berbagai pertanyaan. Ada yang ingin ditanyakan?",
                "Halo! Senang bertemu dengan Anda. Saya bisa membantu dengan pertanyaan umum, keuangan, atau topik lainnya.",
                "Hi! Saya Ayu, asisten AI yang dapat menjawab berbagai pertanyaan. Silakan tanya apa saja!",
                "Selamat datang! Saya di sini untuk membantu menjawab pertanyaan Anda dengan kemampuan AI."
            ];
        }
        // Finance questions
        elseif ($this->isFinanceRelated($userMessage)) {
            $financialSummary = $this->buildFullFinancialContext();
            $replies = [
                "Dari data keuangan kamu, Ayu liat ada yang perlu diperhatiin nih! Mau Ayu jelasin? 💰",
                "Keuangan kamu gimana ya... Ayu coba analisis dulu ya! 📊",
                "Soal uang nih! Ayu paling suka bahas ini. Mau tips hemat ga? 💡",
                "Budget planning ya? Ayu expert di bidang ini! 🎯"
            ];
        }
        // General questions - provide more AI-like responses
        elseif (preg_match('/\b(apa|bagaimana|kenapa|dimana|kapan|siapa)\b/', $message)) {
            // Try to give more specific responses based on keywords
            if (preg_match('/\b(AI|artificial intelligence|kecerdasan buatan)\b/i', $message)) {
                $replies = [
                    "AI atau Artificial Intelligence adalah teknologi yang memungkinkan mesin untuk belajar dan membuat keputusan seperti manusia. AI digunakan dalam berbagai bidang seperti healthcare, finance, dan teknologi.",
                    "Kecerdasan Buatan (AI) adalah simulasi kecerdasan manusia dalam mesin yang diprogram untuk berpikir dan belajar. Contohnya seperti chatbot, voice assistant, dan sistem rekomendasi."
                ];
            } elseif (preg_match('/\b(investasi|saham|trading)\b/i', $message)) {
                $replies = [
                    "Investasi adalah cara menempatkan uang untuk mendapatkan keuntungan di masa depan. Ada berbagai jenis investasi seperti saham, obligasi, reksa dana, dan properti. Penting untuk memahami risiko sebelum berinvestasi.",
                    "Saham adalah surat berharga yang menunjukkan kepemilikan dalam suatu perusahaan. Trading saham melibatkan jual beli saham untuk mendapatkan profit dari perubahan harga."
                ];
            } elseif (preg_match('/\b(teknologi|programming|coding)\b/i', $message)) {
                $replies = [
                    "Teknologi terus berkembang pesat, terutama di bidang AI, cloud computing, dan mobile development. Programming adalah skill yang sangat valuable di era digital ini.",
                    "Coding atau programming adalah proses menulis instruksi untuk komputer. Bahasa pemrograman populer saat ini termasuk Python, JavaScript, Java, dan Go."
                ];
            } else {
                $replies = [
                    "Pertanyaan yang menarik! Berdasarkan pengetahuan saya, saya akan coba berikan informasi yang akurat. Bisa Anda berikan detail lebih spesifik?",
                    "Saya akan berusaha menjawab sebaik mungkin. Untuk memberikan jawaban yang lebih tepat, bisa tolong jelaskan konteks pertanyaan Anda?",
                    "Ini topik yang luas. Saya bisa membantu memberikan penjelasan umum, tapi akan lebih baik jika Anda bisa spesifikkan aspek mana yang ingin diketahui."
                ];
            }
        }
        // Thank you
        elseif (preg_match('/\b(terima kasih|thanks|makasih|thx)\b/', $message)) {
            $replies = [
                "Sama-sama! Ayu senang bisa bantu! 😊",
                "You're welcome! Kapan-kapan tanya lagi ya! 🤗",
                "Gak papa! Ayu emang suka bantu-bantu! ✨",
                "Makasih juga udah ngobrol sama Ayu! 💕"
            ];
        }
        // Default responses - more AI-like
        else {
            $replies = [
                "Saya belum sepenuhnya memahami pertanyaan Anda. Bisa tolong dijelaskan lebih detail atau dengan kata-kata yang berbeda?",
                "Maaf, saya perlu informasi lebih spesifik untuk memberikan jawaban yang akurat. Bisa Anda perjelas maksud pertanyaan Anda?",
                "Ini topik yang menarik, tapi saya butuh konteks lebih banyak untuk memberikan respons yang tepat. Bisa berikan detail tambahan?",
                "Saya ingin membantu, tapi perlu pemahaman yang lebih baik tentang apa yang Anda tanyakan. Bisa dijelaskan dengan cara lain?"
            ];
        }
        
        $reply = $replies[array_rand($replies)];
        
        $history[] = ['role' => 'assistant', 'text' => $reply];
        session()->put('ayu_memory', $history);
        
        return response()->json([
            'reply' => [$reply]
        ]);
    }



    /* ============================================================
     * DETECT FINANCE RELATED QUESTION
     * - check if user question is about finance or general
     * ============================================================ */
    private function isFinanceRelated(string $message): bool
    {
        $financeKeywords = [
            // Indonesian
            'uang', 'duit', 'keuangan', 'budget', 'anggaran', 'pengeluaran', 'pemasukan', 
            'income', 'expense', 'tabungan', 'saving', 'investasi', 'hutang', 'utang',
            'belanja', 'beli', 'bayar', 'harga', 'mahal', 'murah', 'hemat', 'boros',
            'gaji', 'salary', 'cicilan', 'kredit', 'bank', 'atm', 'transfer',
            'financial', 'money', 'cash', 'spend', 'cost', 'price', 'cheap', 'expensive',
            'transaction', 'transaksi', 'kategori', 'category', 'laporan', 'report',
            
            // Context clues
            'berapa', 'total', 'sisa', 'kurang', 'lebih', 'analisis', 'rekomendasi',
            'bagaimana keuangan', 'gimana budget', 'cek saldo', 'lihat pengeluaran'
        ];
        
        $message = strtolower($message);
        
        foreach ($financeKeywords as $keyword) {
            if (strpos($message, strtolower($keyword)) !== false) {
                return true;
            }
        }
        
        return false;
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
