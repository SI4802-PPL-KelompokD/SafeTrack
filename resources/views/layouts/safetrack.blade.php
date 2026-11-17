@php
    $user = Auth::user();
    $userName = $user->name ?? 'Pengguna';
    $userRole = $user->role ?? 'employee';
    $isAdmin = (bool) ($user && $user->can('manage-administration'));
    $roleLabel = match($userRole) {
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'hse' => 'HSE',
        default => 'Karyawan',
    };
    $nameParts = explode(' ', trim($userName));
    $initials = strtoupper(substr($nameParts[0] ?? 'U', 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : substr($nameParts[0] ?? '', 1, 1)));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SafeTrack' }} — SafeTrack</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/inspection.css') }}">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        .master-tabs-bar {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .master-tab-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 13.5px;
            font-weight: 500;
            color: #475569;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .master-tab-item:hover {
            background: #f8fafc;
            color: #0f172a;
        }
        .master-tab-item.active {
            background: #0f2a4a;
            border-color: #0f2a4a;
            color: #ffffff;
            font-weight: 600;
        }
        .master-tab-item.active .tab-badge {
            color: #94a3b8;
        }
        .master-tab-item .tab-badge {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
        }
        .sidebar nav a.active {
            background-color: #fffbeb !important;
            color: #92400e !important;
            font-weight: 700 !important;
            border-left: 3.5px solid #f59e0b !important;
            border-radius: 0 7px 7px 0 !important;
        }
        .sidebar nav a.active svg {
            color: #d97706 !important;
        }
        .profile-avatar-link {
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .profile-avatar-link:hover {
            transform: scale(1.06);
            box-shadow: 0 0 0 2px #f59e0b;
        }
    </style>
    @stack('styles')
    @include('partials.favicon')
</head>
<body>
<div class="app-shell">
    <!-- Sidebar -->
    <aside class="sidebar" role="navigation" aria-label="Navigasi SafeTrack">
        <div class="brand">
            <div class="brand-mark">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none">
                    <path d="M12 2 4 5v6.5c0 5 3.4 9.6 8 10.8 4.6-1.2 8-5.8 8-10.8V5l-8-3Z" fill="currentColor" fill-opacity=".2" stroke="currentColor" stroke-width="2"/>
                    <path d="m9 12 2 2 4-4.5" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
            </div>
            <div>
                <strong>safe<span>Track</span></strong>
                <small>SAFETY MANAGEMENT</small>
            </div>
        </div>

        <div class="workspace">
            <strong>PT Sentra Industri</strong>
            <small>Workspace demonstrasi</small>
        </div>

        <nav>
            @include('partials.sidebar-menu', ['linkClass' => '', 'labelClass' => 'nav-label', 'listClass' => ''])
        </nav>

        <div class="sidebar-user">
            <a href="{{ route('profile.edit') }}" title="Kelola Profil" class="avatar profile-avatar-link" style="overflow:hidden;">
                @if($user?->profile_photo_url)
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $userName }}" style="width:100%;height:100%;object-fit:cover;">
                @else
                    {{ $initials }}
                @endif
            </a>
            <div>
                <strong>{{ $userName }}</strong>
                <small>{{ $roleLabel }}</small>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin-left:auto;">
                @csrf
                <button type="submit" title="Keluar / Logout">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H3" />
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Shell -->
    <main class="main-shell">
        <header class="topbar">
            <div>
                <a href="{{ route('dashboard') }}" style="color:inherit;text-decoration:none;">Workspace</a>
                <span style="margin: 0 8px; color:#cbd5e1;">&rsaquo;</span>
                <strong style="color:#0f172a;">{{ $pageTitle ?? 'Data Master' }}</strong>
            </div>
            <div class="user-inline">
                <div class="filter-search" style="height:36px;width:240px;">
                    <span style="font-size:16px;">🔍</span>
                    <input type="text" placeholder="Cari laporan bahaya..." aria-label="Cari laporan" style="font-size:12px;">
                </div>
                <div style="font-size:12px;font-weight:600;padding:5px 10px;background:#f1f5f9;border-radius:6px;color:#334155;">
                    {{ $roleLabel }}: {{ $userName }}
                </div>
                <a href="{{ route('profile.edit') }}" title="Kelola Profil" class="avatar profile-avatar-link" style="width:30px;height:30px;font-size:11px;overflow:hidden;">
                    @if($user?->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $userName }}" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        {{ $initials }}
                    @endif
                </a>
            </div>
        </header>

        <div class="content">
            <!-- Flash Notification Messages -->
            @if(session('success'))
                <div class="alert success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert error" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert error" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

@stack('modals')
@stack('scripts')
</body>
</html>