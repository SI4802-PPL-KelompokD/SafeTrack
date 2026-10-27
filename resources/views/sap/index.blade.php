<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pencapaian SAP - SafeTrack</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        :root {
            --primary: #0f2a4a;
            --primary-hover: #1e3a5f;
            --accent: #f59e0b;
            --accent-dark: #d97706;
            --bg-body: #f8fafc;
            --bg-sidebar: #ffffff;
            --border-light: #e2e8f0;
            --border-dark: #cbd5e1;
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --success: #10b981;
            --success-bg: #ecfdf5;
            --success-border: #a7f3d0;
            --danger: #ef4444;
            --danger-bg: #fef2f2;
            --danger-border: #fecaca;
            --info: #0284c7;
            --info-bg: #f0f9ff;
            --sidebar-width: 260px;
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-body);
            -webkit-font-smoothing: antialiased;
        }

        .app-layout {
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .app-sidebar {
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-light);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 40;
            overflow-y: auto;
        }

        .brand-container {
            padding: 20px 20px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #0f2a4a 0%, #1e3a5f 100%);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 2px 4px rgba(15, 42, 74, 0.2);
        }
        .brand-logo-icon svg {
            width: 20px;
            height: 20px;
            color: #f59e0b;
        }
        .brand-text {
            display: flex;
            flex-direction: column;
        }
        .brand-title {
            font-size: 18px;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.02em;
            color: #0f2a4a;
        }
        .brand-title span {
            color: #d97706;
        }
        .brand-subtitle {
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-top: 2px;
        }

        .workspace-card {
            margin: 0 16px 16px;
            padding: 10px 12px;
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        }
        .workspace-icon {
            width: 32px;
            height: 32px;
            background: #f1f5f9;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            flex-shrink: 0;
        }
        .workspace-icon svg { width: 17px; height: 17px; }
        .workspace-info {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .workspace-name {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--text-heading);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .workspace-desc {
            font-size: 11px;
            color: var(--text-light);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .nav-section-title {
            padding: 12px 20px 6px;
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0 12px;
            margin-bottom: 8px;
        }
        .sidebar-menu li {
            margin-bottom: 2px;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 9px 12px;
            font-size: 13.5px;
            font-weight: 500;
            color: #475569;
            text-decoration: none;
            border-radius: var(--radius-sm);
            transition: all 0.15s ease;
        }
        .sidebar-link svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
            color: #64748b;
            transition: color 0.15s ease;
        }
        .sidebar-link:hover {
            background-color: #f1f5f9;
            color: var(--text-heading);
        }
        .sidebar-link:hover svg {
            color: var(--text-heading);
        }

        /* Active Sidebar Link (Navy Blue styling identical to user specification) */
        .sidebar-link.active {
            background-color: #0f2a4a;
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(15, 42, 74, 0.2);
        }
        .sidebar-link.active svg {
            color: #f59e0b;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 16px 20px;
            border-top: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
        }
        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #0f2a4a;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            border: 2px solid #e2e8f0;
        }
        .btn-exit-form button {
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
        }
        .btn-exit-form button:hover {
            color: #ef4444;
            background: #fef2f2;
        }

        /* ── Main Area ── */
        .app-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .app-topbar {
            height: 60px;
            background: #ffffff;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 30;
        }
        .breadcrumb-item {
            font-size: 13.5px;
            color: var(--text-muted);
            text-decoration: none;
        }
        .breadcrumb-item.active {
            color: var(--text-heading);
            font-weight: 600;
        }
        .breadcrumb-sep {
            margin: 0 8px;
            color: var(--text-light);
        }

        .main-content {
            padding: 28px;
            width: 100%;
        }

        /* ── Alerts ── */
        .alert-box {
            padding: 14px 18px;
            border-radius: var(--radius-md);
            font-size: 13.5px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: fadeIn 0.3s ease;
        }
        .alert-box.success {
            background-color: var(--success-bg);
            border: 1px solid var(--success-border);
            color: #065f46;
        }
        .alert-box.error {
            background-color: var(--danger-bg);
            border: 1px solid var(--danger-border);
            color: #991b1b;
        }

        /* ── Page Header ── */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 24px;
            gap: 20px;
            flex-wrap: wrap;
        }
        .page-tag {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #0f2a4a;
            background: #e0f2fe;
            padding: 4px 8px;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        .page-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-heading);
            letter-spacing: -0.02em;
            line-height: 1.2;
        }
        .page-desc {
            font-size: 13.5px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        /* ── Recalculate CTA & Filter Bar ── */
        .filter-action-card {
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-lg);
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            box-shadow: var(--shadow-sm);
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .filter-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-body);
        }
        .filter-select {
            padding: 8px 12px;
            border: 1px solid var(--border-dark);
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            color: var(--text-heading);
            background-color: #ffffff;
            outline: none;
            transition: border-color 0.15s ease;
        }
        .filter-select:focus {
            border-color: var(--primary);
        }
        .filter-search-input {
            padding: 8px 14px;
            border: 1px solid var(--border-dark);
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            width: 220px;
            outline: none;
            transition: border-color 0.15s ease;
        }
        .filter-search-input:focus {
            border-color: var(--primary);
        }

        .btn-filter {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid var(--border-light);
            padding: 8px 14px;
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-filter:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        /* Recalculate Button (UC-28) */
        .btn-recalculate {
            background: linear-gradient(135deg, #0f2a4a 0%, #1e3a5f 100%);
            color: #ffffff;
            border: none;
            padding: 9px 18px;
            border-radius: var(--radius-md);
            font-size: 13.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(15, 42, 74, 0.2);
            transition: all 0.2s ease;
        }
        .btn-recalculate:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(15, 42, 74, 0.3);
            background: linear-gradient(135deg, #1e3a5f 0%, #2a4d7d 100%);
        }
        .btn-recalculate:active {
            transform: translateY(0);
        }

        /* ── Stats Grid ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-lg);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .stat-icon.points { background: #fef3c7; color: #b45309; }
        .stat-icon.users { background: #e0f2fe; color: #0369a1; }
        .stat-icon.avg { background: #ede9fe; color: #6d28d9; }
        .stat-icon.targets { background: #dcfce7; color: #15803d; }
        .stat-icon svg { width: 24px; height: 24px; }
        .stat-info { display: flex; flex-direction: column; }
        .stat-label { font-size: 12.5px; font-weight: 600; color: var(--text-muted); }
        .stat-value { font-size: 24px; font-weight: 800; color: var(--text-heading); line-height: 1.2; margin-top: 2px; }

        /* ── Table Container ── */
        .table-card {
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }
        .table-header-bar {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .table-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-heading);
        }
        .table-subtitle {
            font-size: 12.5px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
            text-align: left;
        }
        .data-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 18px;
            border-bottom: 1px solid var(--border-light);
            white-space: nowrap;
        }
        .data-table td {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border-light);
            color: var(--text-body);
            vertical-align: middle;
        }
        .data-table tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Badges */
        .user-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-circle {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: #0f2a4a;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .user-name-box {
            display: flex;
            flex-direction: column;
        }
        .user-full-name {
            font-weight: 700;
            color: var(--text-heading);
        }
        .user-nik {
            font-size: 11.5px;
            color: var(--text-muted);
            font-family: monospace;
        }

        .badge-points {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #fef3c7;
            color: #92400e;
            font-weight: 800;
            font-size: 13px;
            padding: 4px 10px;
            border-radius: 20px;
            border: 1px solid #fde68a;
        }
        .badge-points svg { width: 14px; height: 14px; color: #d97706; }

        .badge-target {
            display: inline-block;
            font-size: 11.5px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            text-align: center;
        }
        .badge-target.met { background: #dcfce7; color: #166534; }
        .badge-target.unmet { background: #fee2e2; color: #991b1b; }
        .badge-target.none { background: #f1f5f9; color: #64748b; }

        .badge-reward {
            display: inline-block;
            font-size: 11.5px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            text-align: center;
            white-space: nowrap;
        }
        .badge-reward.eligible { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-reward.not-eligible { background: #f1f5f9; color: #64748b; }

        .badge-dept {
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            background: #f1f5f9;
            padding: 3px 8px;
            border-radius: 4px;
        }

        .empty-state {
            padding: 48px 24px;
            text-align: center;
        }
        .empty-state svg {
            width: 48px;
            height: 48px;
            color: #cbd5e1;
            margin-bottom: 12px;
        }
        .empty-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-heading);
            margin-bottom: 6px;
        }
        .empty-desc {
            font-size: 13.5px;
            color: var(--text-muted);
            max-width: 420px;
            margin: 0 auto 16px;
        }

        .pagination-container {
            padding: 16px 20px;
            border-top: 1px solid var(--border-light);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="app-layout">
    <!-- Sidebar -->
    <aside class="app-sidebar">
        <div class="brand-container">
            <div class="brand-logo-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
            </div>
            <div class="brand-text">
                <div class="brand-title">safe<span>Track</span></div>
                <div class="brand-subtitle">Safety Management</div>
            </div>
        </div>

        <div class="workspace-card">
            <div class="workspace-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
            </div>
            <div class="workspace-info">
                <div class="workspace-name">SafeTrack Enterprise</div>
                <div class="workspace-desc">K3 Operation Workspace</div>
            </div>
        </div>

        <!-- MAIN MENU -->
        <div class="nav-section-title">Menu Utama</div>
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('dashboard') }}" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span>Ringkasan</span>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <span>Laporan Bahaya</span>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Inspeksi Harian</span>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                    </svg>
                    <span>Safety Talk</span>
                </a>
            </li>
            <li>
                <!-- Active Pencapaian SAP (Navy Blue) -->
                <a href="{{ route('sap.index') }}" class="sidebar-link active">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zM12 2.25V4.5m5.834.166l-1.591 1.591M20.25 10.5H18M7.757 14.757l-1.59 1.59M6 10.5H3.75m4.007-4.243l-1.59-1.59" />
                    </svg>
                    <span>Pencapaian SAP</span>
                </a>
            </li>
        </ul>

        <!-- ADMINISTRASI -->
        @if (Auth::user() && in_array(Auth::user()->role, ['super_admin', 'admin'], true))
            <div class="nav-section-title">Administrasi</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('shifts.index') }}" class="sidebar-link">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Kelola Shift Kerja</span>
                    </a>
                </li>
            </ul>
        @endif

        <div class="sidebar-footer">
            <div style="display:flex;align-items:center;gap:10px;">
                <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}</div>
                <div>
                    <div style="font-size:13px;font-weight:700;color:var(--text-heading);">{{ Auth::user()->name ?? 'Pengguna' }}</div>
                    <div style="font-size:11px;color:var(--text-muted);">
                        {{ Auth::user()->role === 'super_admin' ? 'Super Admin' : (Auth::user()->role === 'admin' ? 'Admin / HSE' : 'Karyawan') }}
                    </div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="btn-exit-form">
                @csrf
                <button type="submit" title="Keluar / Logout" id="btnLogoutSap">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:18px;height:18px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="app-main">
        <!-- Topbar -->
        <header class="app-topbar">
            <div>
                <a href="{{ route('dashboard') }}" class="breadcrumb-item">Workspace</a>
                <span class="breadcrumb-sep">&gt;</span>
                <span class="breadcrumb-item active">Pencapaian SAP</span>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
                <span style="font-size:12.5px;color:var(--text-muted);">Halo, <strong>{{ Auth::user()->name ?? 'Admin' }}</strong></span>
                <div class="user-avatar" style="width:32px;height:32px;font-size:11.5px;">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 2)) }}</div>
            </div>
        </header>

        <div class="main-content">
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="alert-box success">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:20px;height:20px;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="alert-box error">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:20px;height:20px;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <span class="page-tag">Safety Achievement Points</span>
                    <h1 class="page-title">Pencapaian SAP Karyawan</h1>
                    <p class="page-desc">Rekapitulasi otomatis dan perhitungan poin keaktifan K3 bulanan dari Hazard Report, Inspeksi, Safety Talk, dan Resolusi.</p>
                </div>
            </div>

            <!-- Filter & Action Card -->
            <div class="filter-action-card">
                <form method="GET" action="{{ route('sap.index') }}" class="filter-group">
                    <span class="filter-label">Periode:</span>
                    <select name="month" class="filter-select" id="selectMonth">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $selectedMonth === $m ? 'selected' : '' }}>
                                {{ Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>

                    <select name="year" class="filter-select" id="selectYear">
                        @for ($y = Carbon\Carbon::now()->year + 1; $y >= Carbon\Carbon::now()->year - 2; $y--)
                            <option value="{{ $y }}" {{ $selectedYear === $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>

                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIK..." class="filter-search-input" id="inputSearch">

                    <button type="submit" class="btn-filter" id="btnFilterSubmit">
                        Tampilkan
                    </button>
                    @if (request('search'))
                        <a href="{{ route('sap.index', ['month' => $selectedMonth, 'year' => $selectedYear]) }}" class="btn-filter" style="text-decoration:none;">Reset</a>
                    @endif
                </form>

                <!-- Trigger Hitung Ulang SAP (UC-28) -->
                <form method="POST" action="{{ route('sap.recalculate') }}" onsubmit="return confirm('Jalankan hitung ulang poin SAP untuk periode {{ Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1)->translatedFormat('F Y') }}? Semua aktivitas valid akan disinkronkan ke ledger.');">
                    @csrf
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    <button type="submit" class="btn-recalculate" id="btnRecalculateSap" title="Hitung Ulang SAP Bulanan">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:17px;height:17px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span>Hitung Ulang SAP</span>
                    </button>
                </form>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon points">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.004 0A5.25 5.25 0 0018 9.75V7.5a2.25 2.25 0 00-2.25-2.25h-7.5A2.25 2.25 0 006 7.5v2.25a5.25 5.25 0 005.004 4.5m5.004 0h-5.004" />
                        </svg>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Total Poin Terdistribusi</span>
                        <span class="stat-value" id="statTotalPoints">{{ number_format($totalPoints) }}</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon users">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Karyawan Berpartisipasi</span>
                        <span class="stat-value" id="statParticipating">{{ $participatingEmployees }} <span style="font-size:13px;font-weight:500;color:var(--text-muted);">/ {{ $totalEmployeesRecapped }}</span></span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon avg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Rata-rata Poin</span>
                        <span class="stat-value" id="statAveragePoints">{{ $averagePoints }}</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon targets">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Konversi Terpenuhi</span>
                        <span class="stat-value" id="statTargetMet" style="font-size:19px;">{{ $targetMetCount }} <span style="font-size:12px;color:var(--text-muted);font-weight:600;">HSE</span> &bull; {{ $rewardEligibleCount }} <span style="font-size:12px;color:var(--text-muted);font-weight:600;">Reward</span></span>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="table-card">
                <div class="table-header-bar">
                    <div>
                        <div class="table-title">Daftar Rekap SAP: {{ $periodCarbon->translatedFormat('F Y') }}</div>
                        <div class="table-subtitle">Data sinkronisasi kumulatif dari ledger perolehan poin K3 karyawan</div>
                    </div>
                </div>

                @if ($summaries->isEmpty())
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                        </svg>
                        <h3 class="empty-title">Belum Ada Rekap SAP untuk Periode Ini</h3>
                        <p class="empty-desc">Tekan tombol <strong>Hitung Ulang SAP</strong> di atas untuk memproses dan mengumpulkan seluruh poin karyawan secara otomatis.</p>
                    </div>
                @else
                    <div style="overflow-x:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Karyawan</th>
                                    <th>Departemen</th>
                                    <th style="text-align:center;" title="Hazard Report Valid">Hazard</th>
                                    <th style="text-align:center;" title="Inspeksi Harian Selesai">Inspeksi</th>
                                    <th style="text-align:center;" title="Safety Talk Diikuti">Safety Talk</th>
                                    <th style="text-align:center;" title="Resolusi Bahaya Diterima">Resolusi</th>
                                    <th style="text-align:center;">Total Poin SAP</th>
                                    <th style="text-align:center;">Status Konversi (Target / Reward)</th>
                                    <th>Terakhir Dihitung</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($summaries as $summary)
                                    <tr>
                                        <td>
                                            <div class="user-cell">
                                                <div class="user-circle">
                                                    {{ strtoupper(substr($summary->user?->name ?? 'K', 0, 2)) }}
                                                </div>
                                                <div class="user-name-box">
                                                    <span class="user-full-name">{{ $summary->user?->name ?? 'Karyawan' }}</span>
                                                    <span class="user-nik">NIK: {{ $summary->user?->employeeRegistry?->nik ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge-dept">{{ $summary->department?->name ?? '-' }}</span>
                                        </td>
                                        <td style="text-align:center;font-weight:600;">
                                            {{ $summary->hazard_count }}
                                        </td>
                                        <td style="text-align:center;font-weight:600;">
                                            {{ $summary->inspection_count }}
                                        </td>
                                        <td style="text-align:center;font-weight:600;">
                                            {{ $summary->safety_talk_count }}
                                        </td>
                                        <td style="text-align:center;font-weight:600;">
                                            {{ $summary->resolution_count }}
                                        </td>
                                        <td style="text-align:center;">
                                            <span class="badge-points">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                                </svg>
                                                {{ $summary->total_points }} pts
                                            </span>
                                        </td>
                                        <td style="text-align:center;">
                                            @if ($summary->is_hse_snapshot)
                                                {{-- Karyawan Departemen HSE: Konversi Target Bulanan (KPI) --}}
                                                @if ($summary->target_met === true)
                                                    <span class="badge-target met" title="KPI / Target Bulanan HSE Terpenuhi">Target Terpenuhi</span>
                                                @elseif ($summary->target_met === false)
                                                    <span class="badge-target unmet" title="Poin SAP belum mencapai ambang target HSE">Target Belum Terpenuhi</span>
                                                @else
                                                    <span class="badge-target none" title="Aturan konversi target HSE belum ditetapkan">-</span>
                                                @endif
                                            @else
                                                {{-- Karyawan Non-HSE: Konversi Reward --}}
                                                @if ($summary->reward_eligible && $summary->eligibleReward)
                                                    <span class="badge-reward eligible" title="Berhak atas reward sesuai ambang poin">🎁 {{ $summary->eligibleReward->name }} ({{ $summary->eligibleReward->required_points }} pts)</span>
                                                @else
                                                    <span class="badge-reward not-eligible" title="Poin belum mencapai ambang reward aktif">Belum Mencapai Ambang</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td style="font-size:12px;color:var(--text-muted);">
                                            {{ $summary->calculated_at ? $summary->calculated_at->format('d/m/Y H:i') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($summaries->hasPages())
                        <div class="pagination-container">
                            {{ $summaries->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </main>
</div>
</body>
</html>
