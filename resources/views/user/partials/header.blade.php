<div class="header">
        <div class="header-left">
            <div class="hamburger" id="hamburger">☰</div>
            <img src="{{ asset('images/DPPPA ver2.png') }}" alt="Logo">
        </div>

        <div class="header-right">
            @php
                $unreadNotifs = auth()->user()->unreadNotifications ?? collect();
                // Ambil nama bidang (atau nama user jika bidang kosong)
                $namaBidang = Auth::user()->bidang ? Auth::user()->bidang->nama : Auth::user()->name;
            @endphp

            {{-- Lonceng Notifikasi --}}
            <div class="notif-wrapper">
                <button class="btn-bell" id="notifButton">
                    <i class="fa-regular fa-bell" style="font-size: 20px;"></i>
                    @if($unreadNotifs->count() > 0)
                        <span class="notif-badge">{{ $unreadNotifs->count() }}</span>
                    @endif
                </button>

                <div class="notif-dropdown" id="notifDropdown">
                    <div class="notif-header">
                        <span>Notifikasi</span>
                        @if($unreadNotifs->count() > 0)
                            <a href="#" id="markAllRead" style="font-size: 12px; font-weight:normal;">Tandai semua dibaca</a>
                        @endif
                    </div>
                    <div class="notif-body">
                        @forelse($unreadNotifs as $notif)
                            <a href="#" class="notif-item unread"
                                data-notif-id="{{ $notif->id }}"
                                data-page="{{ $notif->data['page'] ?? 'permintaan' }}"
                                data-pengajuan-id="{{ $notif->data['pengajuan_id'] ?? '' }}"
                                onclick="handleNotifClickUser(event, this)">
                                <div class="notif-icon" style="background: {{ $notif->data['color'] }}; color: {{ $notif->data['text_color'] }};">
                                    <i class="fa-solid {{ $notif->data['icon'] }}"></i>
                                </div>
                                <div class="notif-text">
                                    <p><b>{{ $notif->data['pengirim'] ?? '' }}</b> {{ $notif->data['pesan'] }} <b>{{ $notif->data['judul'] }}</b>.</p>
                                    <span>{{ $notif->created_at->diffForHumans() }}</span>
                                </div>
                            </a>
                        @empty
                            <div style="padding: 20px; text-align: center; color: #888; font-size: 13px;">
                                <i class="fa-regular fa-bell-slash" style="font-size: 24px; margin-bottom: 8px; color: #ccc;"></i><br>
                                Belum ada notifikasi baru.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Profil User & Dropdown Logout --}}
            <div class="user-info-wrapper" style="position: relative; margin-left: 15px;">
                <div class="user-info" id="userDropdownBtn" style="cursor: pointer; padding: 5px 10px; border-radius: 6px; transition: background 0.2s; display:flex; align-items:center; gap:8px;">
                    <div class="user-icon">
                        <img src="{{ asset('images/accicon.png') }}" alt="User" style="width:26px; height:26px;">
                    </div>
                    <span style="font-weight:600; font-size:14px; color:#2c2c2c;">{{ $namaBidang }}</span>
                    <i class="fa-solid fa-chevron-down" style="font-size: 10px; color:#555;"></i>
                </div>

                <div class="profile-dropdown" id="profileDropdown" style="display: none; position: absolute; right: 0; top: 110%; background: #ffffff; box-shadow: 0 4px 15px rgba(0,0,0,0.15); border-radius: 8px; width: 190px; z-index: 1000; overflow: hidden; border: 1px solid #e0e0e0;">
                    <a href="#" id="btnOpenUbahPassword" style="display: block; padding: 12px 16px; color: #2c2c2c; text-decoration: none; border-bottom: 1px solid #f0f0f0; font-size:14px;">
                        <i class="fa-solid fa-key" style="margin-right: 8px; color: #067fb2; width: 16px;"></i> Ubah Password
                    </a>
                    
                    {{-- ✅ FIX: Memanggil confirmLogout --}}
                    <form method="POST" action="{{ route('logout') }}" id="formLogout" style="margin: 0;" onsubmit="confirmLogout(event, this)">
                        @csrf
                        <button type="submit" style="width: 100%; text-align: left; background: none; border: none; padding: 12px 16px; color: #E74A3B; cursor: pointer; font-weight: 600; font-family: inherit; font-size: 14px;">
                            <i class="fa-solid fa-right-from-bracket" style="margin-right: 8px; width: 16px;"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>