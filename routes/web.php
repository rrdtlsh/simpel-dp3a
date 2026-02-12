<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('start.home');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
});
Route::middleware(['auth'])->group(function () {
    // Ganti view('admin.dashboard') menjadi view('admin.dashboardadmin')
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboardadmin');
    })->name('admin.dashboard');

    Route::get('/user/khp/permintaan', function () {
        return view('user.khp.permintaan');
    })->name('khp.permintaan');

    Route::get('/user/khp/unggah', function () {
        return view('user.khp.unggah');
    })->name('khp.unggah');

    Route::get('/user/khp/pug', function () {
        return view('user.khp.pug');
    })->name('khp.pug');
});

Route::get('/force-logout', function () {
    Auth::logout();
    session()->flush();
    return "Berhasil Logout! Silakan coba akses /login lagi.";
});

require __DIR__ . '/auth.php';
