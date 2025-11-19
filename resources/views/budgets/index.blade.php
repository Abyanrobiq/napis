@extends('layouts.app')

@section('title', 'Budget')

@section('content')
<div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Budget Management</h1>
        <a href="{{ route('budgets.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-medium">
            + Add Budget
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($budgets as $budget)
        <div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition">
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-2xl" style="background-color: {{ $budget->category->color }}20;">
                        {{ $budget->category->icon }}
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">{{ $budget->category->name }}</h3>
                        <p class="text-xs text-gray-500">{{ $budget->period_start->format('d M') }} - {{ $budget->period_end->format('d M Y') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-3">
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="h-3 rounded-full transition-all {{ $budget->spent > $budget->amount ? 'bg-red-500' : 'bg-blue-500' }}" 
                         style="width: {{ min(($budget->spent / $budget->amount) * 100, 100) }}%"></div>
                </div>
                
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Spent</span>
                    <span class="font-bold">Rp {{ number_format($budget->spent, 0, ',', '.') }}</span>
                </div>
                
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Budget</span>
                    <span class="font-semibold">Rp {{ number_format($budget->amount, 0, ',', '.') }}</span>
                </div>
                
                <div class="flex justify-between text-sm pt-2 border-t">
                    <span class="text-gray-600">Remaining</span>
                    <span class="font-bold {{ $budget->remaining() < 0 ? 'text-red-600' : 'text-green-600' }}">
                        Rp {{ number_format($budget->remaining(), 0, ',', '.') }}
                    </span>
                </div>

                <div class="flex gap-2 pt-3">
                    <a href="{{ route('budgets.edit', $budget) }}" class="flex-1 text-center bg-blue-50 text-blue-600 py-2 rounded-lg hover:bg-blue-100 text-sm font-medium">Edit</a>
                    <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-50 text-red-600 py-2 rounded-lg hover:bg-red-100 text-sm font-medium" onclick="return confirm('Yakin hapus budget ini?')">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-20">
            <p class="text-gray-400 text-xl">No Budget Yet</p>
            <p class="text-gray-400 text-sm mt-2">Create your first budget to start tracking expenses</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
