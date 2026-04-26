<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ManageUserController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────
    public function index(): View
    {
        $users   = User::with('bidang')->latest()->get();
        $bidangs = Bidang::orderBy('nama')->get();

        return view('admin.manage_users.index', compact('users', 'bidangs'));
    }

    // ── Store ─────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'             => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z\s]+$/', 'unique:users,name'],
            'nip'              => ['required', 'digits:18', 'unique:users,nip'],
            'email'            => ['required', 'email', 'max:50', 'unique:users,email'],
            'password'         => ['required', 'string', 'min:6', 'max:18'],
            'role'             => ['required', 'in:admin,user'],
            'bidang_id'        => [Rule::requiredIf(fn() => $request->role === 'user')],
            'nama_bidang_baru' => [Rule::requiredIf(fn() => $request->bidang_id === 'baru'), 'nullable', 'string', 'max:50'],
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'name.regex'         => 'Nama tidak boleh mengandung karakter non-huruf/angka.',
            'name.unique'        => 'Nama ini sudah digunakan oleh akun lain.',
            'name.max'           => 'Nama maksimal 50 karakter.',
            'bidang_id.required' => 'Bidang wajib dipilih.',
            'nama_bidang_baru.required' => 'Nama bidang baru wajib diisi.',
            'nip.required'       => 'NIP wajib diisi.',
            'nip.digits'         => 'NIP harus tepat 18 digit angka.',
            'nip.unique'         => 'NIP ini sudah digunakan oleh akun lain.',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.max'          => 'Email maksimal 50 karakter.',
            'email.unique'       => 'Email ini sudah digunakan oleh akun lain.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 6 karakter.',
            'password.max'       => 'Password maksimal 18 karakter.',
            'role.required'      => 'Peran (role) wajib dipilih.',
        ]);

        $bidangId = $this->resolveBidangId($request);

        User::create([
            'name'      => $request->name,
            'nip'       => $request->nip,
            'email'     => $request->email,
            'role'      => $request->role,
            'password'  => Hash::make($request->password),
            'bidang_id' => $bidangId,
        ]);

        return response()->json(['success' => true, 'message' => 'Akun berhasil ditambahkan.']);
    }

    // ── Update ────────────────────────────────────────────────────────
    public function update(Request $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'             => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z\s]+$/', Rule::unique('users')->ignore($user->id)],
            'nip'              => ['required', 'digits:18', Rule::unique('users')->ignore($user->id)],
            'email'            => ['required', 'email', 'max:50', Rule::unique('users')->ignore($user->id)],
            'role'             => ['required', 'in:admin,user'],
            'password'         => ['nullable', 'string', 'min:6', 'max:18', 'confirmed'],
            'bidang_id'        => [Rule::requiredIf(fn() => $request->role === 'user')],
            'nama_bidang_baru' => [Rule::requiredIf(fn() => $request->bidang_id === 'baru'), 'nullable', 'string', 'max:50'],
        ], [
            'name.regex'         => 'Nama tidak boleh mengandung karakter non-huruf/angka.',
            'name.unique'        => 'Nama ini sudah digunakan oleh akun lain.',
            'bidang_id.required' => 'Bidang wajib dipilih.',
            'nama_bidang_baru.required' => 'Nama bidang baru wajib diisi.',
            'nip.digits'         => 'NIP harus tepat 18 digit angka.',
            'nip.unique'         => 'NIP ini sudah digunakan oleh akun lain.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email ini sudah digunakan oleh akun lain.',
            'password.min'       => 'Password baru minimal 6 karakter.',
            'password.max'       => 'Password baru maksimal 18 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $bidangId = $this->resolveBidangId($request);

        $data = [
            'name'      => $request->name,
            'nip'       => $request->nip,
            'email'     => $request->email,
            'role'      => $request->role,
            'bidang_id' => $bidangId,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        /*
         * ✅ DETEKSI PERUBAHAN DI BACKEND (double-layer protection):
         * Frontend sudah cek, tapi backend juga verifikasi
         * agar endpoint tidak bisa di-bypass via Postman/curl.
         */
        $changed = false;
        foreach (['name', 'nip', 'email', 'role'] as $field) {
            if ((string)$user->$field !== (string)$data[$field]) {
                $changed = true;
                break;
            }
        }
        if (!$changed && (string)($user->bidang_id ?? '') !== (string)($bidangId ?? '')) {
            $changed = true;
        }
        if (!$changed && $request->filled('password')) {
            $changed = true;
        }

        if (!$changed) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada perubahan yang disimpan.',
            ], 422);
        }

        $user->update($data);

        return response()->json(['success' => true, 'message' => 'Akun berhasil diperbarui.']);
    }

    // ── Destroy ───────────────────────────────────────────────────────
    public function destroy($id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus akun sendiri.'], 403);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'Akun berhasil dihapus.']);
    }

    // ── Reset Password ─────────────────────────────────────────────────
    public function resetPassword($id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat mereset password akun sendiri.'], 403);
        }

        $user->update(['password' => Hash::make('123456')]);

        return response()->json([
            'success' => true,
            'message' => "Password '{$user->name}' berhasil direset menjadi 123456.",
        ]);
    }

    // ── Private: resolve bidang_id ─────────────────────────────────────
    private function resolveBidangId(Request $request): ?int
    {
        $bidangId = $request->bidang_id;

        if ($bidangId === 'baru' && $request->filled('nama_bidang_baru')) {
            $bidang   = Bidang::firstOrCreate(['nama' => trim($request->nama_bidang_baru)]);
            $bidangId = $bidang->id;
        }

        return in_array($bidangId, ['', 'baru', 'kosong', null], true)
            ? null
            : (int) $bidangId;
    }
}
