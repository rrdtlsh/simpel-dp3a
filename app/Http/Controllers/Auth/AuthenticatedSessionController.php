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

    // Sesuaikan dengan kata 'admin' dan 'user' yang ada di database Anda
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard')
        ->with('success', 'Login Berhasil! Selamat Datang.');
    } 

    // Jika di database tulisannya 'user', maka arahkan ke khp.permintaan
    if ($user->role === 'user') {
        return redirect()->route('khp.permintaan')
        ->with('success', 'Login Berhasil! Selamat Datang.');
    }

    return redirect('/'); 
}
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
