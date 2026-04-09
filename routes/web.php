<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\EvaluasiPugController; // ✅ TAMBAHKAN INI
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('start.home');
})->name('home');

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');

    // ── Notifikasi ──────────────────────────────────────────────────────────
    Route::post('/notifications/{id}/read', function (Request $request, $id) {
        $user = $request->user();
        $notif = $user->notifications()->find($id);
        if ($notif) $notif->markAsRead();
        return response()->json(['ok' => true]);
    })->name('notifications.read');

    Route::post('/notifications/mark-all-read', function (Request $request) {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['ok' => true]);
    })->name('notifications.markAllRead');

    // ── ADMIN ────────────────────────────────────────────────────────────────
    Route::middleware('isAdmin')->prefix('admin')->group(function () {

        Route::get('/dashboard', function () {
            return view('admin.dashboardadmin');
        })->name('admin.dashboard');

        Route::get('/pengajuan', [PengajuanController::class, 'adminIndex'])->name('admin.pengajuan');
        Route::post('/pengajuan/store', [PengajuanController::class, 'store'])->name('admin.pengajuan.store');
        Route::put('/pengajuan/{id}', [PengajuanController::class, 'update'])->name('admin.pengajuan.update');
        Route::delete('/pengajuan/{id}', [PengajuanController::class, 'destroy'])->name('admin.pengajuan.destroy');

        Route::post('/pengajuan/file/{id}/review', [PengajuanController::class, 'review'])->name('admin.pengajuan.review');
        Route::get('/export/pdf', [PengajuanController::class, 'exportPdf'])->name('admin.export.pdf');
        Route::get('/export/excel', [PengajuanController::class, 'exportExcel'])->name('admin.export.excel');

        // ✅ ROUTING SPA CONTENT (Bagian Kanan Dashboard)
        Route::prefix('content')->group(function () {
            Route::get('/permintaan', [PengajuanController::class, 'adminContent']);
            Route::get('/verifikasi', [PengajuanController::class, 'verifikasiContent']);
            Route::get('/dokumen-masuk', [PengajuanController::class, 'dokumenMasukContent']);
            // ✅ TAMBAHAN UNTUK PUG (Pastikan Claude sudah membuat fungsi 'index' di controllernya)
            Route::get('/evaluasi-pug', [EvaluasiPugController::class, 'index']);
        });

        // ✅ ROUTING AKSI EVALUASI PUG (Berdasarkan instruksi Claude)
        Route::prefix('evaluasi-pug')->name('evaluasi-pug.')->group(function () {
            Route::get('/pertanyaan/{id}', [EvaluasiPugController::class, 'show'])->name('show');
            Route::put('/pertanyaan/{id}', [EvaluasiPugController::class, 'updatePertanyaan'])->name('pertanyaan.update');
            Route::delete('/pertanyaan/{id}', [EvaluasiPugController::class, 'hapusPertanyaan'])->name('pertanyaan.destroy');
            Route::post('/jawaban', [EvaluasiPugController::class, 'simpanJawaban'])->name('jawaban.simpan');
            Route::post('/lampiran', [EvaluasiPugController::class, 'uploadLampiran'])->name('lampiran.upload');
            Route::delete('/lampiran/{id}', [EvaluasiPugController::class, 'hapusLampiran'])->name('lampiran.hapus');
            Route::post('/verifikasi', [EvaluasiPugController::class, 'verifikasi'])->name('verifikasi');
            Route::post('/pertanyaan', [EvaluasiPugController::class, 'tambahPertanyaan'])->name('pertanyaan.tambah');
            Route::get('/export/excel', [EvaluasiPugController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf', [EvaluasiPugController::class, 'exportPdf'])->name('export.pdf');
        });
    });

    // ── USER ─────────────────────────────────────────────────────────────────
    Route::middleware(['auth'])->prefix('user')->name('user.')->group(function () {
        Route::get('/dashboard', [PengajuanController::class, 'userDashboard'])->name('dashboard');
        Route::get('/permintaan', [PengajuanController::class, 'userPermintaan'])->name('permintaan');
        Route::get('/arsip', [PengajuanController::class, 'userArsip'])->name('arsip');
        Route::get('/export/pdf', [PengajuanController::class, 'exportPdfUser'])->name('export.pdf');
        Route::get('/export/excel', [PengajuanController::class, 'exportExcelUser'])->name('export.excel');

        Route::prefix('evaluasi-pug')->name('evaluasi-pug.')->group(function () {
            // Kita akan buat UserEvaluasiPugController setelah ini
            Route::get('/', [\App\Http\Controllers\UserEvaluasiPugController::class, 'index'])->name('index');
            Route::get('/pertanyaan/{id}', [\App\Http\Controllers\UserEvaluasiPugController::class, 'show'])->name('show');
            Route::post('/jawaban', [\App\Http\Controllers\UserEvaluasiPugController::class, 'simpanJawaban'])->name('jawaban.simpan');
            Route::post('/lampiran', [\App\Http\Controllers\UserEvaluasiPugController::class, 'uploadLampiran'])->name('lampiran.upload');
            Route::delete('/lampiran/{id}', [\App\Http\Controllers\UserEvaluasiPugController::class, 'hapusLampiran'])->name('lampiran.hapus');

            Route::get('/export/excel', [\App\Http\Controllers\UserEvaluasiPugController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf', [\App\Http\Controllers\UserEvaluasiPugController::class, 'exportPdf'])->name('export.pdf');
        });
    });

    Route::post('/pengajuan/{id}/upload', [PengajuanController::class, 'upload'])->name('pengajuan.upload');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->middleware('auth')->name('logout');

require __DIR__ . '/auth.php';
