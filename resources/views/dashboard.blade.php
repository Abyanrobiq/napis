@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            @if(isset($isNewUser) && $isNewUser)
                Welcome to NAPISS, {{ Auth::user()->name }}!
            @else
                Welcome Back, {{ Auth::user()->name }}!
            @endif
        </h1>
    </div>

    <!-- Balance Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Balance Card -->
        <div class="bg-yellow-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-lg">💳</span>
                <span class="font-semibold text-sm">Balance</span>
            </div>
            <p class="text-2xl font-bold">Rp {{ number_format($currentBalance, 0, ',', '.') }}</p>
        </div>

        <!-- Income Card -->
        <div class="bg-yellow-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-lg">📥</span>
                <span class="font-semibold text-sm">Income</span>
            </div>
            <p class="text-2xl font-bold">{{ \App\Models\Transaction::where('type', 'income')->count() > 0 ? 'Rp ' . number_format(\App\Models\Transaction::where('type', 'income')->sum('amount'), 0, ',', '.') : '-' }}</p>
        </div>

        <!-- Expenses Card -->
        <div class="bg-yellow-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-lg">📤</span>
                <span class="font-semibold text-sm">Expenses</span>
            </div>
            <p class="text-2xl font-bold">{{ \App\Models\Transaction::where('type', 'expense')->count() > 0 ? 'Rp ' . number_format(\App\Models\Transaction::where('type', 'expense')->sum('amount'), 0, ',', '.') : '-' }}</p>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Transactions -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold">Recent Transactions</h2>
                <a href="{{ route('transactions.create') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">+ Add Transaction</a>
            </div>
            
            @forelse($recentTransactions as $transaction)
            <div class="flex justify-between items-center py-3 border-b last:border-b-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center text-xl">
                        {{ $transaction->category->icon }}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">{{ $transaction->description }}</p>
                        <p class="text-xs text-gray-500">{{ $transaction->transaction_date->format('d M Y') }}</p>
                    </div>
                </div>
                <span class="font-bold text-sm {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                    {{ $transaction->type === 'income' ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                </span>
            </div>
            @empty
            <div class="text-center py-12">
                <p class="text-gray-400">No Data Yet</p>
            </div>
            @endforelse
        </div>

        <!-- Right Column - Budgets & Savings -->
        <div class="space-y-6">
            <!-- Active Budgets -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold">Active Budgets</h2>
                    <a href="{{ route('budgets.create') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">+ Add</a>
                </div>
                
                @forelse($budgets as $budget)
                <div class="mb-4 pb-3 border-b last:border-b-0">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">{{ $budget->category->icon }}</span>
                            <span class="font-semibold text-sm">{{ $budget->category->name }}</span>
                        </div>
                        <span class="text-xs text-gray-500">{{ number_format(($budget->spent / $budget->amount) * 100, 0) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mb-1">
                        <div class="h-1.5 rounded-full {{ $budget->spent > $budget->amount ? 'bg-red-500' : 'bg-blue-500' }}" 
                             style="width: {{ min(($budget->spent / $budget->amount) * 100, 100) }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500">
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
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold">Savings Goals</h2>
                    <a href="{{ route('savings.create') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">+ Add</a>
                </div>
                
                @forelse($savings as $saving)
                <div class="mb-4 pb-3 border-b last:border-b-0">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">{{ $saving->icon ?? '🎯' }}</span>
                            <span class="font-semibold text-sm">{{ $saving->name }}</span>
                        </div>
                        <span class="text-xs font-semibold" style="color: {{ $saving->color }}">{{ number_format($saving->progressPercentage(), 0) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mb-1">
                        <div class="h-1.5 rounded-full" 
                             style="width: {{ $saving->progressPercentage() }}%; background-color: {{ $saving->color }}"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500">
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
                <div class="pt-3 border-t mt-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 font-medium">Total Savings</span>
                        <span class="font-bold text-green-600 text-sm">Rp {{ number_format($totalSavings, 0, ',', '.') }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Set Balance Modal for New Users -->
    @if(isset($isNewUser) && $isNewUser)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl">💰</span>
                </div>
                <h2 class="text-2xl font-bold mb-2">Welcome to NAPISS!</h2>
                <p class="text-gray-600 text-sm">Let's start by setting your initial balance to track your finances properly.</p>
            </div>
            
            <form action="{{ route('set.balance') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2 text-gray-700">Initial Balance</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                        <input type="number" name="balance" placeholder="1000000" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            step="1" min="0" required>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Enter your current cash/bank balance</p>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-semibold transition-colors">
                    Start Managing My Finances
                </button>
            </form>
        </div>
    </div>
    @endif
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {

    fetch("/ai/auto-alert")
        .then(res => res.json())
        .then(data => {
            if (data.status === "WARNING") {
                // Gabungkan alert jadi satu bubble
                let message = data.alerts.join("\n\n");

                // Trigger popup Ayu
                openAyuPopup(message);
            }
        })
        .catch(err => console.error("Ayu alert error:", err));

});

function openAyuPopup(text) {
    // Jika chat bubble kamu pakai sistem append:
    const chatBody = document.getElementById("chat-body");

    chatBody.innerHTML += `
        <div class="ai-bubble">${text}</div>
    `;

    chatBody.scrollTop = chatBody.scrollHeight;
}
</script>
@if(session('ayu_overspend'))
<script>
document.addEventListener("DOMContentLoaded", () => {
    ayuAutoMessage("⚠️ Hei! Kamu overspend di kategori **{{ session('ayu_overspend') }}**! Ayu sedih banget nih 😭 Yuk coba dikurangin biar keuangan kamu makin sehat ❤️‍🔥");
});
</script>
@endif

@endsection
