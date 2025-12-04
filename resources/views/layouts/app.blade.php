<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    </style>
</head>
@if(session('success'))
<div class="fixed top-6 right-6 bg-green-100 border border-green-300 
            text-green-800 px-4 py-3 rounded-xl shadow-md animate-fade-in z-50">
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
            <svg class="w-12 h-12" viewBox="0 0 100 100" fill="none">
                <path d="M20 20 L50 80 L80 20 M35 35 L50 60 L65 35"
                      stroke="black" stroke-width="8" stroke-linecap="round"/>
            </svg>
        </div>

        <nav class="flex-1 px-4">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg mb-1 text-sm
               {{ request()->routeIs('dashboard') ? 'sidebar-active' : 'text-gray-700 hover:bg-gray-100' }}">
                <span class="font-medium">Home</span>
            </a>

            <a href="{{ route('budgets.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg mb-1 text-sm
               {{ request()->routeIs('budgets.*') ? 'sidebar-active' : 'text-gray-700 hover:bg-gray-100' }}">
                <span class="font-medium">Budget</span>
            </a>

            <a href="{{ route('transactions.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg mb-1 text-sm
               {{ request()->routeIs('transactions.*') ? 'sidebar-active' : 'text-gray-700 hover:bg-gray-100' }}">
                <span class="font-medium">Transaction</span>
            </a>

            <a href="{{ route('savings.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg mb-1 text-sm
               {{ request()->routeIs('savings.*') ? 'sidebar-active' : 'text-gray-700 hover:bg-gray-100' }}">
                <span class="font-medium">Savings</span>
            </a>

            <a href="{{ route('reports.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg mb-1 text-sm
               {{ request()->routeIs('reports.*') ? 'sidebar-active' : 'text-gray-700 hover:bg-gray-100' }}">
                <span class="font-medium">Reports</span>
            </a>

            <div class="border-t my-3"></div>
            <p class="px-4 text-xs font-semibold text-gray-500 mb-2">AI FEATURES</p>

            <a href="{{ route('ai.reminders') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg mb-1 text-sm
               {{ request()->routeIs('ai.reminders') ? 'sidebar-active' : 'text-gray-700 hover:bg-gray-100' }}">
                <span class="font-medium">Smart Reminders</span>
            </a>

            <a href="{{ route('ai.analysis') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg mb-1 text-sm
               {{ request()->routeIs('ai.analysis') ? 'sidebar-active' : 'text-gray-700 hover:bg-gray-100' }}">
                <span class="font-medium">AI Analysis</span>
            </a>

            <a href="{{ route('ai.budget-recommendation') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg mb-1 text-sm
               {{ request()->routeIs('ai.budget-recommendation') ? 'sidebar-active' : 'text-gray-700 hover:bg-gray-100' }}">
                <span class="font-medium">AI Recommendations</span>
            </a>
        </nav>

        <div class="p-4 border-t">
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600">CASH</span>
                <span class="font-semibold">
                    Rp {{ number_format(\App\Models\Setting::get('initial_balance', 0),0,',','.') }}
                </span>
            </div>
        </div>
    </aside>


    {{-- MAIN --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            @yield('content')
        </main>
    </div>
</div>

</body>
</html>
