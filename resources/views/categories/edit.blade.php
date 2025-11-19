@extends('layouts.app')

@section('title', 'Edit Kategori')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Edit Kategori</h1>

    <form action="{{ route('categories.update', $category) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama Kategori</label>
                <input type="text" name="name" value="{{ $category->name }}" class="w-full px-4 py-2 border rounded-lg" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Icon (emoji)</label>
                <input type="text" name="icon" value="{{ $category->icon }}" class="w-full px-4 py-2 border rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Warna</label>
                <input type="color" name="color" value="{{ $category->color }}" class="w-full h-10 border rounded-lg">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Update
                </button>
                <a href="{{ route('categories.index') }}" class="bg-gray-200 px-6 py-2 rounded-lg hover:bg-gray-300">
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
