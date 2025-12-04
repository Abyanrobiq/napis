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
                <p class="text-gray-500">Average Spending (3 months)</p>
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

        {{-- RECOMMENDED BUDGET --}}
        <p class="text-gray-500 mb-1">Recommended Budget</p>
        <p class="text-green-600 font-bold text-2xl mb-5">
            Rp {{ number_format($rec['recommended_budget'], 0, ',', '.') }}
        </p>

        {{-- TITLE --}}
        <h4 class="text-center font-bold mb-3 text-gray-700">AI Lifestyle Recommendations</h4>

        {{-- LIFESTYLE CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- SUPER HEMAT --}}
            <div 
                class="p-4 rounded-xl bg-green-100 cursor-pointer hover:shadow-md transition budget-card" 
                onclick="selectBudget('{{ $rec['super_hemat'] }}', this)"
            >
                <p class="font-bold text-green-700 text-sm mb-1">Super Hemat</p>
                <p class="text-xl font-semibold">
                    Rp {{ number_format($rec['super_hemat'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-600 mt-1">(-20% dari rata-rata)</p>
            </div>

            {{-- HEMAT --}}
            <div 
                class="p-4 rounded-xl bg-blue-100 cursor-pointer hover:shadow-md transition budget-card" 
                onclick="selectBudget('{{ $rec['hemat'] }}', this)"
            >
                <p class="font-bold text-blue-700 text-sm mb-1">Hemat</p>
                <p class="text-xl font-semibold">
                    Rp {{ number_format($rec['hemat'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-600 mt-1">(normal)</p>
            </div>

            {{-- NYANTAI --}}
            <div 
                class="p-4 rounded-xl bg-yellow-100 cursor-pointer hover:shadow-md transition budget-card" 
                onclick="selectBudget('{{ $rec['nyantai'] }}', this)"
            >
                <p class="font-bold text-yellow-700 text-sm mb-1">Nyantai</p>
                <p class="text-xl font-semibold">
                    Rp {{ number_format($rec['nyantai'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-600 mt-1">(+20% fleksibel)</p>
            </div>

        </div>

        {{-- WARNING --}}
        @if($rec['current_budget'] < $rec['average_spending'])
        <div class="mt-6 p-3 bg-orange-100 border border-orange-300 rounded-lg text-orange-700 text-sm">
            ⚠️ Warning: Your current budget is lower than your average spending.
        </div>
        @endif

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
