<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        // 1. JIKA YANG LOGIN ADALAH ADMIN -> Lempar ke Dashboard Admin
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Login Berhasil! Selamat Datang Admin.');
        }

        // 2. JIKA YANG LOGIN ADALAH USER/BIDANG -> Lempar ke Daftar Permintaan
        // (Tidak peduli apakah dia KHP, PHA, PP, atau PKA, semua masuk lewat 1 pintu ini)
        return redirect()->route('user.dashboard')
            ->with('success', 'Login Berhasil! Selamat Datang.');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Anda telah berhasil logout dari sistem.');
    }
}
