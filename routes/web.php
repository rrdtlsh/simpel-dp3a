<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\EvaluasiPugController;
use App\Http\Controllers\Admin\ManageUserController;
use App\Http\Controllers\Admin\PengumumanController;
use App\Http\Controllers\HomeController;
use Illuminate\Http\Request;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])
        ->name('profile.change-password');

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

    Route::middleware('isAdmin')->prefix('admin')->group(function () {

        Route::get('/dashboard', function () {
            return view('admin.dashboardadmin');
        })->name('admin.dashboard');

        Route::get('/pengajuan', [PengajuanController::class, 'adminIndex'])
            ->name('admin.pengajuan');
        Route::post('/pengajuan/store', [PengajuanController::class, 'store'])
            ->name('admin.pengajuan.store');
        Route::put('/pengajuan/{id}', [PengajuanController::class, 'update'])
            ->name('admin.pengajuan.update');
        Route::delete('/pengajuan/{id}', [PengajuanController::class, 'destroy'])
            ->name('admin.pengajuan.destroy');

        Route::post('/pengajuan/file/{id}/review', [PengajuanController::class, 'review'])
            ->name('admin.pengajuan.review');
        Route::get('/export/pdf', [PengajuanController::class, 'exportPdf'])
            ->name('admin.export.pdf');
        Route::get('/export/excel', [PengajuanController::class, 'exportExcel'])
            ->name('admin.export.excel');

        Route::prefix('content')->group(function () {
            Route::get('/permintaan', [PengajuanController::class, 'adminContent']);
            Route::get('/verifikasi', [PengajuanController::class, 'verifikasiContent']);
            Route::get('/dokumen-masuk', [PengajuanController::class, 'dokumenMasukContent']);
            Route::get('/evaluasi-pug', [EvaluasiPugController::class, 'index']);

            Route::get('/manage_users', [ManageUserController::class, 'index']);
            Route::get('/pengumuman', [PengumumanController::class, 'index']);
        });

        Route::prefix('evaluasi-pug')->name('evaluasi-pug.')->group(function () {
            Route::get('/pertanyaan/{id}', [EvaluasiPugController::class, 'show'])
                ->name('show');
            Route::put('/pertanyaan/{id}', [EvaluasiPugController::class, 'updatePertanyaan'])
                ->name('pertanyaan.update');
            Route::delete('/pertanyaan/{id}', [EvaluasiPugController::class, 'hapusPertanyaan'])
                ->name('pertanyaan.destroy');
            Route::post('/jawaban', [EvaluasiPugController::class, 'simpanJawaban'])
                ->name('jawaban.simpan');
            Route::post('/lampiran', [EvaluasiPugController::class, 'uploadLampiran'])
                ->name('lampiran.upload');
            Route::delete('/lampiran/{id}', [EvaluasiPugController::class, 'hapusLampiran'])
                ->name('lampiran.hapus');
            Route::post('/verifikasi', [EvaluasiPugController::class, 'verifikasi'])
                ->name('verifikasi');
            Route::post('/pertanyaan', [EvaluasiPugController::class, 'tambahPertanyaan'])
                ->name('pertanyaan.tambah');
            Route::get('/export/excel', [EvaluasiPugController::class, 'exportExcel'])
                ->name('export.excel');
            Route::get('/export/pdf', [EvaluasiPugController::class, 'exportPdf'])
                ->name('export.pdf');
        });

        Route::resource('manage_users', ManageUserController::class)
            ->names('admin.manage_users')
            ->parameters(['manage_users' => 'user'])
            ->except(['show', 'create', 'edit']);

        Route::post('manage_users/{user}/reset-password', [ManageUserController::class, 'resetPassword'])
            ->name('admin.manage_users.reset-password');

        Route::resource('pengumuman', PengumumanController::class)
            ->names('admin.pengumuman')
            ->except(['show', 'create', 'edit']);

        Route::post('pengumuman/{pengumuman}/toggle-status', [PengumumanController::class, 'toggleStatus'])
            ->name('admin.pengumuman.toggle-status');
    });

    Route::middleware(['auth'])->prefix('user')->name('user.')->group(function () {
        Route::get('/dashboard', [PengajuanController::class, 'userDashboard'])
            ->name('dashboard');
        Route::get('/permintaan', [PengajuanController::class, 'userPermintaan'])
            ->name('permintaan');
        Route::get('/arsip', [PengajuanController::class, 'userArsip'])->name('arsip');
        Route::get('/export/pdf', [PengajuanController::class, 'exportPdfUser'])
            ->name('export.pdf');
        Route::get('/export/excel', [PengajuanController::class, 'exportExcelUser'])
            ->name('export.excel');

        Route::prefix('evaluasi-pug')->name('evaluasi-pug.')->group(function () {
            Route::get('/', [\App\Http\Controllers\UserEvaluasiPugController::class, 'index'])
                ->name('index');
            Route::get('/pertanyaan/{id}', [\App\Http\Controllers\UserEvaluasiPugController::class, 'show'])
                ->name('show');
            Route::post('/jawaban', [\App\Http\Controllers\UserEvaluasiPugController::class, 'simpanJawaban'])
                ->name('jawaban.simpan');
            Route::post('/lampiran', [\App\Http\Controllers\UserEvaluasiPugController::class, 'uploadLampiran'])
                ->name('lampiran.upload');
            Route::delete('/lampiran/{id}', [\App\Http\Controllers\UserEvaluasiPugController::class, 'hapusLampiran'])
                ->name('lampiran.hapus');

            Route::get('/export/excel', [\App\Http\Controllers\UserEvaluasiPugController::class, 'exportExcel'])
                ->name('export.excel');
            Route::get('/export/pdf', [\App\Http\Controllers\UserEvaluasiPugController::class, 'exportPdf'])
                ->name('export.pdf');
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
