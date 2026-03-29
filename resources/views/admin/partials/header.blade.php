<div class="header">
    <div class="header-left">
        <div class="hamburger" id="hamburger">☰</div>
        <img src="{{ asset('images/DPPPA ver2.png') }}" alt="Logo DP3A">
    </div>
    
    <div class="header-right">
        @php $unreadNotifs = auth()->user()->unreadNotifications ?? collect(); @endphp

        {{-- WIDGET NOTIFIKASI --}}
        <div class="notif-wrapper">
            <button class="btn-bell" id="notifButton">
                <i class="fa-regular fa-bell"></i>
                @if($unreadNotifs->count() > 0)
                    <span class="notif-badge">{{ $unreadNotifs->count() }}</span>
                @endif
            </button>
            
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header">
                    <span>Notifikasi</span>
                    @if($unreadNotifs->count() > 0)
                        <a href="#" id="markAllRead" class="mark-all-read-btn">
                            Tandai semua dibaca
                        </a>
                    @endif
                </div>
                
                <div class="notif-body">
                    @forelse($unreadNotifs as $notif)
                        <a href="#" class="notif-item unread"
                            data-notif-id="{{ $notif->id }}"
                            data-pengajuan-id="{{ $notif->data['pengajuan_id'] ?? '' }}"
                            onclick="handleNotifClick(event, this)">
                            
                            {{-- Warna dinamis dari database dibiarkan inline agar aman --}}
                            <div class="notif-icon" style="background:{{ $notif->data['color'] }}; color:{{ $notif->data['text_color'] }};">
                                <i class="fa-solid {{ $notif->data['icon'] }}"></i>
                            </div>
                            
                            <div class="notif-text">
                                <p>
                                    <b>{{ $notif->data['pengirim'] ?? '' }}</b>
                                    {{ $notif->data['pesan'] }}
                                    <b>{{ $notif->data['judul'] }}</b>.
                                </p>
                                <span>{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="notif-empty-state">
                            <i class="fa-regular fa-bell-slash notif-empty-icon"></i><br>
                            Belum ada notifikasi baru.
                        </div>
                    @endforelse
                </div>
                
                <div class="notif-footer">
                    <a href="#" onclick="handleLoadVerifikasi(event)">Lihat Halaman Verifikasi</a>
                </div>
            </div>
        </div>

        {{-- WIDGET PROFIL USER --}}
        <div class="user-profile-wrapper">
            <div class="user-profile-btn" id="userDropdownBtn">
                <div class="user-icon">
                    <img src="{{ asset('images/accicon.png') }}" alt="Akun">
                </div>
                <span class="user-profile-name">
                    {{ Auth::user()->name ?? 'Admin' }}
                </span>
                <i class="fa-solid fa-chevron-down user-profile-arrow"></i>
            </div>
            
            <div class="profile-dropdown-menu" id="profileDropdown" style="display:none;">
                <a href="#" id="btnOpenUbahPassword" class="profile-dropdown-item">
                    <i class="fa-solid fa-key" style="color:#067fb2;"></i>
                    Ubah Password
                </a>
                
                <form method="POST" action="{{ route('logout') }}" id="formLogout" class="form-logout">
                    @csrf
                    <button type="button" onclick="confirmLogout(event, this.form)" class="profile-dropdown-item text-red">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>
        
    </div>
</div>