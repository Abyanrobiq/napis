@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">My Profile</h1>
        <a href="{{ route('profile.edit') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium">
            Edit Profile
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
        {{ session('success') }}
    </div>
    @endif

    <!-- Profile Information -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center space-x-6">
            <!-- Avatar -->
            <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-3xl font-bold text-blue-600">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            </div>
            
            <!-- User Info -->
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h2>
                <p class="text-gray-600">{{ $user->email }}</p>
                <p class="text-sm text-gray-500 mt-2">Member since {{ $user->created_at->format('F Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Account Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <span class="text-xl">💰</span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Transactions</p>
                    <p class="text-xl font-bold">{{ \App\Models\Transaction::count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <span class="text-xl">📊</span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Active Budgets</p>
                    <p class="text-xl font-bold">{{ \App\Models\Budget::count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <span class="text-xl">🎯</span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Savings Goals</p>
                    <p class="text-xl font-bold">{{ \App\Models\Saving::count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Actions -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-bold mb-4">Account Actions</h3>
        <div class="space-y-3">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                <span class="text-xl">✏️</span>
                <div>
                    <p class="font-medium">Edit Profile</p>
                    <p class="text-sm text-gray-600">Update your name, email, and password</p>
                </div>
            </a>
            
            <form action="{{ route('logout') }}" method="POST" class="block">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-red-50 transition-colors text-left">
                    <span class="text-xl">🚪</span>
                    <div>
                        <p class="font-medium text-red-600">Logout</p>
                        <p class="text-sm text-gray-600">Sign out of your account</p>
                    </div>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection