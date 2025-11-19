@extends('layouts.app')

@section('title', 'Edit Saving Goal')

@section('content')
<div class="bg-white rounded-2xl shadow-sm p-8 max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Edit Saving Goal</h1>
        <p class="text-gray-500 text-sm mt-1">Update your financial target</p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('savings.update', $saving) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-semibold mb-2">Goal Name *</label>
                <input type="text" name="name" value="{{ old('name', $saving->name) }}" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                       placeholder="e.g., Buy New Car, Vacation to Bali" required>
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-semibold mb-2">Description</label>
                <textarea name="description" rows="3" 
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                          placeholder="Describe your goal...">{{ old('description', $saving->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Target Amount *</label>
                <input type="number" name="target_amount" value="{{ old('target_amount', $saving->target_amount) }}" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                       placeholder="10000000" step="0.01" required>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Current Amount</label>
                <input type="number" name="current_amount" value="{{ old('current_amount', $saving->current_amount) }}" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                       placeholder="0" step="0.01">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Icon (Emoji)</label>
                <input type="text" name="icon" value="{{ old('icon', $saving->icon) }}" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                       placeholder="🎯">
                <p class="text-xs text-gray-500 mt-1">Examples: 🚗 🏠 ✈️ 💍 📱 🎓</p>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Color</label>
                <input type="color" name="color" value="{{ old('color', $saving->color) }}" 
                       class="w-full h-12 border border-gray-300 rounded-lg cursor-pointer">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Target Date</label>
                <input type="date" name="target_date" value="{{ old('target_date', $saving->target_date?->format('Y-m-d')) }}" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Status *</label>
                <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    <option value="active" {{ old('status', $saving->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="paused" {{ old('status', $saving->status) === 'paused' ? 'selected' : '' }}>Paused</option>
                    <option value="completed" {{ old('status', $saving->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-semibold">
                Update Saving Goal
            </button>
            <a href="{{ route('savings.index') }}" class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-lg hover:bg-gray-200 font-semibold text-center">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
