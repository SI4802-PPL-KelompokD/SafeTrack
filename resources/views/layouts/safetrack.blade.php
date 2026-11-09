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
    </style>
    @stack('styles')
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
            <p class="nav-label">Utama</p>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6Zm0 9.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6Zm0 9.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                <span>Ringkasan</span>
            </a>
            <a href="{{ route('hazards.index') }}" class="{{ request()->routeIs('hazards.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <span>Laporan Bahaya</span>
            </a>
            <a href="{{ route('daily-inspections.index') }}" class="{{ request()->routeIs('daily-inspections.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.4 48.4 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V19.5a2.25 2.25 0 0 0 2.25 2.25h.75" />
                </svg>
                <span>Inspeksi Harian</span>
            </a>
            <a href="#" onclick="event.preventDefault()">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.2 48.2 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.4 48.4 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                </svg>
                <span>Safety Talk</span>
            </a>
            <a href="{{ route('sap.index') }}" class="{{ request()->routeIs('sap.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m15.042 21.672-1.358-5.072m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.757l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" />
                </svg>
                <span>Pencapaian SAP</span>
            </a>
            <a href="#" onclick="event.preventDefault()">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9a6 6 0 0 0-12 0v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
                <span>Notifikasi</span>
            </a>

            @if($isAdmin)
                <p class="nav-label" style="margin-top:16px;">Administrasi</p>
                <a href="{{ route('admin.pengguna.index') }}" class="{{ request()->routeIs('admin.pengguna.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    <span>Kelola Karyawan</span>
                </a>
                <a href="{{ route('admin.work-areas.index') }}" class="{{ (request()->routeIs('admin.work-areas.*') || request()->routeIs('shifts.*') || request()->routeIs('admin.departments.*')) ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125S3.75 14.278 3.75 12m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                    </svg>
                    <span>Data Master</span>
                </a>
                <a href="#" onclick="event.preventDefault()">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>Ekspor Laporan</span>
                </a>
                <a href="#" onclick="event.preventDefault()">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>Jejak Audit</span>
                </a>
            @endif
        </nav>

        <div class="sidebar-user">
            <div class="avatar">{{ $initials }}</div>
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
                <div class="avatar" style="width:30px;height:30px;font-size:11px;">{{ $initials }}</div>
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
