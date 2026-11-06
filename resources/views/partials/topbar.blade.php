{{--
    Partial: SafeTrack Standard Topbar Component
    Parameters:
    - $currentTitle: string (Judul halaman saat ini, misal: 'Profil Saya', 'Ringkasan')
    - $parentTitle: string (opsional, default: 'Workspace')
    - $parentUrl: string (opsional, default: route('dashboard'))
--}}

<style>
    /* Topbar Shell */
    .app-topbar {
        height: 64px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 0 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 30;
    }
    .topbar-breadcrumbs {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13.5px;
        color: #64748b;
    }
    .topbar-breadcrumbs a {
        color: #64748b;
        text-decoration: none;
        transition: color 0.15s ease;
    }
    .topbar-breadcrumbs a:hover {
        color: #0f172a;
    }
    .topbar-breadcrumbs .sep {
        color: #cbd5e1;
        font-size: 12px;
    }
    .topbar-breadcrumbs .current {
        font-weight: 600;
        color: #0f172a;
    }

    .topbar-actions {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .topbar-search-box {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 7px 12px;
        width: 220px;
        transition: all 0.15s ease;
    }
    .topbar-search-box:focus-within {
        border-color: #3b82f6;
        background: #ffffff;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }
    .topbar-search-box svg {
        width: 15px;
        height: 15px;
        color: #94a3b8;
        flex-shrink: 0;
    }
    .topbar-search-box input {
        border: none;
        background: transparent;
        font-size: 12.5px;
        color: #334155;
        width: 100%;
        outline: none;
    }
    .topbar-search-box input::placeholder {
        color: #94a3b8;
    }

    /* Ganti Akun Dropdown Container */
    .account-switch-wrapper {
        position: relative;
        display: inline-block;
    }
    .account-switch-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 12.5px;
        color: #0f172a;
        cursor: pointer;
        font-family: inherit;
        transition: all 0.15s ease;
    }
    .account-switch-btn:hover,
    .account-switch-btn[aria-expanded="true"] {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .account-switch-label {
        font-size: 10px;
        font-weight: 700;
        color: #94a3b8;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }
    .account-switch-name {
        font-weight: 600;
        color: #0f172a;
    }
    .account-switch-chevron {
        width: 12px;
        height: 12px;
        color: #64748b;
        transition: transform 0.15s ease;
    }
    .account-switch-btn[aria-expanded="true"] .account-switch-chevron {
        transform: rotate(180deg);
    }

    /* Dropdown Popover */
    .account-dropdown-menu {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        width: 330px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.08);
        z-index: 100;
        overflow: hidden;
        animation: fadeInDropdown 0.15s ease-out;
    }
    .account-dropdown-menu.show {
        display: block;
    }
    @keyframes fadeInDropdown {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .account-dropdown-header {
        padding: 11px 16px 8px;
        font-size: 10.5px;
        font-weight: 700;
        color: #94a3b8;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        border-bottom: 1px solid #f1f5f9;
        background: #fafbfc;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .account-dropdown-list {
        padding: 6px;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .account-dropdown-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 9px 12px;
        border-radius: 7px;
        width: 100%;
        text-align: left;
        background: transparent;
        border: none;
        cursor: pointer;
        font-family: inherit;
        text-decoration: none;
        color: inherit;
        transition: all 0.15s ease;
    }
    .account-dropdown-item:hover:not(.active) {
        background: #f8fafc;
    }
    .account-dropdown-item.active {
        background: #f0fdf4;
        cursor: default;
    }
    .item-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #dbeafe;
        color: #1e40af;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        flex-shrink: 0;
    }
    .account-dropdown-item.active .item-avatar {
        background: #dcfce7;
        color: #166534;
    }
    .item-info {
        flex: 1;
        min-width: 0;
    }
    .item-name {
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .item-role {
        font-size: 11.5px;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .item-badge-active {
        font-size: 10.5px;
        font-weight: 700;
        color: #15803d;
        background: #dcfce7;
        padding: 3px 8px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .item-action-switch {
        font-size: 11.5px;
        font-weight: 600;
        color: #0f2a4a;
        padding: 4px 9px;
        border-radius: 5px;
        background: #f1f5f9;
        white-space: nowrap;
        transition: background 0.15s ease, color 0.15s ease;
    }
    .account-dropdown-item:hover .item-action-switch {
        background: #0f2a4a;
        color: #ffffff;
    }

    /* Notifikasi Button */
    .topbar-icon-btn {
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
        padding: 6px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        transition: color 0.15s ease, background-color 0.15s ease;
    }
    .topbar-icon-btn:hover {
        color: #0f172a;
        background-color: #f1f5f9;
    }
    .topbar-icon-btn svg { width: 19px; height: 19px; }

    /* Topbar Profile User Link (Interactive Greeting & Avatar) */
    .topbar-profile-link {
        display: inline-flex;
        align-items: center;
        gap: 11px;
        padding: 6px 10px;
        border-radius: 8px;
        text-decoration: none;
        color: #334155;
        transition: background-color 0.15s ease, box-shadow 0.15s ease;
        cursor: pointer;
        user-select: none;
    }
    .topbar-profile-link:hover {
        background-color: #f1f5f9;
    }
    .topbar-profile-link:hover .topbar-greeting strong {
        color: #0f2a4a;
    }
    .topbar-greeting {
        font-size: 12.5px;
        color: #64748b;
        white-space: nowrap;
        display: inline-block;
    }
    .topbar-greeting strong {
        color: #0f172a;
        font-weight: 700;
        transition: color 0.15s ease;
    }
    .topbar-avatar-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #dbeafe;
        color: #1e40af;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        flex-shrink: 0;
        overflow: hidden;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .topbar-profile-link:hover .topbar-avatar-badge {
        transform: scale(1.05);
        box-shadow: 0 0 0 2px #3b82f6;
    }
    .topbar-avatar-badge img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    @media (max-width: 900px) {
        .app-topbar { padding: 0 16px; }
        .topbar-search-box { display: none; }
        .topbar-greeting { display: none; }
        .account-switch-name { max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    }
</style>

<header class="app-topbar">
    <!-- Breadcrumbs -->
    <nav class="topbar-breadcrumbs" aria-label="Breadcrumb">
        <a href="{{ $parentUrl ?? route('dashboard') }}">{{ $parentTitle ?? 'Workspace' }}</a>
        <span class="sep">&gt;</span>
        <span class="current">{{ $currentTitle ?? 'Profil Saya' }}</span>
    </nav>

    <!-- Actions & User Profile Area -->
    <div class="topbar-actions">
        <!-- Search Box -->
        <div class="topbar-search-box">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input type="text" placeholder="Cari laporan bahaya..." aria-label="Cari laporan">
        </div>

        <!-- Dropdown Ganti Akun -->
        <div class="account-switch-wrapper" id="accountSwitchWrapper">
            <button
                type="button"
                class="account-switch-btn"
                id="btnToggleAccountDropdown"
                aria-haspopup="true"
                aria-expanded="false"
                title="Buka menu ganti akun demo"
            >
                <span class="account-switch-label">Ganti Akun</span>
                <span class="account-switch-name">{{ Auth::user()->name }} · {{ Auth::user()->role_label }}</span>
                <svg class="account-switch-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <!-- Dropdown Menu List (3 Akun Demo Resmi Sesuai DemoSeeder) -->
            <div class="account-dropdown-menu" id="accountDropdownMenu" role="menu">
                <div class="account-dropdown-header">
                    <span>PILIH AKUN DEMO</span>
                    <span style="font-size:9.5px;color:#16a34a;font-weight:600;">Lokal / Demo</span>
                </div>
                <div class="account-dropdown-list">
                    @php
                        $demoAccounts = [
                            [
                                'username' => 'admin',
                                'name' => 'Admin Utama',
                                'role' => 'Super Admin',
                                'dept' => 'Dept HSE',
                                'initials' => 'AU',
                            ],
                            [
                                'username' => 'hse',
                                'name' => 'Petugas HSE',
                                'role' => 'Karyawan HSE',
                                'dept' => 'Dept HSE',
                                'initials' => 'PH',
                            ],
                            [
                                'username' => 'karyawan',
                                'name' => 'Karyawan Demo',
                                'role' => 'Karyawan',
                                'dept' => 'Dept Operasional',
                                'initials' => 'KD',
                            ],
                        ];
                    @endphp

                    @foreach ($demoAccounts as $acc)
                        @php $isActive = (Auth::user()->username === $acc['username']); @endphp
                        @if ($isActive)
                            <div class="account-dropdown-item active" role="menuitem">
                                <div class="item-avatar">{{ $acc['initials'] }}</div>
                                <div class="item-info">
                                    <div class="item-name">{{ $acc['name'] }}</div>
                                    <div class="item-role">{{ $acc['role'] }} · {{ $acc['dept'] }}</div>
                                </div>
                                <span class="item-badge-active">Aktif</span>
                            </div>
                        @else
                            <form method="POST" action="{{ route('demo.switch-account') }}" style="margin:0;">
                                @csrf
                                <input type="hidden" name="username" value="{{ $acc['username'] }}">
                                <button type="submit" class="account-dropdown-item" role="menuitem" title="Beralih ke akun {{ $acc['name'] }}">
                                    <div class="item-avatar">{{ $acc['initials'] }}</div>
                                    <div class="item-info">
                                        <div class="item-name">{{ $acc['name'] }}</div>
                                        <div class="item-role">{{ $acc['role'] }} · {{ $acc['dept'] }}</div>
                                    </div>
                                    <span class="item-action-switch">Ganti &rarr;</span>
                                </button>
                            </form>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Notifikasi Icon -->
        <button class="topbar-icon-btn" aria-label="Notifikasi">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
        </button>

        <!-- Area Profil Pengguna (Sapaan + Avatar) Menuju Profil Saya -->
        <a href="{{ route('profile.edit') }}" class="topbar-profile-link" title="Buka Profil Saya ({{ Auth::user()->name }})">
            <span class="topbar-greeting">Halo, <strong>{{ Auth::user()->name }}</strong></span>
            <div class="user-avatar-badge topbar-avatar-badge">
                @if (Auth::user()->profile_photo_url)
                    <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
                @else
                    <span>{{ Auth::user()->initials }}</span>
                @endif
            </div>
        </a>
    </div>
</header>

<script>
    (function () {
        function initAccountDropdown() {
            var btn = document.getElementById('btnToggleAccountDropdown');
            var menu = document.getElementById('accountDropdownMenu');
            var wrapper = document.getElementById('accountSwitchWrapper');
            if (!btn || !menu) return;

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var isOpen = menu.classList.contains('show');
                if (isOpen) {
                    menu.classList.remove('show');
                    btn.setAttribute('aria-expanded', 'false');
                } else {
                    menu.classList.add('show');
                    btn.setAttribute('aria-expanded', 'true');
                }
            });

            document.addEventListener('click', function (e) {
                if (wrapper && !wrapper.contains(e.target)) {
                    menu.classList.remove('show');
                    btn.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' || e.key === 'Esc') {
                    menu.classList.remove('show');
                    btn.setAttribute('aria-expanded', 'false');
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAccountDropdown);
        } else {
            initAccountDropdown();
        }
    })();
</script>

