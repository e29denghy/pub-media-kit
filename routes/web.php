<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublishCombinationController;
use App\Http\Controllers\PublishJobController;
use App\Publishing\PlatformAdapterRegistry;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function (PlatformAdapterRegistry $registry) {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'adapters' => $registry->catalog(),
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('drafts', DraftController::class);
    Route::post('/drafts/{draft}/jobs', [PublishJobController::class, 'store'])->name('drafts.jobs.store');

    Route::get('/jobs', [PublishJobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/{job}', [PublishJobController::class, 'show'])->name('jobs.show');
    Route::post('/targets/{target}/confirm', [PublishJobController::class, 'confirm'])->name('targets.confirm');

    Route::get('/combinations', [PublishCombinationController::class, 'index'])->name('combinations.index');
    Route::post('/combinations', [PublishCombinationController::class, 'store'])->name('combinations.store');
    Route::delete('/combinations/{combination}', [PublishCombinationController::class, 'destroy'])->name('combinations.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
