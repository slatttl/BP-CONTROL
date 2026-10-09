<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('reviews.index')
    : view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::redirect('/dashboard', '/reviews')->name('dashboard');
    Route::get('/reviews/duplicate-check', [ReviewController::class, 'checkDuplicate'])->name('reviews.duplicate-check');
    Route::post('/reviews/draft', [ReviewController::class, 'saveDraft'])->name('reviews.draft.store');
    Route::post('/reviews/bulk/pdf', [ReviewController::class, 'bulkPdf'])->name('reviews.bulk.pdf');
    Route::post('/reviews/bulk/csv', [ReviewController::class, 'bulkCsv'])->name('reviews.bulk.csv');
    Route::get('/reviews/{review}/pdf', [ReviewController::class, 'pdf'])->name('reviews.pdf');
    Route::post('/reviews/{review}/draft', [ReviewController::class, 'saveDraft'])->name('reviews.draft.update');
    Route::post('/reviews/{review}/duplicate', [ReviewController::class, 'duplicate'])->name('reviews.duplicate');
    Route::resource('reviews', ReviewController::class);

    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
