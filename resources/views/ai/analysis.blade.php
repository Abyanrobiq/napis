@extends('layouts.app')

@section('title', 'AI Spending Analysis')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">🤖 AI Spending Pattern Analysis</h1>
        <p class="text-gray-500 text-sm mt-1">Smart insights about your spending behavior</p>
    </div>

    <!-- Trend Analysis -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="text-xl font-bold mb-4">Spending Trend</h2>
        <div class="flex items-center gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-4xl">{{ $trend === 'increasing' ? '📈' : '📉' }}</span>
                    <div>
                        <p class="text-sm text-gray-600">Your spending is</p>
                        <p class="text-2xl font-bold {{ $trend === 'increasing' ? 'text-red-600' : 'text-green-600' }}">
                            {{ ucfirst($trend) }}
                        </p>
                    </div>
                </div>
                <p class="text-gray-600">
                    {{ abs($trendPercentage) > 0 ? number_format(abs($trendPercentage), 1) . '% ' . ($trend === 'increasing' ? 'higher' : 'lower') : 'Same' }} 
                    compared to last month
                </p>
            </div>
            <div class="flex gap-8">
                <div>
                    <p class="text-sm text-gray-600">This Month</p>
                    <p class="text-xl font-bold">Rp {{ number_format($thisMonthExpense, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Last Month</p>
                    <p class="text-xl font-bold">Rp {{ number_format($lastMonthExpense, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Analysis -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="text-xl font-bold mb-4">Spending by Category (Last 30 Days)</h2>
        <div class="space-y-4">
            @foreach($categoryAnalysis as $analysis)
            <div class="border-b pb-4 last:border-b-0">
                <div class="flex justify-between items-center mb-2">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">{{ $analysis->category->icon }}</span>
                        <div>
                            <h3 class="font-bold">{{ $analysis->category->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $analysis->count }} transactions</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-bold text-red-600">Rp {{ number_format($analysis->total, 0, ',', '.') }}</p>
                        <p class="text-sm text-gray-500">Avg: Rp {{ number_format($analysis->total / $analysis->count, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Anomaly Detection -->
    @if(count($anomalies) > 0)
    <div class="bg-orange-50 border border-orange-200 rounded-2xl p-6">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-2xl">⚠️</span>
            <h2 class="text-xl font-bold text-orange-800">Unusual Transactions Detected</h2>
        </div>
        <div class="space-y-3">
            @foreach($anomalies as $anomaly)
            <div class="bg-white rounded-lg p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-semibold">{{ $anomaly['transaction']->description }}</p>
                        <p class="text-sm text-gray-600">{{ $anomaly['transaction']->category->name }} • {{ $anomaly['transaction']->transaction_date->format('d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-red-600">Rp {{ number_format($anomaly['transaction']->amount, 0, ',', '.') }}</p>
                        <p class="text-xs text-orange-600">+Rp {{ number_format($anomaly['difference'], 0, ',', '.') }} above average</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- AI Insights -->
    <div class="bg-gradient-to-br from-purple-50 to-blue-50 rounded-2xl p-6 border border-purple-200">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-2xl">💡</span>
            <h2 class="text-xl font-bold text-purple-800">AI Insights & Recommendations</h2>
        </div>
        <div class="space-y-3">
            @if($trend === 'increasing' && $trendPercentage > 10)
            <div class="bg-white rounded-lg p-4">
                <p class="font-semibold text-purple-800">⚠️ High Spending Alert</p>
                <p class="text-sm text-gray-700 mt-1">Your spending increased by {{ number_format($trendPercentage, 1) }}%. Consider reviewing your budget and cutting unnecessary expenses.</p>
            </div>
            @endif

            @if(count($anomalies) > 0)
            <div class="bg-white rounded-lg p-4">
                <p class="font-semibold text-purple-800">🔍 Unusual Activity</p>
                <p class="text-sm text-gray-700 mt-1">We detected {{ count($anomalies) }} unusual transaction(s). Make sure these are legitimate expenses.</p>
            </div>
            @endif

            <div class="bg-white rounded-lg p-4">
                <p class="font-semibold text-purple-800">📊 Pattern Recognition</p>
                <p class="text-sm text-gray-700 mt-1">Based on your spending pattern, we recommend setting up budgets for your top spending categories.</p>
            </div>

            <div class="bg-white rounded-lg p-4">
                <p class="font-semibold text-purple-800">🎯 Next Steps</p>
                <p class="text-sm text-gray-700 mt-1">
                    <a href="{{ route('ai.budget-recommendation') }}" class="text-blue-600 hover:underline">View AI Budget Recommendations</a> • 
                    <a href="{{ route('ai.reminders') }}" class="text-blue-600 hover:underline">Check Smart Reminders</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
