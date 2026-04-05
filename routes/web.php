<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MemberLoginController;
use App\Http\Controllers\MemberPortalController;
use App\Http\Controllers\FineController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

// Member Portal
Route::group(['prefix' => 'member'], function () {
    Route::get('/login', [MemberLoginController::class, 'showLoginForm'])->name('member.login');
    Route::post('/login', [MemberLoginController::class, 'login']);
    Route::post('/logout', [MemberLoginController::class, 'logout'])->name('member.logout');

    Route::middleware('auth:member')->group(function () {
        Route::get('/dashboard', [MemberPortalController::class, 'dashboard'])->name('member.dashboard');
        Route::get('/history', [MemberPortalController::class, 'history'])->name('member.history');
        Route::get('/fines', [MemberPortalController::class, 'fines'])->name('member.fines');
    });
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:principal,librarian,teacher')->group(function () {
        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/overdue', [ReportController::class, 'overdueBooks'])->name('reports.overdue');
        Route::get('/reports/inventory', [ReportController::class, 'inventorySummary'])->name('reports.inventory');
        Route::get('/reports/incidents', [ReportController::class, 'incidents'])->name('reports.incidents');
        Route::get('/reports/ai-strategy', [ReportController::class, 'aiStrategy'])->name('reports.ai-strategy');
    });

    Route::middleware('role:librarian,teacher')->group(function () {
        // Book Issues
        Route::get('/issues', [IssueController::class, 'index'])->name('issues.index');
        Route::post('/issues', [IssueController::class, 'issueBook'])->name('issues.store');
        Route::post('/issues/{issue}/return', [IssueController::class, 'returnBook'])->name('issues.return');
        Route::post('/issues/{issue}/condition', [IssueController::class, 'markCondition'])->name('issues.condition');

        // POS Issue
        Route::get('/issues/pos', [IssueController::class, 'pos'])->name('issues.pos');
        Route::get('/issues/search-books', [IssueController::class, 'searchBooks'])->name('issues.search-books');
        Route::get('/issues/search-members', [IssueController::class, 'searchMembers'])->name('issues.search-members');
        Route::post('/issues/multiple', [IssueController::class, 'issueMultiple'])->name('issues.multiple');
    });

    Route::middleware('role:librarian')->group(function () {
        // Books
        Route::resource('books', BookController::class);

        // Categories
        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'destroy']);

        // Members
        Route::resource('members', MemberController::class);

        // Charges
        Route::get('/fines', [FineController::class, 'index'])->name('fines.index');
        Route::post('/fines/{fine}/resolve', [FineController::class, 'resolve'])->name('fines.resolve');

        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

require __DIR__.'/auth.php';
