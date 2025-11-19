@extends('layouts.app')

@section('title', 'Add Transaction')

@section('content')
<div class="bg-white rounded-2xl shadow-sm p-8 max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Add New Transaction</h1>
        <p class="text-gray-500 text-sm mt-1">Record your income or expense</p>
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

    <form action="{{ route('transactions.store') }}" method="POST" class="space-y-5">
        @csrf
        
        <div>
            <label class="block text-sm font-semibold mb-2">Transaction Type</label>
            <select name="type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                <option value="expense">Expense</option>
                <option value="income">Income</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-2">Category</label>
            <select name="category_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                <option value="">Select Category</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->icon }} {{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-2">Budget (Optional)</label>
            <select name="budget_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">No budget</option>
                @foreach($budgets as $budget)
                <option value="{{ $budget->id }}">
                    {{ $budget->category->name }} - Rp {{ number_format($budget->remaining(), 0, ',', '.') }} remaining
                </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-2">Description</label>
            <input type="text" id="description" name="description" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Lunch at restaurant" required>
            <div id="ai-suggestion" class="hidden mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800">
                    <strong>🤖 AI Suggestion:</strong> <span id="suggestion-text"></span>
                </p>
                <button type="button" onclick="applySuggestion()" class="text-xs text-blue-600 hover:underline mt-1">Apply suggestion</button>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-2">Amount</label>
            <input type="number" name="amount" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="0" step="0.01" required>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-2">Transaction Date</label>
            <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-semibold">
                Save Transaction
            </button>
            <a href="{{ route('transactions.index') }}" class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-lg hover:bg-gray-200 font-semibold text-center">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
let suggestedCategoryId = null;

document.getElementById('description').addEventListener('input', function(e) {
    const description = e.target.value;
    if (description.length >= 3) {
        fetch('{{ route("ai.suggest-category") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ description: description })
        })
        .then(response => response.json())
        .then(data => {
            if (data.suggested_category && data.confidence > 0) {
                suggestedCategoryId = data.suggested_category.id;
                document.getElementById('suggestion-text').textContent = 
                    `${data.suggested_category.icon} ${data.suggested_category.name} (${data.confidence}% confidence)`;
                document.getElementById('ai-suggestion').classList.remove('hidden');
            } else {
                document.getElementById('ai-suggestion').classList.add('hidden');
            }
        });
    } else {
        document.getElementById('ai-suggestion').classList.add('hidden');
    }
});

function applySuggestion() {
    if (suggestedCategoryId) {
        document.querySelector('select[name="category_id"]').value = suggestedCategoryId;
        document.getElementById('ai-suggestion').classList.add('hidden');
    }
}
</script>
@endsection


<script>
let suggestedCategoryId = null;

document.getElementById('description').addEventListener('input', function(e) {
    const description = e.target.value;
    if (description.length >= 3) {
        fetch('{{ route("ai.suggest-category") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ description: description })
        })
        .then(response => response.json())
        .then(data => {
            if (data.suggested_category && data.confidence > 0) {
                suggestedCategoryId = data.suggested_category.id;
                document.getElementById('suggestion-text').textContent = 
                    `${data.suggested_category.icon} ${data.suggested_category.name} (${data.confidence}% confidence)`;
                document.getElementById('ai-suggestion').classList.remove('hidden');
            } else {
                document.getElementById('ai-suggestion').classList.add('hidden');
            }
        });
    } else {
        document.getElementById('ai-suggestion').classList.add('hidden');
    }
});

function applySuggestion() {
    if (suggestedCategoryId) {
        document.querySelector('select[name="category_id"]').value = suggestedCategoryId;
        document.getElementById('ai-suggestion').classList.add('hidden');
    }
}
</script>
