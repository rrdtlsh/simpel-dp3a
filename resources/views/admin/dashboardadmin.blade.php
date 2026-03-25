@extends('layouts.admin')

@section('title', 'Dashboard | SIMPEL DP3A')

@section('content')

<div class="header">
    <div class="header-left">
        <div class="hamburger" id="hamburger">☰</div>
        <img src="{{ asset('images/DPPPA ver2.png') }}" alt="Logo DP3A">
    </div>

    <div class="header-right">
        <div class="user-info">
            <div class="user-icon">
                <img src="{{ asset('images/accicon.png') }}" alt="Akun">
            </div>
            <span>Perencanaan</span>
        </div>

        <!-- Logout Laravel -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn-logout">Logout</button>
        </form>
    </div>
</div>

<div class="wrapper">

    <div class="sidebar" id="sidebar">
        <ul id="menu">
            <li class="active">Dashboard</li>
            <li onclick="loadPage('permintaan')">Permintaan Dokumen</li>
            <li>Verifikasi Dokumen</li>
            <li>Dokumen Masuk</li>
            <li>Pertanyaan Evaluasi PUG</li>
        </ul>
    </div>

    <div class="content">
        <div id="main-content">
            <h2>Halo, Bidang Perencanaan!</h2>

            <div class="card-container">
                <div class="card">
                    <h4>Permintaan Anda</h4>
                    <span>12</span>
                </div>
                <div class="card">
                    <h4>Total Arsip</h4>
                    <span>58</span>
                </div>
            </div>
        </div>
    </div>

</div>
<script>
    function escapeHtml(input) {
        return String(input)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function initPermintaanPage() {
        const overlay = document.getElementById('modalOverlay');
        const openBtn = document.getElementById('openModal');
        const closeBtn = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelModal');
        const dueInput = document.getElementById('due_date');
        const form = document.getElementById('createPermintaanForm');
        const errorsBox = document.getElementById('permintaanErrors');

        if (!overlay || !openBtn || !form) {
            return;
        }

        // FRONTEND BLOCK WAKTU LAMPAU (DETIK)
        if (dueInput) {
            const now = new Date();
            now.setSeconds(now.getSeconds() + 60);
            const iso = now.toISOString().slice(0, 16);
            dueInput.min = iso;
        }

        function openModal() {
            overlay.classList.add('open');
        }

        function closeModal() {
            overlay.classList.remove('open');
            if (errorsBox) errorsBox.style.display = 'none';
        }

        openBtn.addEventListener('click', openModal);
        closeBtn?.addEventListener('click', closeModal);
        cancelBtn?.addEventListener('click', closeModal);

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                closeModal();
            }
        });

        // SPA-like: submit tanpa redirect halaman
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (errorsBox) {
                errorsBox.innerHTML = '';
                errorsBox.style.display = 'none';
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                if (res.ok) {
                    closeModal();
                    await loadPage('permintaan');
                    return;
                }

                if (res.status === 422) {
                    const data = await res.json();
                    const messages = [];
                    if (data && data.errors) {
                        Object.values(data.errors).forEach(list => {
                            if (Array.isArray(list)) messages.push(...list);
                        });
                    }

                    if (errorsBox) {
                        errorsBox.innerHTML = messages.map(m => '<div>' + escapeHtml(m) + '</div>').join('');
                        errorsBox.style.display = 'block';
                    }
                    return;
                }

                if (errorsBox) {
                    errorsBox.innerHTML = '<div>Gagal menyimpan data.</div>';
                    errorsBox.style.display = 'block';
                }
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

    function loadPage(page) {
        return fetch('/admin/content/' + page, { credentials: 'same-origin' })
            .then(res => res.text())
            .then(html => {
                document.getElementById('main-content').innerHTML = html;
                if (page === 'permintaan') {
                    initPermintaanPage();
                }
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // default: tidak memuat permintaan sampai user klik sidebar
    });
</script>
@endsection