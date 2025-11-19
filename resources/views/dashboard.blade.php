@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Welcome Back, {{ Auth::user()->name }}!</h1>
    </div>

    <!-- Balance Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Balance Card -->
        <div class="bg-gradient-to-br from-yellow-200 to-yellow-300 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                    <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                </svg>
                <span class="font-semibold">Balance</span>
            </div>
            <p class="text-3xl font-bold">Rp {{ number_format($currentBalance, 0, ',', '.') }}</p>
        </div>

        <!-- Income Card -->
        <div class="bg-gradient-to-br from-yellow-200 to-yellow-300 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                </svg>
                <span class="font-semibold">Income</span>
            </div>
            <p class="text-3xl font-bold">{{ \App\Models\Transaction::where('type', 'income')->count() > 0 ? 'Rp ' . number_format(\App\Models\Transaction::where('type', 'income')->sum('amount'), 0, ',', '.') : '-' }}</p>
        </div>

        <!-- Expenses Card -->
        <div class="bg-gradient-to-br from-yellow-200 to-yellow-300 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                </svg>
                <span class="font-semibold">Expenses</span>
            </div>
            <p class="text-3xl font-bold">{{ \App\Models\Transaction::where('type', 'expense')->count() > 0 ? 'Rp ' . number_format(\App\Models\Transaction::where('type', 'expense')->sum('amount'), 0, ',', '.') : '-' }}</p>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Transactions -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">Recent Transactions</h2>
                <a href="{{ route('transactions.create') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">+ Add Transaction</a>
            </div>
            
            @forelse($recentTransactions as $transaction)
            <div class="flex justify-between items-center py-4 border-b last:border-b-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-xl">
                        {{ $transaction->category->icon }}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">{{ $transaction->description }}</p>
                        <p class="text-sm text-gray-500">{{ $transaction->transaction_date->format('d M Y') }}</p>
                    </div>
                </div>
                <span class="font-bold {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                    {{ $transaction->type === 'income' ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                </span>
            </div>
            @empty
            <div class="text-center py-12">
                <p class="text-gray-400 text-lg">No Data Yet</p>
            </div>
            @endforelse
        </div>

        <!-- Right Column - Budgets & Savings -->
        <div class="space-y-6">
            <!-- Active Budgets -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold">Active Budgets</h2>
                    <a href="{{ route('budgets.create') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">+ Add</a>
                </div>
                
                @forelse($budgets as $budget)
                <div class="mb-4 pb-4 border-b last:border-b-0">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">{{ $budget->category->icon }}</span>
                            <span class="font-semibold text-sm">{{ $budget->category->name }}</span>
                        </div>
                        <span class="text-xs text-gray-500">{{ number_format(($budget->spent / $budget->amount) * 100, 0) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-1">
                        <div class="h-2 rounded-full {{ $budget->spent > $budget->amount ? 'bg-red-500' : 'bg-blue-500' }}" 
                             style="width: {{ min(($budget->spent / $budget->amount) * 100, 100) }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600">
                        <span>Rp {{ number_format($budget->spent, 0, ',', '.') }}</span>
                        <span>Rp {{ number_format($budget->amount, 0, ',', '.') }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <p class="text-gray-400 text-sm">No Data Yet</p>
                </div>
                @endforelse
            </div>

            <!-- Savings Goals -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold">Savings Goals</h2>
                    <a href="{{ route('savings.create') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">+ Add</a>
                </div>
                
                @forelse($savings as $saving)
                <div class="mb-4 pb-4 border-b last:border-b-0">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">{{ $saving->icon ?? '🎯' }}</span>
                            <span class="font-semibold text-sm">{{ $saving->name }}</span>
                        </div>
                        <span class="text-xs font-semibold" style="color: {{ $saving->color }}">{{ number_format($saving->progressPercentage(), 0) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-1">
                        <div class="h-2 rounded-full" 
                             style="width: {{ $saving->progressPercentage() }}%; background-color: {{ $saving->color }}"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600">
                        <span>Rp {{ number_format($saving->current_amount, 0, ',', '.') }}</span>
                        <span>Rp {{ number_format($saving->target_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <p class="text-gray-400 text-sm">No Data Yet</p>
                </div>
                @endforelse

                @if($savings->count() > 0)
                <div class="pt-4 border-t mt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Total Savings</span>
                        <span class="font-bold text-green-600">Rp {{ number_format($totalSavings, 0, ',', '.') }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Set Balance Modal Trigger (Hidden by default) -->
    @if(\App\Models\Setting::get('initial_balance') === null)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4">
            <h2 class="text-2xl font-bold mb-4">Set Your Initial Balance</h2>
            <form action="{{ route('set.balance') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Initial Balance</label>
                    <input type="number" name="balance" placeholder="1000000" 
                        class="w-full px-4 py-3 border rounded-lg" step="0.01" required>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-semibold">
                    Save Balance
                </button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
