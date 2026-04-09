@extends('layouts.user')
@section('title', 'Evaluasi Mandiri PUG')

@push('styles')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="{{ asset('css/evaluasi_pug.css') }}">
@endpush

@section('content')
<div class="epug-wrap">
    {{-- Header tanpa tombol Tambah Pertanyaan --}}
    <div class="epug-header">
        <div class="epug-header-left">
            <h2 class="epug-title">Evaluasi Mandiri PUG</h2>
            <div class="epug-tahun-wrap">
                <label class="epug-tahun-label">Tahun</label>
                <select id="epugTahunSelect" class="epug-tahun-select">
                    @for($y = date('Y') + 1; $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="epug-header-right">
            <button class="epug-btn epug-btn-excel" id="epugBtnExportExcel">
                <i class="fa-solid fa-file-excel"></i> Export Excel
            </button>
            <button class="epug-btn epug-btn-pdf" id="epugBtnExportPdf">
                <i class="fa-solid fa-file-pdf"></i> Export PDF
            </button>
        </div>
    </div>

    {{-- ── SCORING CARDS & CHART ── --}}
    <div class="epug-dashboard-top" style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 24px;">
        <div class="epug-score-card" style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 30px; background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(0,0,0,.07);">
            <span class="epug-score-label" style="font-size: 1rem; color: #4b5563; font-weight:700;">Total Capaian PUG</span>
            <div class="epug-score-value" style="font-size: 3.5rem; font-weight:800; color: #067fb2; margin: 15px 0; line-height:1;">
                {{ number_format($totalSkor, 2) }}
            </div>
            <div class="epug-progress-wrap" style="width: 100%; background: #f3f4f6; height: 12px; border-radius: 20px; overflow: hidden;">
                <div class="epug-progress-fill" style="width:{{ $persenTotal }}%; background: #067fb2; height: 100%; transition: width 1s ease;"></div>
            </div>
            <span class="epug-score-maks" style="margin-top: 12px; font-size: 0.85rem; color:#6b7280; font-weight:600;">
                Maksimal: {{ number_format($totalMaks, 2) }} ({{ $persenTotal }}%)
            </span>
        </div>

        <div class="epug-score-card" style="padding: 20px; background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(0,0,0,.07);">
            <span style="font-size:.9rem; font-weight:700; color:#111827;">Sebaran Skor per Komponen</span>
            <div id="epugComponentChart" style="min-height: 220px; margin-top: 10px;"></div>
        </div>
    </div>

    <div id="epugChartData" data-categories="{{ json_encode($chartData['categories']) }}" data-series="{{ json_encode($chartData['series']) }}" style="display:none;"></div>

    @php
        $tahunData = $tahun ?? date('Y');
    @endphp

    {{-- ── CARD PROGRESS PER KOMPONEN ── --}}
    <div style="display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap;">
        @foreach($komponen ?? [] as $komp)
            @php
                $totalPertanyaan = 0;
                $terjawab = 0;
                foreach($komp->indikator as $indk) {
                    $totalPertanyaan += $indk->pertanyaan->count();
                    foreach($indk->pertanyaan as $pert) {
                        $jwb = $pert->jawaban->where('tahun', $tahunData)->first();
                        if($jwb && in_array($jwb->status, ['diisi', 'disetujui'])) {
                            $terjawab++;
                        }
                    }
                }
            @endphp
            <div style="flex: 1; min-width: 160px; background: #fff; padding: 15px; border-radius: 10px; border-left: 5px solid #067fb2; box-shadow: var(--ep-shadow);">
                <div style="font-size: 0.85rem; color: #4b5563; font-weight: 700;">Komp {{ $komp->kode }}</div>
                <div style="font-size: 1.4rem; font-weight: 800; color: #111827; margin: 4px 0;">{{ $terjawab }} / {{ $totalPertanyaan }}</div>
                <div style="font-size: 0.75rem; color: #6b7280;">Pertanyaan Terjawab</div>
            </div>
        @endforeach
    </div>

    {{-- ── HEADER STRUKTUR & SEARCH BAR ── --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap:wrap; gap:10px;">
        <h3 style="margin: 0; font-size: 1.1rem; color: #111827;">Struktur Pertanyaan PUG</h3>
        <div style="position: relative; width: 100%; max-width: 300px;">
            <input type="text" id="epugSearchInput" class="epug-input" placeholder="Cari pertanyaan atau kode..." style="padding-left: 36px; border-radius: 20px;">
            <i class="fa-solid fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
        </div>
    </div>

    {{-- ── TREE: Komponen → Indikator → Pertanyaan ── --}}
    <div id="epugStrukturContainer">
        @forelse($komponen ?? [] as $komp)
        <div class="epug-komponen-block">
            <div class="epug-komponen-header" data-toggle="komp-{{ $komp->id }}">
                <div class="epug-komponen-toggle">
                    <i class="fa-solid fa-chevron-down epug-chevron open" id="chevron-komp-{{ $komp->id }}"></i>
                    <span class="epug-komponen-label">{{ $komp->kode }}. {{ $komp->nama }}</span>
                </div>
                @php
                    $kompSkor = 0; $kompMaks = 0;
                    foreach($komp->indikator as $indk) {
                        foreach($indk->pertanyaan as $pert) {
                            $kompMaks += $pert->skor_maksimal;
                            $jwb = $pert->jawaban->where('tahun', $tahunData)->first();
                            if($jwb) $kompSkor += $jwb->skor;
                        }
                    }
                @endphp
                <span class="epug-komponen-skor">Skor: {{ number_format($kompSkor, 2) }} / {{ number_format($kompMaks, 2) }}</span>
            </div>

            <div class="epug-komponen-body" id="komp-{{ $komp->id }}">
                @foreach($komp->indikator as $indk)
                <div class="epug-indikator-block">
                    <div class="epug-indikator-header" data-toggle="indk-{{ $indk->id }}">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <i class="fa-solid fa-chevron-down epug-chevron-sm open" id="chevron-indk-{{ $indk->id }}"></i>
                            <span class="epug-indikator-label">{{ $indk->kode }}. {{ $indk->nama }}</span>
                        </div>
                    </div>

                    <div class="epug-indikator-body" id="indk-{{ $indk->id }}" style="display:block;">
                        @foreach($indk->pertanyaan as $pert)
                            @php
                                $jwb = $pert->jawaban->where('tahun', $tahunData)->first();
                                $status = $jwb ? $jwb->status : 'belum';
                                $statusClass = match($status) {
                                    'disetujui' => 'epug-status-green',
                                    'diisi'     => 'epug-status-blue',
                                    'ditolak'   => 'epug-status-red',
                                    default     => 'epug-status-red',
                                };
                                $statusLabel = match($status) {
                                    'disetujui' => 'Disetujui',
                                    'diisi'     => 'Sudah Diisi',
                                    'ditolak'   => 'Ditolak',
                                    default     => 'Belum Diisi',
                                };
                            @endphp
                        <div class="epug-pertanyaan-row" data-id="{{ $pert->id }}" data-tahun="{{ $tahunData }}" data-status="{{ $status }}">
                            <div class="epug-pertanyaan-left">
                                <i class="fa-regular fa-file-lines epug-pert-icon"></i>
                                <span class="epug-pert-text">
                                    {{ $pert->kode }}. {{ \Illuminate\Support\Str::limit($pert->pertanyaan, 90) }}
                                </span>
                            </div>
                            <div class="epug-pertanyaan-right">
                                <span class="epug-status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                <span class="epug-skor-text">
                                    Skor: {{ number_format($jwb?->skor ?? 0, 2) }} / {{ number_format($pert->skor_maksimal, 2) }}
                                </span>
                                {{-- Hanya tombol LIHAT, tidak ada edit dan hapus --}}
                                <button class="epug-btn-lihat epug-btn-lihat-trigger"
                                    data-pert-id="{{ $pert->id }}"
                                    data-tahun="{{ $tahunData }}">
                                    Jawab / Lihat
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="epug-empty">
            <i class="fa-regular fa-folder-open" style="font-size:2rem; color:#ccc;"></i>
            <p>Belum ada data komponen PUG.</p>
        </div>
        @endforelse
    </div>
    
    <div id="epugSearchEmpty" class="epug-empty" style="display:none; padding: 50px 20px; text-align: center; background: #fff; border-radius: 10px; margin-top: 15px; box-shadow: var(--ep-shadow);">
        <i class="fa-solid fa-magnifying-glass" style="font-size:3rem; color:#d1d5db; margin-bottom: 12px;"></i>
        <h4 style="margin: 0 0 6px 0; color: #374151; font-size: 1.1rem;">Hasil Pencarian Tidak Ditemukan</h4>
        <p style="margin: 0; color: #9ca3af; font-size: 0.85rem;">Coba gunakan kata kunci atau kode pertanyaan yang lain.</p>
    </div>
</div>

{{-- MODAL JAWAB PERTANYAAN (USER) --}}
<div id="epugModal" class="epug-modal-overlay" style="display:none;" role="dialog" aria-modal="true">
    <div class="epug-modal-dialog">
        <div class="epug-modal-header">
            <div>
                <div class="epug-modal-breadcrumb" id="epugModalBreadcrumb">Evaluasi / Evaluasi Mandiri / Entri</div>
                <h3 class="epug-modal-title" id="epugModalTitle">Jawab Pertanyaan</h3>
            </div>
            <button class="epug-modal-close" id="epugModalClose" aria-label="Tutup">&times;</button>
        </div>

        <div id="epugModalLoader" class="epug-modal-loader">
            <i class="fa-solid fa-spinner fa-spin fa-2x" style="color:#067fb2;"></i>
            <p>Memuat data...</p>
        </div>

        <div id="epugModalBody" class="epug-modal-body" style="display:none;">
            <div id="epugStatusAlert" class="epug-alert" style="display:none;"></div>
            <div id="epugPetunjukAlert" class="epug-alert" style="display:none; background:#f0f9ff; border:1px solid #bae6fd; color:#0369a1; margin-bottom:15px;">
                <i class="fa-solid fa-circle-info"></i>
                <span id="epugModalPetunjuk" style="margin-left:8px; line-height:1.4;"></span>
            </div>
            <div class="epug-modal-kode" id="epugModalKode"></div>

            <div class="epug-section">
                <h4 class="epug-section-title" style="display:flex; justify-content:space-between; align-items:center;">
                    Pilihan
                    <button type="button" class="epug-btn-icon" id="epugBtnClearPilihan" title="Bersihkan Pilihan" style="color:#ef4444; font-size:0.8rem; border:1px solid #ef4444; padding:2px 8px; border-radius:4px;">
                        <i class="fa-solid fa-eraser"></i> Bersihkan
                    </button>
                </h4>
                <div id="epugPilihanContainer" class="epug-pilihan-wrap"></div>
                <p id="epugErrJawaban" class="epug-field-error" style="display:none;"></p>
            </div>

            <div class="epug-section">
                <h4 class="epug-section-title">Catatan <span id="epugCatatanCounter" class="epug-counter">0/1000</span></h4>
                <textarea id="epugCatatan" class="epug-textarea" rows="5" placeholder="Tulis catatan pendukung jawaban..." maxlength="1100"></textarea>
                <p id="epugErrCatatan" class="epug-field-error" style="display:none;"></p>
            </div>

            <div class="epug-section">
                <h4 class="epug-section-title">Lampiran</h4>
                <div id="epugLampiranList" class="epug-lampiran-list"></div>
                <div class="epug-upload-zone" id="epugUploadZone">
                   <input type="file" id="epugFileInput" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" style="display:none;" multiple>
                    <label for="epugFileInput" class="epug-upload-label">
                        <i class="fa-solid fa-paperclip"></i>
                        <span>Klik untuk upload (PDF, DOC, XLS, IMG — maks 10 MB)</span>
                    </label>
                </div>
                <p id="epugErrFile" class="epug-field-error" style="display:none;"></p>
            </div>

            {{-- Kolom Verifikasi Admin dihilangkan --}}
            <div id="epugSkorSection" class="epug-skor-section" style="display:none;">
                <div class="epug-skor-row">
                    <div>
                        <strong>Skor Tersimpan</strong>
                        <div id="epugSkorValue" class="epug-skor-value"></div>
                    </div>
                    <div>
                        <strong>Jawaban</strong>
                        <div id="epugJawabanSaved" class="epug-jawaban-saved"></div>
                    </div>
                </div>
            </div>

            {{-- Catatan dari Admin (Hanya dibaca, jika ada) --}}
            <div id="epugCatatanAdminReadonly" class="epug-section" style="display:none; background: #fdf2f8; border-left: 4px solid #be185d; padding: 12px; margin-top: 15px; border-radius: 4px;">
                <strong style="color: #9d174d; font-size: 13px;"><i class="fa-solid fa-comment-dots"></i> Catatan Verifikasi Admin:</strong>
                <div id="epugCatatanAdminText" style="margin-top: 5px; font-size: 13.5px; color: #831843;"></div>
            </div>

            <div class="epug-section" style="margin-top: 15px;">
                <button class="epug-collapse-btn" id="epugAuditToggle">
                    <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Perubahan
                    <i class="fa-solid fa-chevron-down" id="epugAuditChevron"></i>
                </button>
                <div id="epugAuditBody" class="epug-audit-body" style="display:none;">
                    <div id="epugAuditList"></div>
                </div>
            </div>
        </div>

        <div id="epugModalFooter" class="epug-modal-footer" style="display:none;">
            <button class="epug-btn epug-btn-secondary" id="epugBtnTutup">Tutup</button>
            <button class="epug-btn epug-btn-primary" id="epugBtnSimpan">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Jawaban
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    {{-- ✅ JS Khusus User (Segera kita buat di Tahap 4) --}}
    <script src="{{ asset('js/user_evaluasi_pug.js') }}?v={{ time() }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if(typeof UserEvaluasiPUG !== 'undefined') {
                UserEvaluasiPUG.init({
                    tahun: {{ $tahun }},
                    routes: {
                        show: '/user/evaluasi-pug/pertanyaan/',
                        simpan: '/user/evaluasi-pug/jawaban',
                        uploadFile: '/user/evaluasi-pug/lampiran',
                        hapusFile: '/user/evaluasi-pug/lampiran/',
                        exportExcel: '/user/evaluasi-pug/export/excel',
                        exportPdf: '/user/evaluasi-pug/export/pdf'
                    }
                });
            }
        });
    </script>
@endpush