<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\Pengajuan;
use App\Models\PengajuanFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengajuanController extends Controller
{
    public function index()
    {
        $pengajuans = Pengajuan::with('bidang')
            ->forCurrentUserBidang()
            ->latest()
            ->paginate(10);

        return view('user.khp.permintaan', compact('pengajuans'));
    }

    public function adminContent()
    {
        $pengajuans = Pengajuan::with('bidang')->latest()->get();
        $bidangs = Bidang::whereIn('id', function ($query) {
            $query->select('bidang_id')
                ->from('users')
                ->whereNotNull('bidang_id');
        })->get();

        return view('admin.pengajuan.partials.permintaan', compact('pengajuans', 'bidangs'));
    }

    // 🧑‍💼 ADMIN: LIST PENGAJUAN + MODAL CREATE
    public function adminIndex()
    {
        $pengajuans = Pengajuan::with('bidang')
            ->latest()
            ->paginate(10);

        $bidangs = Bidang::all();

        return view('admin.pengajuan.index', compact('pengajuans', 'bidangs'));
    }

    // 🧑‍💼 ADMIN BUAT PENGAJUAN
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => [
                'required',
                'string',
                function ($attr, $value, $fail) {
                    if (str_word_count((string) $value) > 20) {
                        $fail('Maksimal 20 kata');
                    }
                },
            ],
            'deskripsi' => [
                'required',
                'string',
                function ($attr, $value, $fail) {
                    if (str_word_count((string) $value) > 20) {
                        $fail('Maksimal 20 kata');
                    }
                },
            ],
            'bidang_id' => ['required', 'exists:bidangs,id'],
            'due_date' => [
                'required',
                'date',
                function ($attr, $value, $fail) {
                    if (Carbon::parse($value)->lessThanOrEqualTo(now())) {
                        $fail('Deadline harus di masa depan');
                    }
                },
            ],
        ]);

        $dueDate = Carbon::parse($validated['due_date']);

        DB::transaction(function () use ($validated, $dueDate) {

            Pengajuan::create([
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'bidang_id' => $validated['bidang_id'],
                'due_date' => $dueDate,
                'status' => 'open',
                'created_by' => Auth::id(),
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Permintaan berhasil dibuat']);
        }

        return redirect()->back()->with('success', 'Permintaan berhasil dibuat');
    }

    // 👩‍💻 USER UPLOAD FILE
    public function upload(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $file = $request->file('file')->store('pengajuan_files');

        PengajuanFile::create([
            'pengajuan_id' => $id,
            'user_id' => Auth::id(),
            'file_path' => $file
        ]);

        return back()->with('success', 'File berhasil diupload');
    }

    // 🧑‍💼 ADMIN REVIEW
    public function review(Request $request, $id)
    {
        $file = PengajuanFile::findOrFail($id);

        $file->update([
            'status' => $request->status,
            'admin_notes' => $request->notes
        ]);

        return back()->with('success', 'Berhasil direview');
    }
}
