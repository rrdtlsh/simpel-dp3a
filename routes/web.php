<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengajuanController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('start.home');
})->name('home');

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');

    // ── Notifikasi ──────────────────────────────────────────────────────────
    // Tandai satu notifikasi sebagai dibaca
    Route::post('/notifications/{id}/read', function (Request $request, $id) {
        /** @var \App\Models\User $user */
        $user = $request->user(); // Lebih aman dan mudah dibaca oleh sistem/editor

        $notif = $user->notifications()->find($id);
        if ($notif) {
            $notif->markAsRead();
        }

        return response()->json(['ok' => true]);
    })->name('notifications.read');

    // Tandai semua notifikasi sebagai dibaca
    Route::post('/notifications/mark-all-read', function (Request $request) {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->unreadNotifications->markAsRead();

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

        Route::prefix('content')->group(function () {
            Route::get('/permintaan', [PengajuanController::class, 'adminContent']);
            Route::get('/verifikasi', [PengajuanController::class, 'verifikasiContent']);
            Route::get('/dokumen-masuk', [PengajuanController::class, 'dokumenMasukContent']);
            Route::get('/evaluasi-pug', function () {
                return '<div class="content"><h2>Pertanyaan Evaluasi PUG</h2><p>Sedang dalam pengembangan...</p></div>';
            });
        });
    });

    // ── USER ─────────────────────────────────────────────────────────────────
    Route::middleware(['auth'])->prefix('user')->name('user.')->group(function () {
        Route::get('/dashboard', [PengajuanController::class, 'userDashboard'])->name('dashboard');
        Route::get('/permintaan', [PengajuanController::class, 'userPermintaan'])->name('permintaan');
        Route::get('/arsip', [PengajuanController::class, 'userArsip'])->name('arsip');
        Route::get('/export/pdf', [PengajuanController::class, 'exportPdfUser'])->name('export.pdf');
        Route::get('/export/excel', [PengajuanController::class, 'exportExcelUser'])->name('export.excel');
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
