@extends('layouts.app')

@section('title', 'Edit Budget')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Edit Budget</h1>

    <form action="{{ route('budgets.update', $budget) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Kategori</label>
                <select name="category_id" class="w-full px-4 py-2 border rounded-lg" required>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $budget->category_id == $category->id ? 'selected' : '' }}>
                        {{ $category->icon }} {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Jumlah Budget</label>
                <input type="number" name="amount" value="{{ $budget->amount }}" class="w-full px-4 py-2 border rounded-lg" step="0.01" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Tanggal Mulai</label>
                    <input type="date" name="period_start" value="{{ $budget->period_start->format('Y-m-d') }}" class="w-full px-4 py-2 border rounded-lg" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Tanggal Selesai</label>
                    <input type="date" name="period_end" value="{{ $budget->period_end->format('Y-m-d') }}" class="w-full px-4 py-2 border rounded-lg" required>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Update
                </button>
                <a href="{{ route('budgets.index') }}" class="bg-gray-200 px-6 py-2 rounded-lg hover:bg-gray-300">
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
