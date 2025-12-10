<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SavingController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiAssistantController;



// Landing page
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Guest routes (hanya untuk yang belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Protected routes (harus login)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/set-balance', [DashboardController::class, 'setInitialBalance'])->name('set.balance');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Profile routes
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('categories', CategoryController::class);
    Route::resource('budgets', BudgetController::class);
    Route::resource('transactions', TransactionController::class);
    Route::resource('savings', SavingController::class);
    
    // Savings actions
    Route::post('/savings/{saving}/add', [SavingController::class, 'addAmount'])->name('savings.add');
    Route::post('/savings/{saving}/withdraw', [SavingController::class, 'withdrawAmount'])->name('savings.withdraw');
    
    // Reports
    Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [App\Http\Controllers\ReportController::class, 'export'])->name('reports.export');
    
    // AI Features
    Route::post('/ai/chat', [AiAssistantController::class, 'chat'])->name('ai.chat');
    Route::post('/ai/new-session', [AiAssistantController::class, 'newSession'])->name('ai.newSession');
    Route::post('/ai/apply-recommendation', [AiAssistantController::class, 'applyRecommendation'])->name('ai.applyRecommendation');
    Route::post('/ai/suggest-category', [App\Http\Controllers\AIController::class, 'suggestCategory'])->name('ai.suggest-category');
    Route::get('/ai/analysis', [App\Http\Controllers\AIController::class, 'analyzeSpendingPattern'])->name('ai.analysis');
    Route::get('/ai/budget-recommendation', [App\Http\Controllers\AIController::class, 'recommendBudget'])->name('ai.budget-recommendation');
    Route::get('/ai/reminders', [App\Http\Controllers\AIController::class, 'smartReminders'])->name('ai.reminders');
    Route::post('/ai/apply-budget', [BudgetController::class, 'applyAI'])->name('ai.applyBudget');
    Route::get('/ai/auto-alert', [AiAssistantController::class, 'autoAlert'])->name('ai.autoAlert');
   
});
