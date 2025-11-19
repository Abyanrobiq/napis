@extends('layouts.app')

@section('title', 'Financial Reports')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold">Financial Reports</h1>
            <p class="text-gray-500 text-sm mt-1">Automatic financial analysis and insights</p>
        </div>
        <div class="flex gap-3">
            <select onchange="window.location.href='?period='+this.value" class="px-4 py-2 border rounded-lg">
                <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                <option value="year" {{ $period === 'year' ? 'selected' : '' }}>This Year</option>
            </select>
            <a href="{{ route('reports.export', ['period' => $period]) }}" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 font-medium">
                📥 Export CSV
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-green-400 to-green-500 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-2xl">💰</span>
                <span class="font-semibold">Total Income</span>
            </div>
            <p class="text-3xl font-bold">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
        </div>

        <div class="bg-gradient-to-br from-red-400 to-red-500 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-2xl">💸</span>
                <span class="font-semibold">Total Expense</span>
            </div>
            <p class="text-3xl font-bold">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
        </div>

        <div class="bg-gradient-to-br from-blue-400 to-blue-500 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-2xl">📊</span>
                <span class="font-semibold">Net Income</span>
            </div>
            <p class="text-3xl font-bold">Rp {{ number_format($netIncome, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Expense by Category -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-xl font-bold mb-4">Expense by Category</h2>
            @forelse($expenseByCategory as $item)
            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">{{ $item->category->icon }}</span>
                        <span class="font-semibold text-sm">{{ $item->category->name }}</span>
                    </div>
                    <span class="font-bold text-red-600">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-red-500 h-2 rounded-full" style="width: {{ ($item->total / $totalExpense) * 100 }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ number_format(($item->total / $totalExpense) * 100, 1) }}% of total expenses</p>
            </div>
            @empty
            <p class="text-center text-gray-400 py-8">No expense data</p>
            @endforelse
        </div>

        <!-- Income by Category -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-xl font-bold mb-4">Income by Category</h2>
            @forelse($incomeByCategory as $item)
            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">{{ $item->category->icon }}</span>
                        <span class="font-semibold text-sm">{{ $item->category->name }}</span>
                    </div>
                    <span class="font-bold text-green-600">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ ($item->total / $totalIncome) * 100 }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ number_format(($item->total / $totalIncome) * 100, 1) }}% of total income</p>
            </div>
            @empty
            <p class="text-center text-gray-400 py-8">No income data</p>
            @endforelse
        </div>
    </div>

    <!-- Budget Performance -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="text-xl font-bold mb-4">Budget Performance</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($budgetPerformance as $budget)
            <div class="border rounded-xl p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-2xl">{{ $budget->category->icon }}</span>
                    <div>
                        <h3 class="font-bold">{{ $budget->category->name }}</h3>
                        <p class="text-xs text-gray-500">{{ $budget->period_start->format('d M') }} - {{ $budget->period_end->format('d M') }}</p>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="h-3 rounded-full {{ $budget->spent > $budget->amount ? 'bg-red-500' : 'bg-blue-500' }}" 
                             style="width: {{ min(($budget->spent / $budget->amount) * 100, 100) }}%"></div>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Spent: Rp {{ number_format($budget->spent, 0, ',', '.') }}</span>
                        <span class="font-semibold">{{ number_format(($budget->spent / $budget->amount) * 100, 0) }}%</span>
                    </div>
                </div>
            </div>
            @empty
            <p class="col-span-3 text-center text-gray-400 py-8">No active budgets</p>
            @endforelse
        </div>
    </div>

    <!-- Savings Progress -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="text-xl font-bold mb-4">Savings Goals Progress</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($savingsProgress as $saving)
            <div class="border rounded-xl p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-2xl">{{ $saving->icon ?? '🎯' }}</span>
                    <div>
                        <h3 class="font-bold">{{ $saving->name }}</h3>
                        @if($saving->target_date)
                        <p class="text-xs text-gray-500">Target: {{ $saving->target_date->format('d M Y') }}</p>
                        @endif
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="h-3 rounded-full" 
                             style="width: {{ $saving->progressPercentage() }}%; background-color: {{ $saving->color }}"></div>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Rp {{ number_format($saving->current_amount, 0, ',', '.') }}</span>
                        <span class="font-semibold" style="color: {{ $saving->color }}">{{ number_format($saving->progressPercentage(), 0) }}%</span>
                    </div>
                </div>
            </div>
            @empty
            <p class="col-span-3 text-center text-gray-400 py-8">No active savings goals</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
