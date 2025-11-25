@extends('layouts.app')

@section('title', 'Smart Reminders')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">🔔 Smart Reminders</h1>
        <p class="text-gray-500 text-sm mt-1">AI-powered notifications and alerts</p>
    </div>

    @if(count($reminders) > 0)
    <div class="space-y-4">
        @foreach($reminders as $reminder)
        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 
            {{ ($reminder['color'] ?? '') === 'red' ? 'border-red-500' : '' }}
        {{ ($reminder['color'] ?? '') === 'orange' ? 'border-orange-500' : '' }}
        {{ ($reminder['color'] ?? '') === 'blue' ? 'border-blue-500' : '' }}
        {{ ($reminder['color'] ?? '') === 'purple' ? 'border-purple-500' : '' }}
        {{ ($reminder['color'] ?? '') === 'gray' ? 'border-gray-500' : '' }}">
            
            <div class="flex items-start gap-4">
                <span class="text-4xl">{{ $reminder['icon'] ?? '' }}</span>
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="font-bold text-lg">{{ $reminder['title'] }}</h3>
                        <span class="text-xs px-3 py-1 rounded-full font-semibold
                            {{ $reminder['priority'] === 'high' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $reminder['priority'] === 'medium' ? 'bg-orange-100 text-orange-700' : '' }}
                            {{ $reminder['priority'] === 'low' ? 'bg-gray-100 text-gray-700' : '' }}">
                            {{ strtoupper($reminder['priority']) }} PRIORITY
                        </span>
                    </div>
                    <p class="text-gray-700">{{ $reminder['message'] }}</p>
                    
                    @if($reminder['type'] === 'budget_warning' || $reminder['type'] === 'budget_exceeded')
                    <div class="mt-3">
                        <a href="{{ route('budgets.index') }}" class="text-blue-600 hover:underline text-sm font-medium">
                            View Budgets →
                        </a>
                    </div>
                    @endif

                    @if($reminder['type'] === 'saving_deadline')
                    <div class="mt-3">
                        <a href="{{ route('savings.index') }}" class="text-blue-600 hover:underline text-sm font-medium">
                            View Savings Goals →
                        </a>
                    </div>
                    @endif

                    @if($reminder['type'] === 'no_transaction')
                    <div class="mt-3">
                        <a href="{{ route('transactions.create') }}" class="text-blue-600 hover:underline text-sm font-medium">
                            Add Transaction →
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
        <span class="text-6xl">✅</span>
        <h2 class="text-2xl font-bold mt-4 text-gray-800">All Clear!</h2>
        <p class="text-gray-500 mt-2">No reminders at the moment. You're doing great!</p>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl p-6 border border-blue-200">
        <h3 class="font-bold text-lg mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <a href="{{ route('transactions.create') }}" class="bg-white rounded-lg p-4 text-center hover:shadow-md transition">
                <span class="text-3xl">📝</span>
                <p class="text-sm font-medium mt-2">Add Transaction</p>
            </a>
            <a href="{{ route('budgets.create') }}" class="bg-white rounded-lg p-4 text-center hover:shadow-md transition">
                <span class="text-3xl">💰</span>
                <p class="text-sm font-medium mt-2">Create Budget</p>
            </a>
            <a href="{{ route('savings.create') }}" class="bg-white rounded-lg p-4 text-center hover:shadow-md transition">
                <span class="text-3xl">🎯</span>
                <p class="text-sm font-medium mt-2">New Goal</p>
            </a>
            <a href="{{ route('reports.index') }}" class="bg-white rounded-lg p-4 text-center hover:shadow-md transition">
                <span class="text-3xl">📊</span>
                <p class="text-sm font-medium mt-2">View Reports</p>
            </a>
        </div>
    </div>
</div>
@endsection
