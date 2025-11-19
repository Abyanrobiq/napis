@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
<div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">All Transactions</h1>
        <a href="{{ route('transactions.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-medium">
            + Add Transaction
        </a>
    </div>

    <div class="space-y-1">
        @forelse($transactions as $transaction)
        <div class="flex justify-between items-center p-4 hover:bg-gray-50 rounded-lg transition">
            <div class="flex items-center gap-4 flex-1">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center text-2xl">
                    {{ $transaction->category->icon }}
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-800">{{ $transaction->description }}</p>
                    <p class="text-sm text-gray-500">
                        {{ $transaction->category->name }}
                        @if($transaction->budget)
                        • Budget: {{ $transaction->budget->category->name }}
                        @endif
                    </p>
                    <p class="text-xs text-gray-400">{{ $transaction->transaction_date->format('d M Y, H:i') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-6">
                <span class="font-bold text-lg {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                    {{ $transaction->type === 'income' ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                </span>
                <div class="flex gap-3">
                    <a href="{{ route('transactions.edit', $transaction) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">Edit</a>
                    <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium" onclick="return confirm('Yakin hapus transaksi ini?')">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-20">
            <p class="text-gray-400 text-xl">No Data Yet</p>
            <p class="text-gray-400 text-sm mt-2">Start by adding your first transaction</p>
        </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $transactions->links() }}
    </div>
</div>
@endsection
