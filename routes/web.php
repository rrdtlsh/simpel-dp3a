<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('start.home');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
});
Route::middleware(['auth', 'verified'])->group(function () {
    // Ganti view('admin.dashboard') menjadi view('admin.dashboardadmin')
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboardadmin');
    })->name('admin.dashboard');
});

require __DIR__ . '/auth.php';
