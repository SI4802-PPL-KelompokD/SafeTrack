<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ringkasan Workspace – SafeTrack</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        :root {
            --font-main: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --bg-page: #f8fafc;
            --bg-surface: #ffffff;
            --border-light: #e2e8f0;
            --navy-dark: #0f2a4a;
            --navy-btn: #1e293b;
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --sidebar-width: 260px;
            --radius-md: 10px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font-main);
            background: var(--bg-page);
            color: var(--text-body);
            min-height: 100vh;
            display: flex;
        }
        .app-shell { display: flex; width: 100%; min-height: 100vh; }
        .app-sidebar {
            width: var(--sidebar-width);
            background: #ffffff;
            border-right: 1px solid var(--border-light);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; bottom: 0; left: 0;
            z-index: 40;
        }
        .brand-container {
            padding: 20px 20px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-logo-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #0f2a4a 0%, #1e3a5f 100%);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            color: #f59e0b;
        }
        .brand-logo-icon svg { width: 20px; height: 20px; }
        .brand-title { font-size: 18px; font-weight: 800; color: #0f2a4a; }
        .brand-title span { color: #d97706; }
        .brand-subtitle { font-size: 9.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }

        .workspace-card {
            margin: 0 16px 16px;
            padding: 10px 12px;
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            display: flex; align-items: center; gap: 10px;
        }
        .workspace-name { font-size: 12.5px; font-weight: 700; color: var(--text-heading); }
        .workspace-desc { font-size: 11px; color: #94a3b8; }

        .nav-title { padding: 12px 20px 6px; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; }
        .menu-list { list-style: none; padding: 0 12px; }
        .menu-link {
            display: flex; align-items: center; gap: 11px;
            padding: 9px 12px; font-size: 13.5px; font-weight: 500; color: #475569;
            text-decoration: none; border-radius: 6px;
        }
        .menu-link:hover { background: #f1f5f9; color: var(--text-heading); }
        .menu-link.active { background: #0f2a4a; color: #ffffff; font-weight: 600; }
        .menu-link.active svg { color: #ffffff; }
        .menu-link svg { width: 17px; height: 17px; color: #64748b; }

        .sidebar-footer {
            margin-top: auto; padding: 14px 16px; border-top: 1px solid var(--border-light);
            display: flex; align-items: center; justify-content: space-between;
        }
        .user-avatar-badge {
            width: 34px; height: 34px; border-radius: 50%;
            background: #fef3c7; color: #92400e; display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700;
            text-decoration: none;
            overflow: hidden;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .user-avatar-badge:hover {
            transform: scale(1.06);
            box-shadow: 0 0 0 2px #f59e0b;
        }
        .btn-exit-form button {
            background: none; border: none; color: #94a3b8; cursor: pointer; padding: 4px;
            display: flex; align-items: center; justify-content: center;
        }
        .btn-exit-form button:hover { color: #ef4444; }

        .app-main { flex: 1; margin-left: var(--sidebar-width); display: flex; flex-direction: column; }
        .app-topbar {
            height: 64px; background: #ffffff; border-bottom: 1px solid var(--border-light);
            padding: 0 32px; display: flex; align-items: center; justify-content: space-between;
        }
        .content-area { padding: 32px 36px; width: 100%; }

        .welcome-hero-card {
            background: linear-gradient(135deg, #0f2a4a 0%, #1e3a5f 100%);
            border-radius: 12px; padding: 28px 32px; color: #ffffff; margin-bottom: 28px;
            box-shadow: 0 4px 6px -1px rgba(15, 42, 74, 0.1);
        }
        .welcome-hero-card h1 { font-size: 24px; font-weight: 800; margin-bottom: 6px; }
        .welcome-hero-card p { font-size: 14px; color: #cbd5e1; }

        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 28px; }
        .card-stat { background: #ffffff; border: 1px solid var(--border-light); border-radius: 10px; padding: 20px; }
        .card-stat-label { font-size: 13px; color: #64748b; margin-bottom: 6px; }
        .card-stat-val { font-size: 28px; font-weight: 800; color: #0f172a; }

        .alert-box {
            padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13.5px;
        }
        .alert-box.success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-box.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
    @include('partials.favicon')
</head>
<body>
<div class="app-shell">
    <aside class="app-sidebar">
        <div class="brand-container">
            <div class="brand-logo-icon">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L4 5V11.5C4 16.5 7.4 21.1 12 22.3C16.6 21.1 20 16.5 20 11.5V5L12 2Z" fill="currentColor" fill-opacity="0.2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 12L11 14L15 9.5" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div>
                <div class="brand-title">safe<span>Track</span></div>
                <div class="brand-subtitle">SAFETY MANAGEMENT</div>
            </div>
        </div>

        <div class="workspace-card">
            <div>
                <div class="workspace-name">PT Sentra Industri</div>
                <div class="workspace-desc">Workspace demonstrasi</div>
            </div>
        </div>

        @include('partials.sidebar-menu', ['linkClass' => 'menu-link', 'labelClass' => 'nav-title', 'listClass' => 'menu-list'])

        <div class="sidebar-footer">
            <div style="display:flex;align-items:center;gap:10px;">
                <a href="{{ route('profile.edit') }}" title="Kelola Profil" class="user-avatar-badge">
                    @if(Auth::user()?->profile_photo_url)
                        <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                    @endif
                </a>
                <div>
                    <div style="font-size:13px;font-weight:700;color:#0f172a;">{{ Auth::user()->name ?? 'Pengguna' }}</div>
                    <div style="font-size:11px;color:#64748b;">
                        {{ Auth::user()->role === 'super_admin' ? 'Super Admin' : (Auth::user()->role === 'admin' ? 'Admin / HSE' : 'Karyawan') }}
                    </div>
                </div>
            </div>
            <!-- Logout Button (UC-03) -->
            <form method="POST" action="{{ route('logout') }}" class="btn-exit-form">
                @csrf
                <button type="submit" title="Keluar / Logout" id="btnLogoutDashboard">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:18px;height:18px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <main class="app-main">
        <header class="app-topbar">
            <div style="font-size:13.5px;color:#64748b;">
                Workspace &gt; <strong style="color:#0f172a;">Ringkasan</strong>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
                <span style="font-size:12.5px;color:#64748b;">Halo, <strong>{{ Auth::user()->name ?? 'Pengguna' }}</strong></span>
                <a href="{{ route('profile.edit') }}" title="Kelola Profil" class="user-avatar-badge" style="width:30px;height:30px;font-size:11px;">
                    @if(Auth::user()?->profile_photo_url)
                        <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                    @endif
                </a>
            </div>
        </header>

        <div class="content-area">
            @if (session('success'))
                <div class="alert-box success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert-box error">{{ session('error') }}</div>
            @endif

            <div class="welcome-hero-card">
                <h1>Selamat Datang di SafeTrack, {{ Auth::user()->name }}!</h1>
                <p>Peran Anda saat ini: <strong>{{ Auth::user()->role === 'super_admin' ? 'Super Admin' : (Auth::user()->role === 'admin' ? 'Admin / HSE' : 'Karyawan Operasional') }}</strong>. Bersama mewujudkan tempat kerja yang aman dan berbudaya K3.</p>
            </div>

            <div class="stats-row">
                <div class="card-stat">
                    <div class="card-stat-label">Laporan Bahaya Saya</div>
                    <div class="card-stat-val">{{ $sapSummary?->hazard_count ?? 0 }}</div>
                </div>
                <div class="card-stat">
                    <div class="card-stat-label">Inspeksi Bulan Ini</div>
                    <div class="card-stat-val">{{ $sapSummary?->inspection_count ?? 0 }}</div>
                </div>
                <div class="card-stat">
                    <div class="card-stat-label">Poin SAP K3</div>
                    <div class="card-stat-val">{{ $sapSummary?->total_points ?? 0 }}</div>
                </div>
                <div class="card-stat">
                    <div class="card-stat-label">
                        @if (Auth::user()->department?->is_hse)
                            Target K3 (HSE)
                        @else
                            Hak Reward K3
                        @endif
                    </div>
                    <div class="card-stat-val" style="font-size:18px;margin-top:6px;">
                        @if (Auth::user()->department?->is_hse)
                            @if ($sapSummary?->target_met === true)
                                <span style="color:#15803d;font-weight:700;">&#10003; Terpenuhi</span>
                            @elseif ($sapSummary?->target_met === false)
                                <span style="color:#b91c1c;font-weight:700;">Belum Terpenuhi</span>
                            @else
                                <span style="color:#64748b;font-size:14px;font-weight:600;">Belum Ada Aturan</span>
                            @endif
                        @else
                            @if ($sapSummary?->reward_eligible && $sapSummary?->eligibleReward)
                                <span style="color:#b45309;font-weight:700;">🎁 {{ $sapSummary->eligibleReward->name }}</span>
                            @else
                                <span style="color:#64748b;font-size:14px;font-weight:600;">Belum Capai Ambang</span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            @if (Auth::user()->can('submit-inspections'))
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
                    <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:8px;">Akses Akun Karyawan</h3>
                    <p style="font-size:13.5px;color:#64748b;line-height:1.6;">
                        Sebagai karyawan, Anda dapat membuat laporan temuan bahaya K3, mengikuti inspeksi harian di area kerja, dan membaca materi Safety Talk. Fitur administrasi seperti Kelola Shift Kerja dan Data Master dibatasi untuk peran Admin/HSE.
                    </p>
                </div>
            @else
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
                    <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:8px;">Akses Cepat Admin/HSE</h3>
                    <p style="font-size:13.5px;color:#64748b;line-height:1.6;margin-bottom:16px;">
                        Anda memiliki akses penuh untuk mengelola master data shift kerja, verifikasi temuan bahaya, dan audit K3.
                    </p>
                    <a href="{{ route('shifts.index') }}" style="display:inline-block;background:#0f2a4a;color:#ffffff;padding:9px 18px;border-radius:6px;text-decoration:none;font-size:13.5px;font-weight:600;">
                        Buka Kelola Shift Kerja &rarr;
                    </a>
                </div>
            @endif
        </div>
    </main>
</div>
</body>
</html>