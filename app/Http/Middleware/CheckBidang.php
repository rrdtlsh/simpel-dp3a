<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBidang
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role = null): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        // Jika parameter "role" = admin, hanya admin yang boleh
        if ($role === 'admin' && $user->role !== 'admin') {
            abort(403, 'Hanya admin yang boleh mengakses.');
        }

        // Jika bukan admin, pastikan resource yg diakses sesuai bidang
        // Contoh: route model binding "pengajuan"
        $pengajuan = $request->route('pengajuan');

        if ($pengajuan && $user->role !== 'admin') {
            if ($pengajuan->bidang_id !== $user->bidang_id) {
                abort(403, 'Anda tidak berhak mengakses data bidang lain.');
            }
        }

        return $next($request);
    }
}
