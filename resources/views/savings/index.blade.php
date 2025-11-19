@extends('layouts.app')

@section('title', 'Savings Goals')

@section('content')
<div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Savings Goals</h1>
            <p class="text-gray-500 text-sm mt-1">Track your financial goals and targets</p>
        </div>
        <a href="{{ route('savings.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-medium">
            + Add Goal
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($savings as $saving)
        <div class="border border-gray-200 rounded-xl p-6 hover:shadow-lg transition">
            <!-- Header -->
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center text-3xl" style="background-color: {{ $saving->color }}20;">
                        {{ $saving->icon ?? '🎯' }}
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">{{ $saving->name }}</h3>
                        @if($saving->target_date)
                        <p class="text-xs text-gray-500">Target: {{ $saving->target_date->format('d M Y') }}</p>
                        @endif
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold
                    {{ $saving->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $saving->status === 'active' ? 'bg-blue-100 text-blue-700' : '' }}
                    {{ $saving->status === 'paused' ? 'bg-gray-100 text-gray-700' : '' }}">
                    {{ ucfirst($saving->status) }}
                </span>
            </div>

            @if($saving->description)
            <p class="text-sm text-gray-600 mb-4">{{ $saving->description }}</p>
            @endif

            <!-- Progress -->
            <div class="space-y-3 mb-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Progress</span>
                    <span class="font-bold" style="color: {{ $saving->color }}">{{ number_format($saving->progressPercentage(), 1) }}%</span>
                </div>
                
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="h-3 rounded-full transition-all" 
                         style="width: {{ $saving->progressPercentage() }}%; background-color: {{ $saving->color }}"></div>
                </div>
                
                <div class="flex justify-between text-sm">
                    <div>
                        <p class="text-gray-500 text-xs">Current</p>
                        <p class="font-bold">Rp {{ number_format($saving->current_amount, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-gray-500 text-xs">Target</p>
                        <p class="font-bold">Rp {{ number_format($saving->target_amount, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="pt-2 border-t">
                    <p class="text-xs text-gray-500">Remaining</p>
                    <p class="font-bold text-lg" style="color: {{ $saving->color }}">
                        Rp {{ number_format($saving->remainingAmount(), 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-2">
                <div class="flex gap-2">
                    <button onclick="openAddModal({{ $saving->id }}, '{{ $saving->name }}')" 
                            class="flex-1 bg-green-50 text-green-600 py-2 rounded-lg hover:bg-green-100 text-sm font-medium">
                        + Add Money
                    </button>
                    <button onclick="openWithdrawModal({{ $saving->id }}, '{{ $saving->name }}', {{ $saving->current_amount }})" 
                            class="flex-1 bg-orange-50 text-orange-600 py-2 rounded-lg hover:bg-orange-100 text-sm font-medium">
                        - Withdraw
                    </button>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('savings.edit', $saving) }}" class="flex-1 text-center bg-blue-50 text-blue-600 py-2 rounded-lg hover:bg-blue-100 text-sm font-medium">
                        Edit
                    </a>
                    <form action="{{ route('savings.destroy', $saving) }}" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-50 text-red-600 py-2 rounded-lg hover:bg-red-100 text-sm font-medium" 
                                onclick="return confirm('Delete this saving goal?')">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-20">
            <div class="text-6xl mb-4">🎯</div>
            <p class="text-gray-400 text-xl">No Savings Goals Yet</p>
            <p class="text-gray-400 text-sm mt-2">Create your first savings goal to start tracking</p>
            <a href="{{ route('savings.create') }}" class="inline-block mt-4 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-medium">
                Create First Goal
            </a>
        </div>
        @endforelse
    </div>
</div>

<!-- Add Money Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4">
        <h2 class="text-2xl font-bold mb-4">Add Money</h2>
        <p class="text-gray-600 mb-6">Add money to: <span id="addModalName" class="font-semibold"></span></p>
        <form id="addForm" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-sm font-semibold mb-2">Amount</label>
                <input type="number" name="amount" class="w-full px-4 py-3 border border-gray-300 rounded-lg" 
                       placeholder="100000" step="0.01" required>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 font-semibold">
                    Add Money
                </button>
                <button type="button" onclick="closeAddModal()" class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-lg hover:bg-gray-200 font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Withdraw Modal -->
<div id="withdrawModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4">
        <h2 class="text-2xl font-bold mb-4">Withdraw Money</h2>
        <p class="text-gray-600 mb-2">Withdraw from: <span id="withdrawModalName" class="font-semibold"></span></p>
        <p class="text-sm text-gray-500 mb-6">Available: Rp <span id="withdrawModalAmount"></span></p>
        <form id="withdrawForm" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-sm font-semibold mb-2">Amount</label>
                <input type="number" name="amount" class="w-full px-4 py-3 border border-gray-300 rounded-lg" 
                       placeholder="50000" step="0.01" required>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-orange-600 text-white py-3 rounded-lg hover:bg-orange-700 font-semibold">
                    Withdraw
                </button>
                <button type="button" onclick="closeWithdrawModal()" class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-lg hover:bg-gray-200 font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal(id, name) {
    document.getElementById('addModal').classList.remove('hidden');
    document.getElementById('addModalName').textContent = name;
    document.getElementById('addForm').action = `/savings/${id}/add`;
}

function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
}

function openWithdrawModal(id, name, amount) {
    document.getElementById('withdrawModal').classList.remove('hidden');
    document.getElementById('withdrawModalName').textContent = name;
    document.getElementById('withdrawModalAmount').textContent = new Intl.NumberFormat('id-ID').format(amount);
    document.getElementById('withdrawForm').action = `/savings/${id}/withdraw`;
}

function closeWithdrawModal() {
    document.getElementById('withdrawModal').classList.add('hidden');
}
</script>
@endsection
