@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
<div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Categories</h1>
        <a href="{{ route('categories.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-medium">
            + Add Category
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse($categories as $category)
        <div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-2xl" style="background-color: {{ $category->color }}20;">
                        {{ $category->icon }}
                    </div>
                    <h3 class="font-bold">{{ $category->name }}</h3>
                </div>
            </div>
            <p class="text-sm text-gray-500 mb-4">{{ $category->transactions_count }} transactions</p>
            <div class="flex gap-2">
                <a href="{{ route('categories.edit', $category) }}" class="flex-1 text-center bg-blue-50 text-blue-600 py-2 rounded-lg hover:bg-blue-100 text-sm font-medium">Edit</a>
                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-50 text-red-600 py-2 rounded-lg hover:bg-red-100 text-sm font-medium" onclick="return confirm('Yakin hapus kategori ini?')">Delete</button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-4 text-center py-20">
            <p class="text-gray-400 text-xl">No Categories Yet</p>
            <p class="text-gray-400 text-sm mt-2">Create your first category to organize transactions</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
