@extends('layouts.app')

@section('title', 'AI Budget Recommendation')

@section('content')
<div class="max-w-4xl mx-auto">

    <h2 class="text-2xl font-bold mb-6 text-gray-700">AI Budget Recommendation</h2>

    @foreach($recommendations as $rec)
    <div class="bg-white shadow-md rounded-xl p-6 mb-10 border border-gray-200">

        {{-- HEADER --}}
        <div class="flex items-center gap-3 mb-4">
            <span class="text-3xl">💡</span>
            <h3 class="text-xl font-bold">{{ $rec['category']->name }}</h3>
        </div>

        {{-- AVERAGE & CURRENT --}}
        <div class="grid grid-cols-2 text-sm mb-4">
            <div>
                <p class="text-gray-500">Average Spending (1 month)</p>
                <p class="font-semibold">
                    Rp {{ number_format($rec['average_spending'], 0, ',', '.') }}
                </p>
            </div>

            <div>
                <p class="text-gray-500">Current Budget</p>
                <p class="font-semibold">
                    Rp {{ number_format($rec['current_budget'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        <hr class="my-4">

        {{-- AI ADVICE --}}
        @if(isset($rec['advice']))
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-800">
                <span class="font-semibold">💡 AI Advice:</span> {{ $rec['advice'] }}
            </p>
        </div>
        @endif

        {{-- RECOMMENDED BUDGET --}}
        <div class="mb-4">
            <p class="text-gray-500 mb-1">AI Recommended Budget</p>
            <p class="text-green-600 font-bold text-2xl mb-2">
                Rp {{ number_format($rec['recommended_budget'], 0, ',', '.') }}
            </p>
            
            @if(isset($rec['savings_potential']) && $rec['savings_potential'] > 0)
            <p class="text-sm text-green-600">
                💰 Potensi penghematan: Rp {{ number_format($rec['savings_potential'], 0, ',', '.') }} per bulan
            </p>
            @endif
        </div>

        {{-- CURRENT MONTH SPENDING --}}
        @if(isset($rec['current_month_spending']) && $rec['current_month_spending'] > 0)
        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-600">
                <span class="font-semibold">Spending bulan ini:</span> 
                Rp {{ number_format($rec['current_month_spending'], 0, ',', '.') }}
                @if($rec['overspending'])
                    <span class="text-red-600 font-semibold ml-2">⚠️ Overspend!</span>
                @endif
            </p>
        </div>
        @endif

        {{-- TITLE --}}
        <h4 class="text-center font-bold mb-3 text-gray-700">Pilihan Budget Lifestyle</h4>

        {{-- LIFESTYLE CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">

            {{-- SUPER HEMAT --}}
            <div 
                class="p-4 rounded-xl bg-green-100 cursor-pointer hover:shadow-md transition budget-card" 
                onclick="selectBudget('{{ $rec['super_hemat'] }}', this)"
            >
                <p class="font-bold text-green-700 text-sm mb-1">💚 Super Hemat</p>
                <p class="text-lg font-semibold">
                    Rp {{ number_format($rec['super_hemat'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-600 mt-1">-25% dari spending</p>
                <p class="text-xs text-green-600 mt-1">Maksimal saving!</p>
            </div>

            {{-- HEMAT --}}
            <div 
                class="p-4 rounded-xl bg-blue-100 cursor-pointer hover:shadow-md transition budget-card" 
                onclick="selectBudget('{{ $rec['hemat'] }}', this)"
            >
                <p class="font-bold text-blue-700 text-sm mb-1">💙 Hemat</p>
                <p class="text-lg font-semibold">
                    Rp {{ number_format($rec['hemat'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-600 mt-1">-10% dari spending</p>
                <p class="text-xs text-blue-600 mt-1">Hemat tapi realistis</p>
            </div>

            {{-- NORMAL --}}
            <div 
                class="p-4 rounded-xl bg-gray-100 cursor-pointer hover:shadow-md transition budget-card" 
                onclick="selectBudget('{{ $rec['normal'] }}', this)"
            >
                <p class="font-bold text-gray-700 text-sm mb-1">⚪ Normal</p>
                <p class="text-lg font-semibold">
                    Rp {{ number_format($rec['normal'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-600 mt-1">Sesuai spending aktual</p>
                <p class="text-xs text-gray-600 mt-1">Paling realistis</p>
            </div>

            {{-- NYANTAI --}}
            <div 
                class="p-4 rounded-xl bg-yellow-100 cursor-pointer hover:shadow-md transition budget-card" 
                onclick="selectBudget('{{ $rec['nyantai'] }}', this)"
            >
                <p class="font-bold text-yellow-700 text-sm mb-1">💛 Nyantai</p>
                <p class="text-lg font-semibold">
                    Rp {{ number_format($rec['nyantai'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-600 mt-1">+15% buffer</p>
                <p class="text-xs text-yellow-600 mt-1">Fleksibel & aman</p>
            </div>

        </div>

        {{-- STATUS & WARNINGS --}}
        <div class="mt-6 space-y-3">
            @if($rec['overspending'])
            <div class="p-3 bg-red-100 border border-red-300 rounded-lg text-red-700 text-sm">
                🚨 <strong>Overspending Alert!</strong> 
                Anda sudah overspend Rp {{ number_format($rec['overspend_amount'], 0, ',', '.') }} bulan ini.
            </div>
            @endif

            @if($rec['status'] == 'increase')
            <div class="p-3 bg-orange-100 border border-orange-300 rounded-lg text-orange-700 text-sm">
                ⚠️ <strong>Budget Terlalu Rendah:</strong> 
                Budget saat ini lebih rendah dari pola spending Anda.
            </div>
            @elseif($rec['status'] == 'decrease')
            <div class="p-3 bg-blue-100 border border-blue-300 rounded-lg text-blue-700 text-sm">
                💡 <strong>Budget Bisa Dikurangi:</strong> 
                Budget saat ini terlalu tinggi dibanding spending aktual.
            </div>
            @elseif($rec['status'] == 'sufficient')
            <div class="p-3 bg-green-100 border border-green-300 rounded-lg text-green-700 text-sm">
                ✅ <strong>Budget Sudah Tepat:</strong> 
                Budget Anda sudah sesuai dengan pola spending.
            </div>
            @elseif($rec['status'] == 'create')
            <div class="p-3 bg-purple-100 border border-purple-300 rounded-lg text-purple-700 text-sm">
                🆕 <strong>Buat Budget Baru:</strong> 
                Belum ada budget untuk kategori ini. Disarankan membuat budget.
            </div>
            @endif
        </div>

        {{-- UPDATE FORM --}}
        <form action="{{ route('ai.applyBudget') }}" method="POST" class="mt-6">
            @csrf

            <input type="hidden" name="category_id" value="{{ $rec['category']->id }}">

            {{-- VALUE YANG DIKLIK --}}
            <input type="hidden" name="amount" class="budget-chosen" value="{{ $rec['recommended_budget'] }}">

            <button 
                class="mt-3 w-full bg-blue-600 text-white py-3 rounded-lg font-semibold text-lg hover:bg-blue-700 transition">
                Update Budget
            </button>
        </form>

    </div>
    @endforeach

</div>

{{-- JS UNTUK CARD CLICK --}}
<script>
function selectBudget(amount, card) {

    // hilangkan selected dari semua card dalam kategori yang sama
    card.closest('.grid').querySelectorAll('.budget-card')
        .forEach(c => c.classList.remove('budget-card-selected'));

    // highlight card yang dipilih
    card.classList.add('budget-card-selected');

    // update nilai di hidden input
    const hiddenInput = card.closest('.bg-white').querySelector('.budget-chosen');
    hiddenInput.value = amount;
}
</script>

{{-- STYLE --}}
<style>
.budget-card-selected {
    border: 2px solid #2563eb;
    box-shadow: 0 0 12px rgba(37, 99, 235, 0.35);
}
</style>

@endsection
