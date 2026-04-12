<?php
// FILE: app/Http/Controllers/HomeController.php
// (Sesuaikan dengan controller yang saat ini me-return view 'start.home')
// Jika saat ini route home langsung return view(), ubah menjadi controller ini.

namespace App\Http\Controllers;

use App\Models\Pengumuman;

class HomeController extends Controller
{
    public function index()
    {
        // Ambil hanya pengumuman aktif, tampilkan 6 terbaru
        $pengumumans = Pengumuman::active()
            ->latest()
            ->take(6)
            ->get();

        return view('start.home', compact('pengumumans'));
    }
}


// ============================================================
// TAMBAHKAN di routes/web.php (route publik — TANPA middleware):
// ============================================================
// use App\Http\Controllers\HomeController;
//
// Route::get('/', [HomeController::class, 'index'])->name('home');
// ============================================================
