<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Kelola data karyawan, departemen, status kepegawaian, dan akses akun pengguna SafeTrack.">
    <title>Kelola Karyawan – SafeTrack</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        /* ── Design Tokens SafeTrack System ── */
        :root {
            --font-main: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
            --bg-page: #f8fafc;
            --bg-surface: #ffffff;
            --bg-sidebar: #ffffff;
            --border-light: #e2e8f0;
            --border-focus: #3b82f6;
            --navy-dark: #0f2a4a;
            --navy-deep: #0a1c32;
            --navy-btn: #1e293b;
            --navy-btn-hover: #0f172a;
            --amber-accent: #f59e0b;
            --amber-pale: #fef3c7;
            --amber-active-bg: #fffbeb;
            --amber-border: #f59e0b;
            --amber-text: #92400e;
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --status-green-bg: #dcfce7;
            --status-green-text: #15803d;
            --status-green-dot: #16a34a;
            --status-gray-bg: #f1f5f9;
            --status-gray-text: #64748b;
            --status-gray-dot: #94a3b8;
            --sidebar-width: 260px;
            --topbar-height: 64px;
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 14px;
            --radius-xl: 18px;
            --shadow-subtle: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.04);
            --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -2px rgba(0, 0, 0, 0.03);
            --shadow-modal: 0 20px 25px -5px rgba(15, 23, 42, 0.15), 0 8px 10px -6px rgba(15, 23, 42, 0.1);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font-main);
            background-color: var(--bg-page);
            color: var(--text-body);
            font-size: 14px;
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            -webkit-font-smoothing: antialiased;
        }

        :focus-visible {
            outline: 2px solid var(--border-focus);
            outline-offset: 2px;
        }

        .app-shell { display: flex; width: 100%; min-height: 100vh; }

        /* ── Sidebar ── */
        .app-sidebar {
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-light);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
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
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #0f2a4a 0%, #1e3a5f 100%);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 2px 4px rgba(15, 42, 74, 0.2);
        }
        .brand-logo-icon svg { width: 20px; height: 20px; color: #f59e0b; }
        .brand-text { display: flex; flex-direction: column; }
        .brand-title {
            font-size: 18px; font-weight: 800; line-height: 1.1; letter-spacing: -0.02em; color: #0f2a4a;
        }
        .brand-title span { color: #d97706; }
        .brand-subtitle {
            font-size: 9.5px; font-weight: 700; letter-spacing: 0.1em; color: var(--text-muted); text-transform: uppercase; margin-top: 2px;
        }

        .workspace-card {
            margin: 0 16px 16px;
            padding: 10px 12px;
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            display: flex; align-items: center; gap: 10px;
        }
        .workspace-icon {
            width: 32px; height: 32px; background: #f1f5f9; border-radius: 7px;
            display: flex; align-items: center; justify-content: center; color: #475569; flex-shrink: 0;
        }
        .workspace-icon svg { width: 17px; height: 17px; }
        .workspace-info { display: flex; flex-direction: column; overflow: hidden; }
        .workspace-name { font-size: 12.5px; font-weight: 700; color: var(--text-heading); }
        .workspace-desc { font-size: 11px; color: var(--text-light); }

        .nav-section-title {
            padding: 12px 20px 6px;
            font-size: 11px; font-weight: 700; color: #94a3b8;
            letter-spacing: 0.08em; text-transform: uppercase;
        }
        .sidebar-menu { list-style: none; padding: 0 12px; margin-bottom: 8px; }
        .sidebar-menu li { margin-bottom: 2px; }
        .sidebar-link {
            display: flex; align-items: center; gap: 11px;
            padding: 9px 12px; font-size: 13.5px; font-weight: 500;
            color: #475569; text-decoration: none; border-radius: var(--radius-sm);
            transition: all 0.15s ease;
        }
        .sidebar-link svg { width: 17px; height: 17px; flex-shrink: 0; color: #64748b; }
        .sidebar-link:hover { background-color: #f1f5f9; color: var(--text-heading); }
        .sidebar-link.active-amber {
            background-color: var(--amber-pale);
            color: var(--amber-text);
            font-weight: 700;
            border-left: 3px solid var(--amber-border);
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
            padding-left: 9px;
        }
        .sidebar-link.active-amber svg { color: var(--amber-text); }

        .sidebar-user-footer {
            margin-top: auto;
            padding: 14px 16px;
            border-top: 1px solid var(--border-light);
            display: flex; align-items: center; justify-content: space-between;
        }
        .user-meta-wrap { display: flex; align-items: center; gap: 10px; overflow: hidden; }
        .user-avatar-badge {
            width: 34px; height: 34px; border-radius: 50%;
            background: #fef3c7; color: #92400e;
            display: flex; align-items: center; justify-content: center;
            font-size: 12.5px; font-weight: 700; flex-shrink: 0;
        }
        .user-name-title { display: flex; flex-direction: column; overflow: hidden; }
        .user-full-name {
            font-size: 12.5px; font-weight: 700; color: var(--text-heading);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .user-role-label { font-size: 11px; color: var(--text-light); }
        .btn-user-exit {
            background: none; border: none; color: #94a3b8; cursor: pointer; padding: 6px;
            border-radius: 6px; display: flex; align-items: center; justify-content: center;
            transition: all 0.15s ease;
        }
        .btn-user-exit:hover { color: #ef4444; background: #fee2e2; }

        /* ── Main Layout ── */
        .app-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .app-topbar {
            height: var(--topbar-height);
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border-light);
            padding: 0 32px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 30;
        }
        .topbar-breadcrumb {
            display: flex; align-items: center; gap: 8px;
            font-size: 13.5px; color: var(--text-muted);
        }
        .topbar-breadcrumb span.current {
            color: var(--text-heading); font-weight: 600;
        }
        .btn-mobile-menu {
            display: none; background: none; border: none; padding: 6px; cursor: pointer; color: #475569;
        }

        .content-container {
            padding: 32px 36px 60px;
            max-width: 1400px;
            width: 100%;
        }

        /* ── Header ── */
        .page-badge-label {
            font-size: 11px; font-weight: 700; letter-spacing: 0.08em;
            color: #94a3b8; text-transform: uppercase; margin-bottom: 4px;
        }
        .page-title {
            font-size: 26px; font-weight: 800; color: var(--navy-dark);
            letter-spacing: -0.02em; margin-bottom: 4px;
        }
        .page-subtitle {
            font-size: 14px; color: var(--text-muted); margin-bottom: 24px;
        }

        /* ── Summary Cards ── */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .summary-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-xl);
            padding: 20px 24px;
            box-shadow: var(--shadow-subtle);
        }
        .summary-card-title {
            font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;
        }
        .summary-card-val {
            font-size: 32px; font-weight: 800; color: var(--navy-dark); line-height: 1.1;
        }

        /* ── Alerts ── */
        .alert-box {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 14px 18px; border-radius: var(--radius-md);
            margin-bottom: 20px; font-size: 13.5px;
        }
        .alert-box.success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-box.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-box.info { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }

        /* ── Table Card & Toolbar ── */
        .table-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-card);
            overflow: hidden;
        }
        .table-toolbar {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .search-field-wrap {
            position: relative;
            flex: 1;
            min-width: 260px;
            max-width: 420px;
        }
        .search-icon-svg {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            width: 17px; height: 17px; color: #94a3b8; pointer-events: none;
        }
        .search-input {
            width: 100%;
            padding: 9px 12px 9px 38px;
            font-size: 13.5px;
            font-family: inherit;
            color: var(--text-heading);
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            transition: all 0.15s ease;
        }
        .search-input:focus {
            border-color: var(--border-focus);
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .filter-group-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .select-filter {
            padding: 9px 14px;
            font-size: 13.5px;
            font-family: inherit;
            color: var(--text-body);
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: border-color 0.15s ease;
        }
        .select-filter:focus {
            border-color: var(--border-focus);
            outline: none;
        }
        .btn-filter-reset {
            padding: 9px 14px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            background: #f1f5f9;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            text-decoration: none;
            display: inline-flex; align-items: center; gap: 6px;
            transition: all 0.15s ease;
        }
        .btn-filter-reset:hover {
            background: #e2e8f0; color: var(--text-heading);
        }

        /* ── Table Styling ── */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .safetrack-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .safetrack-table thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border-light);
            white-space: nowrap;
        }
        .safetrack-table tbody td {
            padding: 16px 20px;
            font-size: 13.5px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .safetrack-table tbody tr:last-child td { border-bottom: none; }
        .safetrack-table tbody tr:hover { background-color: #fafbfc; }

        .employee-info-cell {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .employee-name-bold {
            font-weight: 700;
            color: var(--text-heading);
            font-size: 14px;
        }
        .employee-email-sub {
            font-size: 12.5px;
            color: var(--text-muted);
        }
        .nik-tag {
            font-family: var(--font-mono);
            font-size: 12.5px;
            font-weight: 600;
            color: #1e293b;
            background: #f1f5f9;
            padding: 4px 8px;
            border-radius: 5px;
            display: inline-block;
        }

        /* ── Status Badge ── */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        .status-badge.active {
            background-color: var(--status-green-bg);
            color: var(--status-green-text);
        }
        .status-badge.inactive {
            background-color: var(--status-gray-bg);
            color: var(--status-gray-text);
        }
        .status-dot {
            width: 6px; height: 6px; border-radius: 50%;
        }
        .status-badge.active .status-dot { background-color: var(--status-green-dot); }
        .status-badge.inactive .status-dot { background-color: var(--status-gray-dot); }

        .btn-kelola-link {
            background: none;
            border: none;
            color: var(--navy-dark);
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: underline;
            text-underline-offset: 3px;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.15s ease;
        }
        .btn-kelola-link:hover {
            color: #1d4ed8;
            background: #eff6ff;
        }

        /* ── Empty State ── */
        .empty-state-row {
            text-align: center;
            padding: 48px 24px;
        }
        .empty-state-icon {
            width: 44px; height: 44px; margin: 0 auto 12px; color: #94a3b8;
        }
        .empty-state-title {
            font-size: 15px; font-weight: 700; color: var(--text-heading); margin-bottom: 4px;
        }
        .empty-state-desc {
            font-size: 13px; color: var(--text-muted);
        }

        /* ── Table Footer ── */
        .table-card-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .footer-note-text {
            font-size: 12.5px;
            color: var(--text-muted);
        }

        /* ── Modal Design ── */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
            z-index: 100;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .modal-backdrop.open {
            display: flex;
            opacity: 1;
        }
        .modal-card {
            background: var(--bg-surface);
            border-radius: var(--radius-xl);
            width: 100%;
            max-width: 520px;
            box-shadow: var(--shadow-modal);
            transform: translateY(10px);
            transition: transform 0.2s ease;
            overflow: hidden;
        }
        .modal-backdrop.open .modal-card {
            transform: translateY(0);
        }
        .modal-header {
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-light);
        }
        .modal-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--navy-dark);
        }
        .btn-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            line-height: 1;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: color 0.15s ease;
        }
        .btn-modal-close:hover { color: var(--text-heading); }

        .modal-body {
            padding: 24px;
        }

        /* Info Box inside Modal */
        .modal-info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius-md);
            padding: 14px 16px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .info-box-name {
            font-size: 15px;
            font-weight: 700;
            color: var(--navy-dark);
        }
        .info-box-sub {
            font-size: 12.5px;
            color: var(--text-muted);
            font-family: var(--font-mono);
        }

        .form-group {
            margin-bottom: 18px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        .form-input-text, .form-select-control {
            width: 100%;
            padding: 10px 14px;
            font-size: 13.5px;
            font-family: inherit;
            color: var(--text-heading);
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            transition: all 0.15s ease;
        }
        .form-input-text[readonly], .form-select-control[disabled] {
            background: #f8fafc;
            color: #64748b;
            cursor: not-allowed;
        }
        .form-input-text:focus, .form-select-control:focus {
            border-color: var(--border-focus);
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }
        .form-hint-text {
            font-size: 11.5px;
            color: var(--text-muted);
            margin-top: 5px;
        }

        /* Reset Password Button inside Modal */
        .reset-pwd-box {
            background: #fdfdfd;
            border: 1px dashed var(--border-light);
            border-radius: var(--radius-md);
            padding: 12px 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .btn-reset-pwd {
            background: #ffffff;
            border: 1px solid var(--border-light);
            color: #1e293b;
            font-size: 12.5px;
            font-weight: 600;
            padding: 7px 12px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s ease;
        }
        .btn-reset-pwd:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .modal-danger-divider {
            border-top: 1px solid var(--border-light);
            padding-top: 16px;
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .btn-danger-text {
            background: none;
            border: none;
            color: #dc2626;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 8px;
            border-radius: var(--radius-sm);
            transition: background 0.15s ease;
        }
        .btn-danger-text:hover {
            background: #fee2e2;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border-light);
            background: #fafbfc;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
        }
        .btn-modal-cancel {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 13.5px;
            font-weight: 600;
            padding: 9px 18px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: color 0.15s ease;
        }
        .btn-modal-cancel:hover { color: var(--text-heading); }
        .btn-modal-save {
            background: var(--navy-dark);
            border: none;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 700;
            padding: 9px 22px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(15, 42, 74, 0.2);
            transition: background 0.15s ease;
        }
        .btn-modal-save:hover { background: #163a66; }

        .btn-modal-danger-submit {
            background: #dc2626;
            border: none;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 700;
            padding: 9px 22px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .btn-modal-danger-submit:hover { background: #b91c1c; }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .app-sidebar { transform: translateX(-100%); transition: transform 0.2s ease; }
            .app-sidebar.open { transform: translateX(0); }
            .app-main { margin-left: 0; }
            .summary-grid { grid-template-columns: 1fr; }
            .content-container { padding: 20px 16px 40px; }
            .app-topbar { padding: 0 16px; }
            .btn-mobile-menu { display: inline-flex; }
        }
    </style>
</head>
<body>
<div class="app-shell">

    <!-- ══ SafeTrack Sidebar ══ -->
    <aside class="app-sidebar" id="sidebarNav" role="navigation" aria-label="Navigasi SafeTrack">
        <!-- Logo SafeTrack -->
        <div class="brand-container">
            <div class="brand-logo-icon">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L4 5V11.5C4 16.5 7.4 21.1 12 22.3C16.6 21.1 20 16.5 20 11.5V5L12 2Z" fill="currentColor" fill-opacity="0.2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 12L11 14L15 9.5" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="brand-text">
                <div class="brand-title">safe<span>Track</span></div>
                <div class="brand-subtitle">SAFETY MANAGEMENT</div>
            </div>
        </div>

        <!-- Workspace Demonstrasi -->
        <div class="workspace-card">
            <div class="workspace-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
            </div>
            <div class="workspace-info">
                <span class="workspace-name">PT Sentra Industri</span>
                <span class="workspace-desc">Workspace demonstrasi</span>
            </div>
        </div>

        <!-- UTAMA Menu Group -->
        <div class="nav-section-title">Utama</div>
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('dashboard') }}" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
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
                <a href="{{ route('daily-inspections.index') }}" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V19.5a2.25 2.25 0 002.25 2.25h.75" />
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
                <a href="#" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zM12 2.25V4.5m5.834.166l-1.591 1.591M20.25 10.5H18M7.757 14.757l-1.59 1.59M6 10.5H3.75m4.007-4.243l-1.59-1.59" />
                    </svg>
                    <span>Pencapaian SAP</span>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    <span>Notifikasi</span>
                </a>
            </li>
        </ul>

        <!-- ADMINISTRASI Menu Group -->
        <div class="nav-section-title">Administrasi</div>
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('admin.pengguna.index') }}" class="sidebar-link active-amber" id="menuKelolaKaryawan">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <span>Kelola Karyawan</span>
                </a>
            </li>
            <li>
                <a href="{{ route('shifts.index') }}" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Kelola Shift Kerja</span>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                    </svg>
                    <span>Data Master</span>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>Ekspor Laporan</span>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Jejak Audit</span>
                </a>
            </li>
        </ul>

        <!-- Bottom User Profile Card -->
        <div class="sidebar-user-footer">
            <div class="user-meta-wrap">
                <div class="user-avatar-badge">{{ strtoupper(substr(Auth::user()->name ?? 'AH', 0, 2)) }}</div>
                <div class="user-name-title">
                    <span class="user-full-name">{{ Auth::user()->name ?? 'Admin HSE' }}</span>
                    <span class="user-role-label">
                        {{ (Auth::user() && Auth::user()->role === 'super_admin') ? 'Super Admin' : 'Admin / HSE' }}
                    </span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn-user-exit" title="Keluar / Logout" id="btnLogoutSidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:17px;height:17px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- ══ Main Content ══ -->
    <main class="app-main">
        <!-- Topbar -->
        <header class="app-topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button type="button" class="btn-mobile-menu" id="btnToggleSidebar" aria-label="Buka menu navigasi">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:20px;height:20px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <nav class="topbar-breadcrumb" aria-label="Breadcrumb">
                    <span>Workspace</span>
                    <span>&rsaquo;</span>
                    <span class="current">Kelola Karyawan</span>
                </nav>
            </div>
        </header>

        <div class="content-container">

            <!-- Flash Alert Messages -->
            @if (session('success'))
                <div class="alert-box success" role="alert" id="alertSuccess">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:20px;height:20px;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="alert-box error" role="alert" id="alertError">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:20px;height:20px;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if (session('info'))
                <div class="alert-box info" role="alert" id="alertInfo">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:20px;height:20px;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert-box error" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:20px;height:20px;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <div>
                        <div style="font-weight:700;margin-bottom:2px;">Terdapat kesalahan pada input:</div>
                        <ul style="padding-left:18px;margin:0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Page Title Section -->
            <div class="page-badge-label">ADMINISTRASI</div>
            <h1 class="page-title">Kelola karyawan</h1>
            <p class="page-subtitle">Atur departemen, status kepegawaian, dan akses akun karyawan.</p>

            <!-- 3 Summary Cards -->
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-card-title">Seluruh pengguna</div>
                    <div class="summary-card-val" id="statTotalUsers">{{ $totalUsers }}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-card-title">Akun aktif</div>
                    <div class="summary-card-val" id="statActiveUsers">{{ $activeUsers }}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-card-title">Departemen</div>
                    <div class="summary-card-val" id="statTotalDepartments">{{ $totalDepartments }}</div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="table-card">
                <!-- Toolbar: Search & Filters -->
                <form method="GET" action="{{ route('admin.pengguna.index') }}" class="table-toolbar" id="filterForm">
                    <div class="search-field-wrap">
                        <svg class="search-icon-svg" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <input type="text"
                               name="search"
                               id="inputSearch"
                               value="{{ $filters['search'] ?? '' }}"
                               class="search-input"
                               placeholder="Cari nama, NIK, atau email..."
                               aria-label="Pencarian nama, NIK, atau email">
                    </div>

                    <div class="filter-group-wrap">
                        <!-- Filter Departemen -->
                        <select name="department_id" class="select-filter" id="selectDepartment" onchange="this.form.submit()" aria-label="Filter Departemen">
                            <option value="">Semua departemen</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ (string)($filters['department_id'] ?? '') === (string)$dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Filter Status Akun -->
                        <select name="status" class="select-filter" id="selectStatus" onchange="this.form.submit()" aria-label="Filter Status Akun">
                            <option value="">Semua status</option>
                            <option value="1" {{ (string)($filters['status'] ?? '') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ (string)($filters['status'] ?? '') === '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>

                        @if (!empty($filters['search']) || !empty($filters['department_id']) || ($filters['status'] !== null && $filters['status'] !== ''))
                            <a href="{{ route('admin.pengguna.index') }}" class="btn-filter-reset" title="Reset semua filter">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span>Reset</span>
                            </a>
                        @endif
                    </div>
                </form>

                <!-- Responsive Table -->
                <div class="table-responsive">
                    <table class="safetrack-table">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th>NIK</th>
                                <th>Departemen</th>
                                <th>Kepegawaian</th>
                                <th>Peran</th>
                                <th>Status Akun</th>
                                <th style="text-align:right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                @php
                                    $nik = $user->employeeRegistry?->nik ?? '-';
                                    $deptName = $user->department?->name ?? '-';
                                    $employmentStatusName = $user->employeeRegistry?->employmentStatus?->name ?? 'Tetap';
                                    $employmentStatusId = $user->employeeRegistry?->employment_status_id ?? ($employmentStatuses->first()->id ?? 1);
                                    $roleText = match($user->role) {
                                        'super_admin' => 'Admin Utama',
                                        'admin' => 'Admin / HSE',
                                        default => 'Karyawan',
                                    };
                                @endphp
                                <tr id="user-row-{{ $user->id }}">
                                    <td>
                                        <div class="employee-info-cell">
                                            <span class="employee-name-bold">{{ $user->name }}</span>
                                            <span class="employee-email-sub">{{ $user->email }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="nik-tag">{{ $nik }}</span>
                                    </td>
                                    <td>{{ $deptName }}</td>
                                    <td>{{ $employmentStatusName }}</td>
                                    <td>{{ $roleText }}</td>
                                    <td>
                                        @if ($user->is_active)
                                            <span class="status-badge active">
                                                <span class="status-dot"></span>
                                                Aktif
                                            </span>
                                        @else
                                            <span class="status-badge inactive">
                                                <span class="status-dot"></span>
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align:right;">
                                        <button type="button"
                                                class="btn-kelola-link"
                                                data-id="{{ $user->id }}"
                                                data-name="{{ $user->name }}"
                                                data-nik="{{ $nik }}"
                                                data-email="{{ $user->email }}"
                                                data-dept-id="{{ $user->department_id }}"
                                                data-employment-id="{{ $employmentStatusId }}"
                                                data-is-active="{{ $user->is_active ? 1 : 0 }}"
                                                data-role-text="{{ $roleText }}"
                                                data-is-self="{{ Auth::id() === $user->id ? 1 : 0 }}"
                                                onclick="openManageModal(this)"
                                                id="btnKelolaUser{{ $user->id }}">
                                            Kelola
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state-row">
                                            <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                            </svg>
                                            <div class="empty-state-title">Data tidak ditemukan</div>
                                            <div class="empty-state-desc">Tidak ada karyawan yang sesuai dengan kata kunci pencarian atau filter yang diterapkan.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Table Card Footer -->
                <div class="table-card-footer">
                    <div class="footer-note-text">
                        Menampilkan {{ $users->count() }} pengguna. Akun baru dibuat melalui registrasi mandiri NIK yang terdaftar.
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- ══ Modal 1: Kelola Akun Karyawan ══ -->
<div class="modal-backdrop" id="modalManagePengguna" role="dialog" aria-modal="true" aria-labelledby="modalManageTitle">
    <div class="modal-card">
        <div class="modal-header">
            <h2 class="modal-title" id="modalManageTitle">Kelola akun karyawan</h2>
            <button type="button" class="btn-modal-close" onclick="closeManageModal()" aria-label="Tutup modal">&times;</button>
        </div>

        <form id="formManageUser" method="POST" action="">
            @csrf
            @method('PUT')

            <div class="modal-body">
                <!-- Top Info Box (Nama & NIK · Email read-only) -->
                <div class="modal-info-box">
                    <div class="info-box-name" id="modalInfoName">-</div>
                    <div class="info-box-sub" id="modalInfoMeta">-</div>
                </div>

                <!-- Field Departemen -->
                <div class="form-group">
                    <label class="form-label" for="modalDeptSelect">Departemen</label>
                    <select name="department_id" id="modalDeptSelect" class="form-select-control" required>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Field Status Kepegawaian -->
                <div class="form-group">
                    <label class="form-label" for="modalEmploymentSelect">Status kepegawaian</label>
                    <select name="employment_status_id" id="modalEmploymentSelect" class="form-select-control" required>
                        @foreach ($employmentStatuses as $empStatus)
                            <option value="{{ $empStatus->id }}">{{ $empStatus->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Field Status Akun -->
                <div class="form-group">
                    <label class="form-label" for="modalStatusSelect">Status akun</label>
                    <select name="is_active" id="modalStatusSelect" class="form-select-control" required>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                    <div class="form-hint-text">Akun nonaktif tidak dapat mengakses sistem.</div>
                </div>

                <!-- Field Peran (Read-only) -->
                <div class="form-group">
                    <label class="form-label" for="modalRoleText">Peran</label>
                    <input type="text" id="modalRoleText" class="form-input-text" readonly value="Karyawan">
                    <div class="form-hint-text">Peran ditentukan oleh sistem otorisasi dan tidak dapat diubah di sini.</div>
                </div>

                <!-- Simulasikan Reset Password -->
                <div class="reset-pwd-box">
                    <div>
                        <div style="font-size:13px;font-weight:700;color:var(--text-heading);">Reset Password</div>
                        <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px;">
                            Demo menampilkan tautan reset tanpa mengirim email sungguhan.
                        </div>
                    </div>
                    <button type="button" class="btn-reset-pwd" onclick="triggerResetPasswordStub()" id="btnTriggerResetPassword">
                        Simulasikan reset password
                    </button>
                </div>

                <!-- Aksi Hapus Akun (Soft Delete) -->
                <div class="modal-danger-divider">
                    <div style="font-size:12.5px;color:var(--text-muted);">Tindakan berbahaya untuk akun ini</div>
                    <button type="button" class="btn-danger-text" onclick="openDeleteConfirmModal()" id="btnOpenDeleteModal">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        <span>Hapus akun</span>
                    </button>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeManageModal()">Batal</button>
                <button type="submit" class="btn-modal-save" id="btnSubmitManageUser">Simpan perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ Modal 2: Konfirmasi Hapus Akun (Soft Delete) ══ -->
<div class="modal-backdrop" id="modalDeletePengguna" role="dialog" aria-modal="true" aria-labelledby="modalDeleteTitle">
    <div class="modal-card">
        <div class="modal-header" style="background:#fef2f2;border-bottom-color:#fecaca;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:32px;border-radius:50%;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h2 class="modal-title" id="modalDeleteTitle" style="color:#991b1b;">Hapus Akun Karyawan</h2>
            </div>
            <button type="button" class="btn-modal-close" onclick="closeDeleteModal()" aria-label="Tutup modal">&times;</button>
        </div>

        <form id="formDeleteUser" method="POST" action="">
            @csrf
            @method('DELETE')

            <div class="modal-body">
                <p style="font-size:13.5px;color:#475569;margin-bottom:14px;line-height:1.6;">
                    Anda akan menghapus akun karyawan <strong id="deleteTargetName">-</strong>. Akun yang dihapus tidak akan dapat masuk ke sistem lagi. NIK karyawan akan tetap tersimpan sebagai riwayat.
                </p>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="inputDeletionReason">
                        Alasan penghapusan <span style="color:#dc2626;">*</span>
                    </label>
                    <textarea name="deletion_reason"
                              id="inputDeletionReason"
                              rows="3"
                              class="form-input-text"
                              style="resize:vertical;"
                              placeholder="Tuliskan alasan penghapusan akun karyawan ini..."
                              required></textarea>
                    <div class="form-hint-text">Alasan wajib diisi untuk keperluan rekam audit internal.</div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeDeleteModal()">Batal</button>
                <button type="submit" class="btn-modal-danger-submit" id="btnConfirmDelete">Hapus akun</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ Modal 3: Reset Password Stub Form ══ -->
<form id="formResetPasswordStub" method="POST" action="" style="display:none;">
    @csrf
</form>

<script>
    let currentSelectedUserId = null;
    let currentSelectedUserName = '';
    let currentSelectedUserIsSelf = false;

    // Mobile Sidebar Toggle
    const btnToggleSidebar = document.getElementById('btnToggleSidebar');
    const sidebarNav = document.getElementById('sidebarNav');
    if (btnToggleSidebar && sidebarNav) {
        btnToggleSidebar.addEventListener('click', () => {
            sidebarNav.classList.toggle('open');
        });
    }

    // Modal Kelola
    function openManageModal(btn) {
        currentSelectedUserId = btn.dataset.id;
        currentSelectedUserName = btn.dataset.name;
        currentSelectedUserIsSelf = btn.dataset.isSelf === '1';

        document.getElementById('modalInfoName').textContent = btn.dataset.name;
        document.getElementById('modalInfoMeta').textContent = `${btn.dataset.nik} · ${btn.dataset.email}`;
        document.getElementById('modalDeptSelect').value = btn.dataset.deptId;
        document.getElementById('modalEmploymentSelect').value = btn.dataset.employmentId;
        document.getElementById('modalStatusSelect').value = btn.dataset.isActive;
        document.getElementById('modalRoleText').value = btn.dataset.roleText;

        // Aturan keamanan: Admin tidak boleh menonaktifkan atau menghapus dirinya sendiri
        const statusSelect = document.getElementById('modalStatusSelect');
        const btnDelete = document.getElementById('btnOpenDeleteModal');
        if (currentSelectedUserIsSelf) {
            statusSelect.disabled = true;
            statusSelect.title = "Anda tidak dapat menonaktifkan akun Anda sendiri.";
            btnDelete.style.display = 'none';
        } else {
            statusSelect.disabled = false;
            statusSelect.title = "";
            btnDelete.style.display = 'inline-flex';
        }

        const updateUrl = "{{ url('admin/pengguna') }}/" + currentSelectedUserId;
        document.getElementById('formManageUser').action = updateUrl;

        const modal = document.getElementById('modalManagePengguna');
        modal.classList.add('open');
    }

    function closeManageModal() {
        document.getElementById('modalManagePengguna').classList.remove('open');
    }

    // Modal Delete
    function openDeleteConfirmModal() {
        if (currentSelectedUserIsSelf) {
            alert("Anda tidak dapat menghapus akun Anda sendiri.");
            return;
        }

        closeManageModal();
        document.getElementById('deleteTargetName').textContent = currentSelectedUserName;
        document.getElementById('inputDeletionReason').value = '';

        const deleteUrl = "{{ url('admin/pengguna') }}/" + currentSelectedUserId;
        document.getElementById('formDeleteUser').action = deleteUrl;

        const modal = document.getElementById('modalDeletePengguna');
        modal.classList.add('open');
    }

    function closeDeleteModal() {
        document.getElementById('modalDeletePengguna').classList.remove('open');
    }

    // Trigger Reset Password (Stub PBI-08)
    function triggerResetPasswordStub() {
        if (!currentSelectedUserId) return;
        const form = document.getElementById('formResetPasswordStub');
        form.action = "{{ url('admin/pengguna') }}/" + currentSelectedUserId + "/reset-password";
        form.submit();
    }

    // Close on Escape or click outside
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeManageModal();
            closeDeleteModal();
        }
    });

    document.querySelectorAll('.modal-backdrop').forEach((backdrop) => {
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) {
                closeManageModal();
                closeDeleteModal();
            }
        });
    });
</script>
</body>
</html>
