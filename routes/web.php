<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengajuanController;

Route::get('/', function () {
    return view('start.home');
})->name('home');

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    // ADMIN
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboardadmin');
    })->name('admin.dashboard');

    // ADMIN: Permintaan Dokumen (LIST + MODAL)
    Route::middleware('isAdmin')->group(function () {
        Route::get('/admin/pengajuan', [PengajuanController::class, 'adminIndex'])
            ->name('admin.pengajuan');

        Route::post('/admin/pengajuan/store', [PengajuanController::class, 'store'])
            ->name('admin.pengajuan.store');
    });

    // ADMIN: Konten SPA-like (tanpa pindah halaman)
    Route::get('/admin/content/permintaan', [PengajuanController::class, 'adminContent'])
        ->middleware(['auth', 'isAdmin']);

    // USER
    Route::get('/user/khp/permintaan', [PengajuanController::class, 'index'])
        ->name('khp.permintaan');

    Route::get('/user/khp/unggah', function () {
        return view('user.khp.unggah');
    })->name('khp.unggah');

    Route::get('/user/khp/pug', function () {
        return view('user.khp.pug');
    })->name('khp.pug');

    // PENGAJUAN
    Route::get('/pengajuan', [PengajuanController::class, 'index'])
        ->name('pengajuan.index');

    Route::prefix('pengajuan')->group(function () {

        // ✅ ADMIN ONLY
        Route::middleware('isAdmin')->group(function () {
            Route::post('/file/{id}/review', [PengajuanController::class, 'review'])
                ->name('pengajuan.review');
        });

        // ✅ USER
        Route::post('/{id}/upload', [PengajuanController::class, 'upload'])
            ->name('pengajuan.upload');
    });
});

// LOGOUT (lebih aman)
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
})->middleware('auth')->name('logout');

require __DIR__ . '/auth.php';
