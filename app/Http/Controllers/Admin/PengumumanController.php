<?php

// FILE: app/Http/Controllers/Admin/PengumumanController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePengumumanRequest;
use App\Http\Requests\Admin\UpdatePengumumanRequest;
use App\Models\Pengumuman;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PengumumanController extends Controller
{
    // ── Index (SPA partial view) ──────────────────────────────────────
    public function index(): View
    {
        $pengumumans = Pengumuman::with('creator')
            ->latest()
            ->limit(6)
            ->get();

        $pengumumans = Pengumuman::with('creator')->latest()->get();

        return view('admin.pengumuman.index', compact('pengumumans'));
    }

    // ── Store (AJAX — JSON, tanpa redirect) ───────────────────────────
    public function store(StorePengumumanRequest $request): JsonResponse
    {
        if (Pengumuman::count() >= 6) {
            return response()->json([
                'success' => false,
                'message' => 'Batas maksimal pengumuman adalah 6 data. Hapus data lama untuk menambah baru.'
            ], 422);
        }

        $path = $request->file('gambar')->store('pengumuman', 'public');

        $pengumuman = Pengumuman::create([
            'judul'       => $request->judul,
            'konten'      => $request->konten,
            'gambar'      => $path,
            'badge_label' => $request->badge_label,
            'badge_color' => $request->badge_color,
            'is_active'   => $request->boolean('is_active'),
            'created_by'  => Auth::id(),
        ]);

        return response()->json([
            'success'    => true,
            'message'    => 'Pengumuman berhasil ditambahkan.',
            'pengumuman' => $this->formatRow($pengumuman->fresh()),
        ], 201);
    }

    // ── Update (AJAX — JSON, tanpa redirect) ──────────────────────────
    public function update(UpdatePengumumanRequest $request, Pengumuman $pengumuman): JsonResponse
    {
        $data = [
            'judul'       => $request->judul,
            'konten'      => $request->konten,
            'badge_label' => $request->badge_label,
            'badge_color' => $request->badge_color,
            'is_active'   => $request->boolean('is_active'),
        ];

        if ($request->hasFile('gambar')) {
            if ($pengumuman->gambar && Storage::disk('public')->exists($pengumuman->gambar)) {
                Storage::disk('public')->delete($pengumuman->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('pengumuman', 'public');
        }

        $pengumuman->update($data);

        return response()->json([
            'success'    => true,
            'message'    => 'Pengumuman berhasil diperbarui.',
            'pengumuman' => $this->formatRow($pengumuman->fresh()),
        ]);
    }

    // ── Destroy ───────────────────────────────────────────────────────
    public function destroy(Pengumuman $pengumuman): JsonResponse
    {
        if ($pengumuman->gambar && Storage::disk('public')->exists($pengumuman->gambar)) {
            Storage::disk('public')->delete($pengumuman->gambar);
        }

        $pengumuman->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil dihapus.',
        ]);
    }

    // ── Toggle Status ─────────────────────────────────────────────────
    public function toggleStatus(Pengumuman $pengumuman): JsonResponse
    {
        $pengumuman->update(['is_active' => ! $pengumuman->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $pengumuman->is_active,
            'message'   => $pengumuman->is_active ? 'Pengumuman diaktifkan.' : 'Pengumuman dinonaktifkan.',
        ]);
    }

    // ── Private: format baris untuk JSON response ─────────────────────
    private function formatRow(Pengumuman $p): array
    {
        return [
            'id'          => $p->id,
            'judul'       => $p->judul,
            'konten'      => $p->konten,
            'badge_label' => $p->badge_label,
            'badge_color' => $p->badge_color,
            'is_active'   => $p->is_active,
            'gambar_url'  => $p->gambar_url,
            'created_at'  => $p->created_at->format('d M Y'),
            'update_url'  => route('admin.pengumuman.update', $p->id),
            'destroy_url' => route('admin.pengumuman.destroy', $p->id),
            'toggle_url'  => route('admin.pengumuman.toggle-status', $p->id),
        ];
    }
}
