<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .sidebar-active { background: #FDE68A; color: #000; }

        @keyframes fade-in {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fade-in .4s ease-out;
        }

        .napiss-logo {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .napiss-logo:hover {
            transform: scale(1.08) rotate(2deg);
        }

        .napiss-logo svg {
            filter: drop-shadow(0 6px 12px rgba(29, 78, 216, 0.25));
            transition: filter 0.3s ease;
        }
        
        .napiss-logo:hover svg {
            filter: drop-shadow(0 8px 16px rgba(29, 78, 216, 0.35));
        }
        
        /* Logo animation on page load */
        @keyframes logo-entrance {
            0% { 
                opacity: 0; 
                transform: scale(0.8) translateY(-10px); 
            }
            100% { 
                opacity: 1; 
                transform: scale(1) translateY(0); 
            }
        }
        
        .napiss-logo {
            animation: logo-entrance 0.6s ease-out;
        }
    </style>
</head>
@if(session('success'))
<div class="fixed top-20 right-6 bg-green-100 border border-green-300 
            text-green-800 px-4 py-3 rounded-xl shadow-md animate-fade-in z-[60]">
    {{ session('success') }}
</div>

<script>
    setTimeout(() => {
        const el = document.querySelector('[class*="bg-green-100"]');
        if (el) {
            el.style.opacity = "0";
            el.style.transform = "translateY(-10px)";
            setTimeout(() => el.remove(), 400);
        }
    }, 3000);
</script>
@endif

<body class="bg-gray-100">


{{-- ===================================================== --}}
{{-- =============== POPUP AYU AUTO (AI) ================= --}}
{{-- ===================================================== --}}
@if(session('ayu_popup'))
<div id="ayuPopup"
    class="fixed bottom-28 right-6 bg-white/90 backdrop-blur-xl shadow-xl border border-blue-300
           px-4 py-3 rounded-xl z-[99999] animate-fade-in w-64">

    <p class="text-sm font-semibold text-blue-700 leading-tight">
        {{ session('ayu_popup') }}
    </p>
</div>

<script>
    setTimeout(() => {
        const p = document.getElementById('ayuPopup');
        if (p) {
            p.style.opacity = "0";
            p.style.transform = "translateY(30px)";
            setTimeout(() => p.remove(), 500);
        }
    }, 3500);
</script>
@endif



{{-- ===================================================== --}}
{{-- ================= POPUP OVERSPEND ==================== --}}
{{-- ===================================================== --}}
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

@if(session('overspend'))
<div id="overspendPopup"
    class="fixed bottom-44 right-6 bg-white/90 backdrop-blur-xl shadow-xl border border-red-300
           px-4 py-4 rounded-xl flex items-center gap-3 z-[99998] animate-fade-in w-72">

    <lottie-player
        src="https://assets1.lottiefiles.com/packages/lf20_jtbfg2nb.json"
        background="transparent" speed="1"
        style="width: 55px; height: 55px;" autoplay></lottie-player>

    <div>
        <p class="text-red-700 font-bold text-sm">Overspending Detected!</p>

        <p class="text-xs text-red-600 leading-tight mt-1">
            Kategori <strong>{{ session('overspend')['category'] }}</strong>
            overspend sebesar
            <strong>Rp {{ number_format(session('overspend')['overspent'],0,',','.') }}</strong>
        </p>

        <a href="{{ route('ai.budget-recommendation') }}?category_id={{ session('overspend')['category_id'] }}"
   class="mt-2 inline-block bg-red-600 text-white px-2 py-1 rounded text-xs font-semibold">
   Cek Rekomendasi AI 💡
</a>

    </div>

    <button onclick="document.getElementById('overspendPopup').remove()"
        class="absolute top-1 right-2 text-gray-700 text-sm">✕</button>
</div>

<script>
    setTimeout(() => {
        const p = document.getElementById('overspendPopup');
        if (p) {
            p.style.opacity='0';
            p.style.transform='translateY(30px)';
            setTimeout(()=>p.remove(),500);
        }
    }, 4500);
</script>
@endif




{{-- ===================================================== --}}
{{-- =============== FLOATING AI ASSISTANT ================ --}}
{{-- ===================================================== --}}
@include('components.ai-assistant')




{{-- ===================================================== --}}
{{-- ===================== LAYOUT ========================= --}}
{{-- ===================================================== --}}
<div class="flex h-screen">

    {{-- Sidebar --}}
    <aside class="w-64 bg-white shadow-lg flex flex-col">
        <div class="p-6">
            <!-- NAPISS Logo - Clean & Modern -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 napiss-logo cursor-pointer hover:opacity-90 transition-all duration-300 hover:scale-105">
                <div class="relative">
                    <svg class="w-12 h-12" viewBox="0 0 100 100" fill="none">
                        <defs>
                            <!-- Modern gradient with app colors -->
                            <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#6366F1"/>
                                <stop offset="50%" style="stop-color:#3B82F6"/>
                                <stop offset="100%" style="stop-color:#1D4ED8"/>
                            </linearGradient>
                            
                            <!-- Accent for highlights -->
                            <linearGradient id="accentGlow" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#FDE68A"/>
                                <stop offset="100%" style="stop-color:#FBBF24"/>
                            </linearGradient>
                            
                            <!-- Soft shadow filter -->
                            <filter id="logoShadow">
                                <feDropShadow dx="0" dy="3" stdDeviation="6" flood-color="#1D4ED8" flood-opacity="0.25"/>
                            </filter>
                        </defs>
                        
                        <!-- Main background - rounded square for modern look -->
                        <rect x="8" y="8" width="84" height="84" rx="18" ry="18" 
                              fill="url(#logoGradient)" 
                              filter="url(#logoShadow)"/>
                        
                        <!-- Inner border for depth -->
                        <rect x="12" y="12" width="76" height="76" rx="14" ry="14" 
                              fill="none" 
                              stroke="url(#accentGlow)" 
                              stroke-width="0.8" 
                              opacity="0.4"/>
                        
                        <!-- Clean N letter -->
                        <g transform="translate(50,50)">
                            <!-- Left pillar -->
                            <rect x="-16" y="-22" width="5" height="44" 
                                  fill="#FFFFFF" 
                                  rx="2.5"/>
                            
                            <!-- Right pillar -->
                            <rect x="11" y="-22" width="5" height="44" 
                                  fill="#FFFFFF" 
                                  rx="2.5"/>
                            
                            <!-- Diagonal connector -->
                            <path d="M-11 -18 L16 18" 
                                  stroke="#FFFFFF" 
                                  stroke-width="5" 
                                  stroke-linecap="round"/>
                        </g>
                        
                        <!-- Minimal financial accents -->
                        <!-- Top right coin -->
                        <circle cx="78" cy="22" r="6" 
                                fill="url(#accentGlow)" 
                                opacity="0.9"/>
                        <circle cx="78" cy="22" r="3" 
                                fill="#1D4ED8" 
                                opacity="0.7"/>
                        
                        <!-- Bottom left growth indicator -->
                        <g transform="translate(22,78)">
                            <rect x="-2" y="-6" width="1.5" height="6" fill="url(#accentGlow)" rx="0.75"/>
                            <rect x="0" y="-9" width="1.5" height="9" fill="url(#accentGlow)" rx="0.75"/>
                            <rect x="2" y="-4" width="1.5" height="4" fill="url(#accentGlow)" rx="0.75"/>
                        </g>
                        
                        <!-- Subtle highlight for premium feel -->
                        <ellipse cx="35" cy="30" rx="8" ry="4" 
                                 fill="#FFFFFF" 
                                 opacity="0.15" 
                                 transform="rotate(-25 35 30)"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">NAPISS</h1>
                    <p class="text-xs text-gray-500">Financial Planner</p>
                </div>
            </a>
        </div>

        <nav class="flex-1 px-4">

    <a href="{{ route('dashboard') }}"
       class="flex items-center gap-3 px-4 py-2.5 rounded-lg mb-1 text-sm
       {{ request()->routeIs('dashboard') ? 'sidebar-active' : 'text-gray-700 hover:bg-gray-100' }}">
        <span class="text-lg">🏠</span>
        <span class="font-medium">Home</span>
    </a>

    <a href="{{ route('budgets.index') }}"
       class="flex items-center gap-3 px-4 py-2.5 rounded-lg mb-1 text-sm
       {{ request()->routeIs('budgets.*') ? 'sidebar-active' : 'text-gray-700 hover:bg-gray-100' }}">
        <span class="text-lg">💰</span>
        <span class="font-medium">Budget</span>
    </a>

    <a href="{{ route('transactions.index') }}"
       class="flex items-center gap-3 px-4 py-2.5 rounded-lg mb-1 text-sm
       {{ request()->routeIs('transactions.*') ? 'sidebar-active' : 'text-gray-700 hover:bg-gray-100' }}">
        <span class="text-lg">💳</span>
        <span class="font-medium">Transaction</span>
    </a>

    <a href="{{ route('savings.index') }}"
       class="flex items-center gap-3 px-4 py-2.5 rounded-lg mb-1 text-sm
       {{ request()->routeIs('savings.*') ? 'sidebar-active' : 'text-gray-700 hover:bg-gray-100' }}">
        <span class="text-lg">🐷</span>
        <span class="font-medium">Savings</span>
    </a>

    <a href="{{ route('reports.index') }}"
       class="flex items-center gap-3 px-4 py-2.5 rounded-lg mb-1 text-sm
       {{ request()->routeIs('reports.*') ? 'sidebar-active' : 'text-gray-700 hover:bg-gray-100' }}">
        <span class="text-lg">📊</span>
        <span class="font-medium">Reports</span>
    </a>
    
    <div class="border-t my-3"></div>
    <p class="px-4 text-xs font-semibold text-gray-500 mb-2">AI FEATURES</p>

    <a href="{{ route('ai.reminders') }}"
       class="flex items-center gap-3 px-4 py-2.5 rounded-lg mb-1 text-sm
       {{ request()->routeIs('ai.reminders') ? 'sidebar-active' : 'text-gray-700 hover:bg-gray-100' }}">
        <span class="text-lg">🔔</span>
        <span class="font-medium">Smart Reminders</span>
    </a>

    <a href="{{ route('ai.analysis') }}"
       class="flex items-center gap-3 px-4 py-2.5 rounded-lg mb-1 text-sm
       {{ request()->routeIs('ai.analysis') ? 'sidebar-active' : 'text-gray-700 hover:bg-gray-100' }}">
        <span class="text-lg">🤖</span>
        <span class="font-medium">AI Analysis</span>
    </a>

    <a href="{{ route('ai.budget-recommendation') }}"
       class="flex items-center gap-3 px-4 py-2.5 rounded-lg mb-1 text-sm
       {{ request()->routeIs('ai.budget-recommendation') ? 'sidebar-active' : 'text-gray-700 hover:bg-gray-100' }}">
        <span class="text-lg">💡</span>
        <span class="font-medium">AI Recommendations</span>
    </a>
</nav>


        <div class="p-4 border-t">
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600">CASH</span>
                <span class="font-semibold">
                    @php
                        $balance = \App\Models\Setting::get('initial_balance', null);
                        if (is_null($balance)) {
                            $currentBalance = 0;
                        } else {
                            $totalIncome = \App\Models\Transaction::where('type', 'income')->sum('amount');
                            $totalExpense = \App\Models\Transaction::where('type', 'expense')->sum('amount');
                            $currentBalance = $balance + $totalIncome - $totalExpense;
                        }
                    @endphp
                    @if(is_null($balance))
                        <span class="text-gray-400 text-xs">Not Set</span>
                    @else
                        Rp {{ number_format($currentBalance, 0, ',', '.') }}
                    @endif
                </span>
            </div>
        </div>

    </aside>


    {{-- MAIN --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        {{-- Header Bar --}}
        <header class="bg-white shadow-sm border-b px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800">@yield('title', 'NAPISS - Financial Planner')</h1>
                </div>
                
                {{-- Profile Section --}}
                <div class="relative">
                    <button onclick="toggleProfileMenu()" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-sm font-bold text-blue-600">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        </div>
                        <div class="hidden md:block text-left">
                            <p class="text-sm font-medium text-gray-800">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    {{-- Dropdown Menu --}}
                    <div id="profileMenu" class="hidden absolute top-full right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border py-2 z-50">
                        <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <span>👤</span>
                            <span>View Profile</span>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <span>✏️</span>
                            <span>Edit Profile</span>
                        </a>
                        <div class="border-t my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <span>🚪</span>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            @yield('content')
        </main>
    </div>
</div>

<script>
function toggleProfileMenu() {
    const menu = document.getElementById('profileMenu');
    menu.classList.toggle('hidden');
}

// Close profile menu when clicking outside
document.addEventListener('click', function(event) {
    const profileButton = event.target.closest('[onclick="toggleProfileMenu()"]');
    const profileMenu = document.getElementById('profileMenu');
    
    if (!profileButton && !profileMenu.contains(event.target)) {
        profileMenu.classList.add('hidden');
    }
});
</script>

</body>
</html>
