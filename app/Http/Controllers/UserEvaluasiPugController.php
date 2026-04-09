<?php

namespace App\Http\Controllers;

use App\Models\PugKomponen;
use App\Models\PugPertanyaan;
use App\Models\PugJawaban;
use App\Models\PugJawabanLampiran;
use App\Models\PugAuditLog;
use App\Models\PugJawabanVersi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserEvaluasiPugController extends Controller
{
    // ──────────────────────────────────────────────────────────────────
    // TAMPILAN HALAMAN UTAMA EVALUASI PUG (USER)
    // ──────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $tahun = (int) $request->get('tahun', date('Y'));

        $komponen = PugKomponen::with([
            'indikator.pertanyaan.jawaban' => function ($q) use ($tahun) {
                $q->where('tahun', $tahun);
            }
        ])->where('aktif', true)->orderBy('urutan')->get();

        $totalSkor = 0;
        $totalMaks = 0;
        $chartData = [
            'categories' => [],
            'series'     => []
        ];

        foreach ($komponen as $komp) {
            $skorKomp = 0;
            foreach ($komp->indikator as $indk) {
                foreach ($indk->pertanyaan as $pert) {
                    $totalMaks += $pert->skor_maksimal;

                    $jwb = $pert->jawaban->where('tahun', $tahun)->first();
                    if ($jwb && in_array($jwb->status, ['diisi', 'disetujui'])) {
                        $skorKomp += $jwb->skor;
                        $totalSkor += $jwb->skor;
                    }
                }
            }
            $chartData['categories'][] = 'Komp ' . $komp->kode;
            $chartData['series'][]     = round($skorKomp, 2);
        }

        $persenTotal = $totalMaks > 0 ? round(($totalSkor / $totalMaks) * 100, 1) : 0;

        // ✅ Arahkan ke view khusus User
        return view('user.evaluasi_pug.index', compact(
            'komponen',
            'tahun',
            'totalSkor',
            'totalMaks',
            'persenTotal',
            'chartData'
        ));
    }

    // ──────────────────────────────────────────────────────────────────
    // AMBIL DETAIL PERTANYAAN VIA AJAX
    // ──────────────────────────────────────────────────────────────────
    public function show(Request $request, $id)
    {
        $tahun      = $request->get('tahun', date('Y'));
        $pertanyaan = PugPertanyaan::with(['indikator.komponen'])->findOrFail($id);
        $jawaban    = PugJawaban::with(['lampiran', 'diisiOleh', 'diverifikasiOleh'])
            ->where('pertanyaan_id', $id)
            ->where('tahun', $tahun)
            ->first();
        $versi = PugJawabanVersi::with('user')
            ->where('jawaban_id', optional($jawaban)->id)
            ->orderByDesc('versi')
            ->get();
        $auditLog = PugAuditLog::with('user')
            ->where('jawaban_id', optional($jawaban)->id)
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        return response()->json([
            'pertanyaan' => $pertanyaan,
            'jawaban'    => $jawaban,
            'versi'      => $versi,
            'audit_log'  => $auditLog,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // SIMPAN JAWABAN (USER)
    // ──────────────────────────────────────────────────────────────────
    public function simpanJawaban(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pertanyaan_id' => 'required|exists:pug_pertanyaan,id',
            'tahun'         => 'required|integer|min:2020|max:2030',
            'jawaban_kode'  => 'nullable|string|max:100',
            'jawaban_label' => 'nullable|string|max:500',
            'catatan'       => 'nullable|string|max:1000|regex:/^[^<>{}\[\]\\\\]*$/',
            'skor'          => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        DB::transaction(function () use ($request) {
            $jawaban = PugJawaban::firstOrNew([
                'pertanyaan_id' => $request->pertanyaan_id,
                'tahun'         => $request->tahun,
            ]);

            if ($jawaban->exists && $jawaban->status === 'disetujui') {
                throw new \Exception('Jawaban yang sudah disetujui tidak dapat diubah.');
            }

            $sebelum = $jawaban->exists ? $jawaban->toArray() : null;
            $isNew = !$jawaban->exists;

            $statusBaru = empty($request->jawaban_kode) ? 'belum' : 'diisi';

            $jawaban->fill([
                'jawaban_kode'  => $request->jawaban_kode,
                'jawaban_label' => $request->jawaban_label,
                'catatan'       => $request->catatan,
                'skor'          => $request->skor ?? 0,
                'status'        => $statusBaru,
                'diisi_oleh'    => Auth::id(),
            ])->save();

            $versiTerbaru = PugJawabanVersi::where('jawaban_id', $jawaban->id)->max('versi') ?? 0;
            PugJawabanVersi::create([
                'jawaban_id'    => $jawaban->id,
                'user_id'       => Auth::id(),
                'versi'         => $versiTerbaru + 1,
                'jawaban_kode'  => $request->jawaban_kode,
                'jawaban_label' => $request->jawaban_label,
                'catatan'       => $request->catatan,
                'skor'          => $request->skor ?? 0,
                'status'        => $statusBaru,
            ]);

            PugAuditLog::create([
                'jawaban_id' => $jawaban->id,
                'user_id'    => Auth::id(),
                'aksi'       => $isNew ? 'isi_jawaban' : 'ubah_jawaban',
                'sebelum'    => $sebelum,
                'sesudah'    => $jawaban->fresh()->toArray(),
                'ip_address' => request()->ip(),
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Jawaban berhasil disimpan.']);
    }

    // ──────────────────────────────────────────────────────────────────
    // UPLOAD LAMPIRAN JAWABAN (USER)
    // ──────────────────────────────────────────────────────────────────
    public function uploadLampiran(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pertanyaan_id' => 'required|exists:pug_pertanyaan,id',
            'tahun'         => 'required|integer',
            'file'          => 'required|array|max:10',
            'file.*'        => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
        ], [
            'file.*.max'   => 'Masing-masing file maksimal 10 MB.',
            'file.*.mimes' => 'Terdapat format file yang tidak didukung.',
            'file.max'     => 'Anda hanya dapat mengunggah maksimal 10 file sekaligus.'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $jawaban = PugJawaban::firstOrCreate([
            'pertanyaan_id' => $request->pertanyaan_id,
            'tahun'         => $request->tahun,
        ]);

        if ($jawaban->status === 'disetujui') {
            return response()->json(['success' => false, 'message' => 'Tidak dapat merubah lampiran jawaban yang sudah disetujui.'], 403);
        }

        $jumlahLampiranSaatIni = PugJawabanLampiran::where('jawaban_id', $jawaban->id)->count();
        $jumlahUpload = count($request->file('file'));

        if ($jumlahLampiranSaatIni + $jumlahUpload > 10) {
            return response()->json([
                'success' => false,
                'message' => 'Batas maksimal tercapai! Total lampiran tidak boleh lebih dari 10 file (Saat ini: ' . $jumlahLampiranSaatIni . ' file).'
            ], 400);
        }

        $lampirans = [];
        foreach ($request->file('file') as $file) {
            $namaAsli  = $file->getClientOriginalName();
            $safeName  = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $namaAsli);
            $path      = $file->storeAs('pug_lampiran', $safeName, 'public');

            $lampiran = PugJawabanLampiran::create([
                'jawaban_id'     => $jawaban->id,
                'nama_file'      => $namaAsli,
                'path_file'      => $path,
                'mime_type'      => $file->getMimeType(),
                'ukuran'         => $file->getSize(),
                'diupload_oleh'  => Auth::id(),
            ]);
            $lampiran->url = Storage::url($path);
            $lampirans[] = $lampiran;
        }

        return response()->json([
            'success'    => true,
            'lampirans'  => $lampirans,
            'jawaban_id' => $jawaban->id
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // HAPUS LAMPIRAN (USER)
    // ──────────────────────────────────────────────────────────────────
    public function hapusLampiran($id)
    {
        $lampiran = PugJawabanLampiran::findOrFail($id);
        if ($lampiran->jawaban->status === 'disetujui') {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus file pada jawaban yang disetujui.'], 403);
        }
        if (Storage::disk('public')->exists($lampiran->path_file)) {
            Storage::disk('public')->delete($lampiran->path_file);
        }
        $lampiran->delete();
        return response()->json(['success' => true]);
    }

    // ──────────────────────────────────────────────────────────────────
    // EXPORT EXCEL (USER)
    // ──────────────────────────────────────────────────────────────────
    public function exportExcel(Request $request)
    {
        $tahun   = $request->get('tahun', date('Y'));
        $komponen = \App\Models\PugKomponen::with([
            'indikator.pertanyaan.jawaban' => fn($q) => $q->where('tahun', $tahun)->with('lampiran'),
        ])->where('aktif', true)->orderBy('urutan')->get();

        $data = [];
        foreach ($komponen as $komp) {
            foreach ($komp->indikator as $indk) {
                foreach ($indk->pertanyaan as $pert) {
                    $jwb = $pert->jawaban->first();
                    $data[] = [
                        $pert->kode,
                        $komp->nama,
                        $indk->nama,
                        $pert->pertanyaan,
                        $jwb?->jawaban_label ?? '-',
                        $jwb?->skor ?? 0,
                        $pert->skor_maksimal,
                        $jwb?->status ?? 'belum',
                        $jwb?->catatan ?? '-',
                        $jwb?->diisiOleh?->name ?? '-',
                        $jwb?->created_at?->format('d/m/Y H:i') ?? '-',
                        ($jwb?->lampiran ? $jwb->lampiran->count() : 0) . ' file',
                    ];
                }
            }
        }

        $headings = ['Kode', 'Komponen', 'Indikator', 'Pertanyaan', 'Jawaban', 'Skor', 'Skor Maks', 'Status', 'Catatan', 'Diisi Oleh', 'Tanggal Isi', 'Lampiran'];
        $fileName = 'evaluasi-mandiri-pug-' . $tahun . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DataExport($data, $headings), $fileName);
    }

    // ──────────────────────────────────────────────────────────────────
    // EXPORT PDF (USER)
    // ──────────────────────────────────────────────────────────────────
    public function exportPdf(Request $request)
    {
        $tahun   = $request->get('tahun', date('Y'));
        $komponen = \App\Models\PugKomponen::with([
            'indikator.pertanyaan.jawaban' => fn($q) => $q->where('tahun', $tahun)->with('lampiran'),
        ])->where('aktif', true)->orderBy('urutan')->get();

        // Gunakan view yang sama dengan admin agar seragam
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.evaluasi_pug.export_pdf', compact('komponen', 'tahun'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('evaluasi-pug-' . $tahun . '.pdf');
    }
}
