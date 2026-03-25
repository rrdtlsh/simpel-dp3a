<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengajuanController;

Route::get('/', function () {
    return view('start.home');
})->name('home');

// ==========================================
// SEMUA RUTE YANG BUTUH LOGIN (AUTH)
// ==========================================
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');

    // ==========================================
    // 🧑‍💼 ROUTES ADMIN
    // ==========================================
    Route::middleware('isAdmin')->prefix('admin')->group(function () {

        // Dashboard Utama
        Route::get('/dashboard', function () {
            return view('admin.dashboardadmin');
        })->name('admin.dashboard');

        // CRUD Pengajuan (Permintaan Dokumen)
        Route::get('/pengajuan', [PengajuanController::class, 'adminIndex'])->name('admin.pengajuan');
        Route::post('/pengajuan/store', [PengajuanController::class, 'store'])->name('admin.pengajuan.store');
        Route::put('/pengajuan/{id}', [PengajuanController::class, 'update'])->name('admin.pengajuan.update');
        Route::delete('/pengajuan/{id}', [PengajuanController::class, 'destroy'])->name('admin.pengajuan.destroy');

        // Review / Verifikasi Dokumen
        Route::post('/pengajuan/file/{id}/review', [PengajuanController::class, 'review'])->name('admin.pengajuan.review');
        Route::get('/export/pdf', [PengajuanController::class, 'exportPdf'])->name('admin.export.pdf');
        Route::get('/export/excel', [PengajuanController::class, 'exportExcel'])->name('admin.export.excel');

        // --- KONTEN SPA (Single Page Application) ---
        Route::prefix('content')->group(function () {
            Route::get('/permintaan', [PengajuanController::class, 'adminContent']);
            Route::get('/verifikasi', [PengajuanController::class, 'verifikasiContent']);
            Route::get('/dokumen-masuk', [PengajuanController::class, 'dokumenMasukContent']);

            Route::get('/evaluasi-pug', function () {
                return '<div class="content"><h2>Pertanyaan Evaluasi PUG</h2><p>Halaman sedang dalam tahap pengembangan...</p></div>';
            });
        });
    });


    // ==========================================
    // 👩‍💻 ROUTES USER (SKPD/Bidang)
    // ==========================================

    // Menu KHP
    Route::prefix('user/khp')->group(function () {
        Route::get('/permintaan', [PengajuanController::class, 'index'])->name('khp.permintaan');
        Route::get('/unggah', function () {
            return view('user.khp.unggah');
        })->name('khp.unggah');
        Route::get('/pug', function () {
            return view('user.khp.pug');
        })->name('khp.pug');
    });

    // Pengajuan & Upload (Global User)
    Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
    Route::post('/pengajuan/{id}/upload', [PengajuanController::class, 'upload'])->name('pengajuan.upload');
});

// ==========================================
// LOGOUT (Lebih Aman dengan POST)
// ==========================================
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
})->middleware('auth')->name('logout');

require __DIR__ . '/auth.php';
