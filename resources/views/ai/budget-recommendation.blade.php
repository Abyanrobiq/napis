@extends('layouts.app')

@section('title', 'AI Budget Recommendations')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">🤖 AI Budget Recommendations</h1>
        <p class="text-gray-500 text-sm mt-1">Smart budget suggestions based on your spending history</p>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6">
        <div class="flex items-start gap-3">
            <span class="text-3xl">💡</span>
            <div>
                <h3 class="font-bold text-blue-900 mb-2">How It Works</h3>
                <p class="text-sm text-blue-800">
                    Our AI analyzes your spending patterns from the last 3 months and recommends optimal budget amounts for each category. 
                    The recommendations include a 10% buffer to give you flexibility.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($recommendations as $rec)
        <div class="bg-white rounded-2xl shadow-sm p-6 border-2 
        
            {{ $rec['status'] === 'create' ? 'border-green-200' : '' }}
            {{ $rec['status'] === 'increase' ? 'border-orange-200' : '' }}
            {{ $rec['status'] === 'sufficient' ? 'border-blue-200' : '' }}">
            
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">{{ $rec['category']->icon }}</span>
                    <div>
                        <h3 class="font-bold text-lg">{{ $rec['category']->name }}</h3>
                        <span class="text-xs px-2 py-1 rounded-full
                            {{ $rec['status'] === 'create' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $rec['status'] === 'increase' ? 'bg-orange-100 text-orange-700' : '' }}
                            {{ $rec['status'] === 'sufficient' ? 'bg-blue-100 text-blue-700' : '' }}">
                            {{ $rec['status'] === 'create' ? 'No Budget Set' : '' }}
                            {{ $rec['status'] === 'increase' ? 'Increase Recommended' : '' }}
                            {{ $rec['status'] === 'sufficient' ? 'Budget Sufficient' : '' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Average Spending (3 months)</span>
                    <span class="font-semibold">Rp {{ number_format($rec['average_spending'], 0, ',', '.') }}</span>
                </div>

                @if($rec['has_budget'])
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Current Budget</span>
                    <span class="font-semibold">Rp {{ number_format($rec['current_budget'], 0, ',', '.') }}</span>
                </div>
                @endif
                <form method="POST" action="{{ route('ai.applyBudget') }}">
    @csrf
    <input type="hidden" name="amount" value="{{ $rec['super_hemat'] }}">
    <input type="hidden" name="category_id" value="{{ $rec['category']->id }}">
    
    <button type="submit"
        class="p-4 border rounded-xl bg-green-50 shadow-sm cursor-pointer hover:bg-green-100 transition w-full text-left">
        <h5 class="font-bold text-green-700 text-sm">Super Hemat</h5>
        <p class="text-green-900 font-bold mt-1">
            Rp {{ number_format($rec['super_hemat'], 0, ',', '.') }}
        </p>
        <p class="text-xs text-green-700">(-20% dari rata-rata)</p>
    </button>
</form>


                <div class="flex justify-between items-center pt-3 border-t">
                    <span class="text-sm font-semibold text-gray-800">Recommended Budget</span>
                    <span class="text-xl font-bold text-green-600">
                        Rp {{ number_format($rec['recommended_budget'], 0, ',', '.') }}</span>
                                         {{-- Overspending Warning --}}
                    @if(isset($rec['overspending']) && $rec['overspending'])
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mt-4">
                            <h4 class="font-bold text-red-700">⚠ Overspending Detected</h4>
                            <p class="text-red-700 text-sm mt-1">
                                You overspent 
                                <strong>Rp {{ number_format($rec['overspend_amount'], 0, ',', '.') }}</strong>.
                            </p>

                            @if(isset($rec['recommended_if_overspend']))
                            <p class="text-red-700 text-sm mt-1">
                                Suggested new budget: 
                                <strong>Rp {{ number_format($rec['recommended_if_overspend'],0,',','.') }}</strong>
                            </p>
                            @endif
                        </div>
                    @endif

                    {{-- 3 Lifestyle Recommendations --}}
                    <div class="mt-6">
                        <h4 class="font-semibold text-gray-800 mb-3">AI Lifestyle Recommendations</h4>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                            {{-- Super Hemat --}}
                            <div class="p-4 border rounded-xl bg-green-50 shadow-sm">
                                <h5 class="font-bold text-green-700 text-sm">Super Hemat</h5>
                                <p class="text-green-900 font-bold mt-1">
                                    Rp {{ number_format($rec['super_hemat'], 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-green-700">(-20% dari rata-rata)</p>
                            </div>

                            {{-- Hemat --}}
                            <div class="p-4 border rounded-xl bg-blue-50 shadow-sm">
                                <h5 class="font-bold text-blue-700 text-sm">Hemat</h5>
                                <p class="text-blue-900 font-bold mt-1">
                                    Rp {{ number_format($rec['hemat'], 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-blue-700">(normal)</p>
                            </div>

                            {{-- Nyantai --}}
                            <div class="p-4 border rounded-xl bg-yellow-50 shadow-sm">
                                <h5 class="font-bold text-yellow-700 text-sm">Nyantai</h5>
                                <p class="text-yellow-900 font-bold mt-1">
                                    Rp {{ number_format($rec['nyantai'], 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-yellow-700">(+20% fleksibel)</p>
                            </div>

                        </div>
                    </div>

                </div>

                @if($rec['status'] === 'create')
                <div class="bg-green-50 rounded-lg p-3 mt-3">
                    <p class="text-sm text-green-800">
                        <strong>💡 Suggestion:</strong> Create a budget for this category to better control your spending.
                    </p>
                </div>
                @elseif($rec['status'] === 'increase')
                <div class="bg-orange-50 rounded-lg p-3 mt-3">
                    <p class="text-sm text-orange-800">
                        <strong>⚠️ Warning:</strong> Your current budget is lower than your average spending. Consider increasing it.
                    </p>
                </div>
                @else
                <div class="bg-blue-50 rounded-lg p-3 mt-3">
                    <p class="text-sm text-blue-800">
                        <strong>✅ Good:</strong> Your current budget is sufficient for this category.
                    </p>
                </div>
                @endif

                @if($rec['status'] !== 'sufficient')
                <a href="{{ route('budgets.create') }}" class="block text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 font-medium mt-3">
                    {{ $rec['status'] === 'create' ? 'Create Budget' : 'Update Budget' }}
                    @if($rec['current_budget'] < $rec['average_spending'])
                <div class="bg-blue-50 rounded-lg p-3 mt-3">
                <p class="text-sm text-blue-800">
                    ✔ Budget updated successfully. Overspending will reset next month.
                </p>
                </div>
                @endif

                </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if(count($recommendations) === 0)
    <div class="text-center py-20">
        <span class="text-6xl">📊</span>
        <p class="text-gray-400 text-xl mt-4">Not Enough Data</p>
        <p class="text-gray-400 text-sm mt-2">Add more transactions to get AI recommendations</p>
    </div>
    @endif
</div>
@endsection
