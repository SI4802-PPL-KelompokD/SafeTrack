<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            --border-focus: #2563eb;
            --navy-dark: #0f2a4a;
            --navy-deep: #0a1c32;
            --navy-btn: #102a45;
            --navy-btn-hover: #163a66;
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
            --status-red-bg: #fee2e2;
            --status-red-text: #b91c1c;
            --status-red-dot: #dc2626;
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
            font-size: 18px;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.02em;
            color: #0f2a4a;
        }
        .brand-title span { color: #d97706; }
        .brand-subtitle {
            font-size: 9.5px;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.12em;
            margin-top: 1px;
        }

        /* Workspace Card */
        .workspace-card {
            margin: 0 12px 16px;
            padding: 10px 12px;
            background: #f8fafc;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .workspace-icon {
            width: 28px; height: 28px;
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
            color: #475569;
            flex-shrink: 0;
        }
        .workspace-icon svg { width: 16px; height: 16px; }
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

        /* Navigation */
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
        .sidebar-menu li { margin-bottom: 2px; }
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
            width: 19px; height: 19px;
            color: #64748b;
            flex-shrink: 0;
        }
        .sidebar-link:hover {
            background-color: #f1f5f9;
            color: var(--text-heading);
        }
        .sidebar-link:hover svg { color: var(--text-heading); }
        .sidebar-link.active-amber {
            background-color: var(--amber-active-bg);
            color: var(--amber-text);
            font-weight: 700;
            border-left: 3px solid var(--amber-border);
        }
        .sidebar-link.active-amber svg { color: #d97706; }

        /* Bottom User Footer */
        .sidebar-user-footer {
            margin-top: auto;
            padding: 16px;
            border-top: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .user-meta-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow: hidden;
        }
        .user-avatar-badge {
            width: 32px; height: 32px;
            background: #e2e8f0;
            color: #475569;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
            font-size: 11.5px;
            flex-shrink: 0;
        }
        .user-name-title {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .user-full-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-heading);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .user-role-label {
            font-size: 11px;
            color: var(--text-muted);
            white-space: nowrap;
        }
        .btn-user-exit {
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 6px;
            border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
            transition: color 0.15s ease, background-color 0.15s ease;
        }
        .btn-user-exit:hover {
            color: #dc2626;
            background-color: #fee2e2;
        }

        /* ── Main Content Area ── */
        .app-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Topbar */
        .app-topbar {
            height: var(--topbar-height);
            background: #ffffff;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 30;
        }
        .topbar-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-muted);
        }
        .topbar-breadcrumb .current {
            color: var(--text-heading);
            font-weight: 600;
        }
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .topbar-search-input {
            padding: 6px 12px 6px 32px;
            font-size: 13px;
            border: 1px solid var(--border-light);
            border-radius: 20px;
            background: #f8fafc;
            color: var(--text-body);
            width: 190px;
        }
        .topbar-account-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            border: 1px solid var(--border-light);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-body);
        }

        .content-container {
            padding: 32px 32px 48px;
            max-width: 1240px;
            width: 100%;
            margin: 0 auto;
        }

        /* Page Headers */
        .page-badge-label {
            font-size: 11px;
            font-weight: 800;
            color: #64748b;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .page-title {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-heading);
            letter-spacing: -0.02em;
            line-height: 1.2;
            margin-bottom: 4px;
        }
        .page-subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        /* ── 3 Summary Cards ── */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .summary-card {
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            padding: 20px 24px;
            box-shadow: var(--shadow-subtle);
        }
        .summary-card-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
        }
        .summary-card-val {
            font-size: 32px;
            font-weight: 800;
            color: var(--text-heading);
            line-height: 1;
        }

        /* ── Table Card ── */
        .table-card {
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-subtle);
        }

        /* Toolbar: Search & Filters */
        .table-toolbar {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            background: #ffffff;
        }
        .search-field-wrap {
            position: relative;
            flex: 1;
            min-width: 260px;
            max-width: 380px;
        }
        .search-icon-svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 17px;
            height: 17px;
            color: #94a3b8;
            pointer-events: none;
        }
        .search-input {
            width: 100%;
            padding: 9px 14px 9px 38px;
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
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
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
            background: #ffffff;
            color: #64748b;
            font-size: 12px;
            font-weight: 600;
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
            background-color: var(--status-red-bg);
            color: var(--status-red-text);
        }
        .status-dot {
            width: 6px; height: 6px; border-radius: 50%;
        }
        .status-badge.active .status-dot { background-color: var(--status-green-dot); }
        .status-badge.inactive .status-dot { background-color: var(--status-red-dot); }

        /* Actions in Table */
        .table-action-links {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 14px;
        }
        .btn-action-kelola {
            background: none;
            border: none;
            color: var(--navy-dark);
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            padding: 4px 0;
            transition: color 0.15s ease;
        }
        .btn-action-kelola:hover {
            color: #2563eb;
            text-decoration: underline;
        }
        .btn-action-hapus {
            background: none;
            border: none;
            color: #dc2626;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            padding: 4px 0;
            transition: color 0.15s ease;
        }
        .btn-action-hapus:hover {
            color: #b91c1c;
            text-decoration: underline;
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
            padding: 16px 20px;
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

        /* ── Page Footer ── */
        .app-page-footer {
            margin-top: 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text-light);
            padding: 0 4px;
        }
        .status-sync-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #f59e0b;
            font-weight: 500;
        }
        .status-sync-dot {
            width: 7px; height: 7px; border-radius: 50%; background-color: #f59e0b;
        }

        /* ── Modal Design ── */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
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
            max-width: 580px;
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
            font-size: 22px;
            line-height: 1;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: color 0.15s ease;
        }
        .btn-modal-close:hover { color: var(--text-heading); }

        .modal-body {
            padding: 24px;
        }

        /* Info Box inside Modal */
        .modal-info-box {
            background: #f0f7ff;
            border: 1px solid #dbeafe;
            border-radius: var(--radius-md);
            padding: 14px 18px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .info-box-name {
            font-size: 15px;
            font-weight: 700;
            color: var(--navy-dark);
        }
        .info-box-sub {
            font-size: 13px;
            color: var(--text-muted);
        }

        /* 2 Column Modal Grid */
        .modal-grid-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
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
            padding: 9px 12px;
            font-size: 13.5px;
            font-family: inherit;
            color: var(--text-heading);
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            transition: all 0.15s ease;
        }
        .form-input-text[readonly] {
            background: #f8fafc;
            color: #64748b;
            cursor: not-allowed;
            border-color: #e2e8f0;
        }
        .form-input-text:focus, .form-select-control:focus {
            border-color: var(--border-focus);
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .modal-alert-error {
            display: none;
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border-light);
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
        }
        .btn-modal-cancel {
            background: #ffffff;
            border: 1px solid var(--border-light);
            color: #475569;
            font-size: 13.5px;
            font-weight: 600;
            padding: 9px 20px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-modal-cancel:hover {
            background: #f8fafc;
            color: var(--text-heading);
        }
        .btn-modal-save {
            background: var(--navy-dark);
            border: none;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 700;
            padding: 9px 24px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(15, 42, 74, 0.2);
            transition: background 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-modal-save:hover { background: var(--navy-btn-hover); }
        .btn-modal-save:disabled, .btn-modal-danger-submit:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        .btn-modal-danger-submit {
            background: #dc2626;
            border: none;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 700;
            padding: 9px 24px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: background 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-modal-danger-submit:hover { background: #b91c1c; }

        /* Floating Toast */
        .toast-floating {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 120;
            padding: 12px 20px;
            border-radius: var(--radius-md);
            background: #0f172a;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 600;
            box-shadow: var(--shadow-modal);
            display: none;
            align-items: center;
            gap: 10px;
            opacity: 0;
            transform: translateY(-8px);
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        .toast-floating.show {
            display: flex;
            opacity: 1;
            transform: translateY(0);
        }
        .toast-floating.success { background: #065f46; color: #ffffff; }
        .toast-floating.error { background: #991b1b; color: #ffffff; }

        /* Responsive */
        @media (max-width: 900px) {
            .app-sidebar { transform: translateX(-100%); transition: transform 0.2s ease; }
            .app-sidebar.open { transform: translateX(0); }
            .app-main { margin-left: 0; }
            .summary-grid { grid-template-columns: 1fr; }
            .content-container { padding: 20px 16px 40px; }
            .app-topbar { padding: 0 16px; }
            .modal-grid-row { grid-template-columns: 1fr; }
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

        <!-- Workspace Demonstrasi (PT Banjasari Pribumi) -->
        <div class="workspace-card">
            <div class="workspace-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
            </div>
            <div class="workspace-info">
                <span class="workspace-name">PT Banjasari Pribumi</span>
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
                <a href="{{ route('hazards.index') }}" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <span>Laporan Bahaya</span>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link">
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
                <a href="{{ route('sap.index') }}" class="sidebar-link">
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
        @can('access-admin')
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
        @endcan

        <!-- Bottom User Profile Card -->
        <div class="sidebar-user-footer">
            <div class="user-meta-wrap">
                <div class="user-avatar-badge">{{ strtoupper(substr(Auth::user()->name ?? 'AU', 0, 2)) }}</div>
                <div class="user-name-title">
                    <span class="user-full-name">{{ Auth::user()->name ?? 'Admin Utama' }}</span>
                    <span class="user-role-label">
                        {{ (Auth::user() && Auth::user()->role === 'super_admin') ? 'Admin Utama' : 'Admin' }}
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
            <nav class="topbar-breadcrumb" aria-label="Breadcrumb">
                <span>Workspace</span>
                <span>&rsaquo;</span>
                <span class="current">Kelola Karyawan</span>
            </nav>

            <div class="topbar-actions">
                @include('partials.role-badge')
                <div style="position:relative;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:#94a3b8;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input type="text" class="topbar-search-input" placeholder="Cari laporan bahaya..." readonly>
                </div>
                <div class="topbar-account-pill">
                    <span style="font-size:10px;color:#94a3b8;font-weight:700;">GANTI AKUN</span>
                    <span>Admin Utama · Admin</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px;color:#64748b;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </div>
                <button type="button" style="background:none;border:none;color:#64748b;cursor:pointer;display:flex;align-items:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:18px;height:18px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                </button>
                <div class="user-avatar-badge" style="width:28px;height:28px;font-size:11px;">AU</div>
            </div>
        </header>

        <div class="content-container">

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
                                <th>Status akun</th>
                                <th style="text-align:right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            @forelse ($users as $user)
                                @php
                                    $nik = $user->employeeRegistry?->nik ?? '-';
                                    $deptName = $user->department?->name ?? '-';
                                    $employmentStatusName = $user->employeeRegistry?->employmentStatus?->name ?? 'Tetap';
                                    $employmentStatusId = $user->employeeRegistry?->employment_status_id ?? ($employmentStatuses->first()->id ?? 1);
                                    $roleText = match($user->role) {
                                        'super_admin' => 'Admin Utama',
                                        'admin' => 'Admin',
                                        default => 'Karyawan',
                                    };
                                    $isEmployee = ($user->role === 'employee');
                                @endphp
                                <tr id="user-row-{{ $user->id }}"
                                    data-id="{{ $user->id }}"
                                    data-name="{{ $user->name }}"
                                    data-nik="{{ $nik }}"
                                    data-email="{{ $user->email }}"
                                    data-dept-id="{{ $user->department_id }}"
                                    data-employment-id="{{ $employmentStatusId }}"
                                    data-is-active="{{ $user->is_active ? 1 : 0 }}"
                                    data-is-employee="{{ $isEmployee ? 1 : 0 }}">
                                    <td>
                                        <div class="employee-info-cell">
                                            <span class="employee-name-bold row-name">{{ $user->name }}</span>
                                            <span class="employee-email-sub row-email">{{ $user->email }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="row-nik">{{ $nik }}</span>
                                    </td>
                                    <td class="row-dept">{{ $deptName }}</td>
                                    <td class="row-employment">{{ $employmentStatusName }}</td>
                                    <td>{{ $roleText }}</td>
                                    <td>
                                        <div class="row-status-container">
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
                                        </div>
                                    </td>
                                    <td style="text-align:right;">
                                        <div class="table-action-links">
                                            @if ($isEmployee)
                                                <button type="button"
                                                        class="btn-action-kelola"
                                                        onclick="openManageModal({{ $user->id }})">
                                                    Kelola
                                                </button>
                                                <button type="button"
                                                        class="btn-action-hapus"
                                                        onclick="openDeleteModalDirect({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                                    Hapus
                                                </button>
                                            @else
                                                <button type="button"
                                                        class="btn-action-kelola"
                                                        style="opacity:0.6;cursor:default;"
                                                        title="Akun Admin tidak menjadi target pengelolaan">
                                                    Kelola
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state-row">
                                            <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                            </svg>
                                            @if (!empty($filters['search']) || !empty($filters['department_id']) || ($filters['status'] !== null && $filters['status'] !== ''))
                                                <div class="empty-state-title">Data tidak ditemukan</div>
                                                <div class="empty-state-desc">Tidak ada karyawan yang sesuai dengan kata kunci pencarian atau filter yang diterapkan.</div>
                                            @else
                                                <div class="empty-state-title">Belum ada data karyawan</div>
                                                <div class="empty-state-desc">Belum ada akun karyawan yang terdaftar di dalam sistem.</div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Table Card Footer -->
                <div class="table-card-footer">
                    <div class="footer-note-text" id="footerCountText">
                        Menampilkan {{ $users->count() }} pengguna. Akun baru dibuat melalui registrasi menggunakan NIK yang terdaftar.
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>

            <!-- Page Bottom Footer -->
            <div class="app-page-footer">
                <div>&copy; 2026 SafeTrack. Setiap kepedulian berarti.</div>
                <div class="status-sync-indicator">
                    <span class="status-sync-dot"></span>
                    <span>Sistem aktif - Data tersimpan otomatis</span>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- ══ Floating Toast ══ -->
<div class="toast-floating" id="floatingToast" role="alert">
    <span id="toastMessageText">-</span>
</div>

<!-- ══ Modal 1: Kelola Akun Karyawan ══ -->
<div class="modal-backdrop" id="modalManagePengguna" role="dialog" aria-modal="true" aria-labelledby="modalManageTitle">
    <div class="modal-card">
        <div class="modal-header">
            <h2 class="modal-title" id="modalManageTitle">Kelola akun karyawan</h2>
            <button type="button" class="btn-modal-close" onclick="closeManageModal()" aria-label="Tutup modal">&times;</button>
        </div>

        <form id="formManageUser" onsubmit="submitManageUser(event)">
            <div class="modal-body">
                <!-- Error Alert inside Modal -->
                <div class="modal-alert-error" id="modalManageErrorAlert"></div>

                <!-- Top Info Box (Nama & NIK · Email read-only) -->
                <div class="modal-info-box">
                    <div class="info-box-name" id="modalInfoName">-</div>
                    <div class="info-box-sub" id="modalInfoMeta">-</div>
                </div>

                <!-- Row 1: Departemen & Status kepegawaian (2 columns) -->
                <div class="modal-grid-row">
                    <div class="form-group">
                        <label class="form-label" for="modalDeptSelect">Departemen</label>
                        <select name="department_id" id="modalDeptSelect" class="form-select-control" required>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="modalEmploymentSelect">Status kepegawaian</label>
                        <select name="employment_status_id" id="modalEmploymentSelect" class="form-select-control" required>
                            @foreach ($employmentStatuses as $empStatus)
                                <option value="{{ $empStatus->id }}">{{ $empStatus->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Row 2: Status akun & Peran (2 columns) -->
                <div class="modal-grid-row">
                    <div class="form-group">
                        <label class="form-label" for="modalStatusSelect">Status akun</label>
                        <select name="is_active" id="modalStatusSelect" class="form-select-control" required>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="modalRoleText">Peran</label>
                        <input type="text" id="modalRoleText" class="form-input-text" readonly value="Karyawan">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeManageModal()">Batal</button>
                <button type="submit" class="btn-modal-save" id="btnSubmitManageUser">
                    <span id="btnSubmitText">Simpan perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══ Modal 2: Konfirmasi Hapus Akun (Soft Delete) ══ -->
<div class="modal-backdrop" id="modalDeletePengguna" role="dialog" aria-modal="true" aria-labelledby="modalDeleteTitle">
    <div class="modal-card">
        <div class="modal-header" style="background:#fef2f2;border-bottom-color:#fecaca;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:30px;height:30px;border-radius:50%;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h2 class="modal-title" id="modalDeleteTitle" style="color:#991b1b;">Hapus Akun Karyawan</h2>
            </div>
            <button type="button" class="btn-modal-close" onclick="closeDeleteModal()" aria-label="Tutup modal">&times;</button>
        </div>

        <form id="formDeleteUser" onsubmit="submitDeleteUser(event)">
            <div class="modal-body">
                <div class="modal-alert-error" id="modalDeleteErrorAlert"></div>

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
                    <div style="font-size:11.5px;color:var(--text-muted);margin-top:5px;">
                        Alasan wajib diisi untuk keperluan rekam audit internal.
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeDeleteModal()">Batal</button>
                <button type="submit" class="btn-modal-danger-submit" id="btnConfirmDelete">
                    <span id="btnDeleteText">Hapus akun</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let currentSelectedUserId = null;

    // Toast Notification
    function showToast(message, type = 'success') {
        const toast = document.getElementById('floatingToast');
        const text = document.getElementById('toastMessageText');
        text.textContent = message;
        toast.className = 'toast-floating ' + type + ' show';

        setTimeout(() => {
            toast.classList.remove('show');
        }, 4000);
    }

    // Modal Kelola
    function openManageModal(userId) {
        const row = document.getElementById('user-row-' + userId);
        if (!row) return;

        if (row.dataset.isEmployee !== '1') {
            showToast('Hanya akun karyawan yang dapat dikelola. Akun Admin bukan target pengelolaan.', 'error');
            return;
        }

        currentSelectedUserId = userId;

        document.getElementById('modalManageErrorAlert').style.display = 'none';
        document.getElementById('modalManageErrorAlert').textContent = '';

        document.getElementById('modalInfoName').textContent = row.dataset.name;
        document.getElementById('modalInfoMeta').textContent = `${row.dataset.nik} · ${row.dataset.email}`;
        document.getElementById('modalDeptSelect').value = row.dataset.deptId;
        document.getElementById('modalEmploymentSelect').value = row.dataset.employmentId;
        document.getElementById('modalStatusSelect').value = row.dataset.isActive;

        document.getElementById('modalManagePengguna').classList.add('open');
    }

    function closeManageModal() {
        document.getElementById('modalManagePengguna').classList.remove('open');
        currentSelectedUserId = null;
    }

    // Submit Kelola Form via Fetch
    async function submitManageUser(event) {
        event.preventDefault();
        if (!currentSelectedUserId) return;

        const btn = document.getElementById('btnSubmitManageUser');
        const btnText = document.getElementById('btnSubmitText');
        const errorAlert = document.getElementById('modalManageErrorAlert');

        errorAlert.style.display = 'none';
        errorAlert.textContent = '';
        btn.disabled = true;
        btnText.textContent = 'Menyimpan...';

        const payload = {
            department_id: parseInt(document.getElementById('modalDeptSelect').value, 10),
            employment_status_id: parseInt(document.getElementById('modalEmploymentSelect').value, 10),
            is_active: parseInt(document.getElementById('modalStatusSelect').value, 10) === 1
        };

        try {
            const response = await fetch("{{ url('admin/pengguna') }}/" + currentSelectedUserId, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok) {
                let errorMsg = data.message || 'Terjadi kesalahan saat menyimpan data.';
                if (data.errors) {
                    const firstKey = Object.keys(data.errors)[0];
                    if (firstKey && data.errors[firstKey][0]) {
                        errorMsg = data.errors[firstKey][0];
                    }
                }
                errorAlert.textContent = errorMsg;
                errorAlert.style.display = 'block';
                btn.disabled = false;
                btnText.textContent = 'Simpan perubahan';
                return;
            }

            // Update row in DOM
            const row = document.getElementById('user-row-' + currentSelectedUserId);
            if (row && data.user) {
                row.dataset.deptId = data.user.department_id;
                row.dataset.employmentId = data.user.employment_status_id;
                row.dataset.isActive = data.user.is_active ? 1 : 0;

                const deptCell = row.querySelector('.row-dept');
                if (deptCell) deptCell.textContent = data.user.department_name;

                const empCell = row.querySelector('.row-employment');
                if (empCell) empCell.textContent = data.user.employment_status_name;

                const statusContainer = row.querySelector('.row-status-container');
                if (statusContainer) {
                    if (data.user.is_active) {
                        statusContainer.innerHTML = '<span class="status-badge active"><span class="status-dot"></span>Aktif</span>';
                    } else {
                        statusContainer.innerHTML = '<span class="status-badge inactive"><span class="status-dot"></span>Nonaktif</span>';
                    }
                }
            }

            // Update stats
            if (data.stats) {
                const statActive = document.getElementById('statActiveUsers');
                if (statActive) statActive.textContent = data.stats.activeUsers;
                const statTotal = document.getElementById('statTotalUsers');
                if (statTotal) statTotal.textContent = data.stats.totalUsers;
            }

            closeManageModal();
            showToast(data.message || 'Data akun karyawan berhasil diperbarui.', 'success');
        } catch (err) {
            errorAlert.textContent = 'Gagal menghubungi server: ' + err.message;
            errorAlert.style.display = 'block';
        } finally {
            btn.disabled = false;
            btnText.textContent = 'Simpan perubahan';
        }
    }

    // Modal Delete
    function openDeleteModalDirect(userId, userName) {
        currentSelectedUserId = userId;
        document.getElementById('deleteTargetName').textContent = userName;
        document.getElementById('inputDeletionReason').value = '';
        document.getElementById('modalDeleteErrorAlert').style.display = 'none';
        document.getElementById('modalDeleteErrorAlert').textContent = '';

        document.getElementById('modalDeletePengguna').classList.add('open');
    }

    function closeDeleteModal() {
        document.getElementById('modalDeletePengguna').classList.remove('open');
        currentSelectedUserId = null;
    }

    // Submit Delete Form via Fetch
    async function submitDeleteUser(event) {
        event.preventDefault();
        if (!currentSelectedUserId) return;

        const reason = document.getElementById('inputDeletionReason').value.trim();
        const btn = document.getElementById('btnConfirmDelete');
        const btnText = document.getElementById('btnDeleteText');
        const errorAlert = document.getElementById('modalDeleteErrorAlert');

        if (!reason) {
            errorAlert.textContent = 'Alasan penghapusan akun wajib diisi.';
            errorAlert.style.display = 'block';
            return;
        }

        errorAlert.style.display = 'none';
        btn.disabled = true;
        btnText.textContent = 'Menghapus...';

        try {
            const response = await fetch("{{ url('admin/pengguna') }}/" + currentSelectedUserId, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ deletion_reason: reason })
            });

            const data = await response.json();

            if (!response.ok) {
                let errorMsg = data.message || 'Terjadi kesalahan saat menghapus akun.';
                if (data.errors && data.errors.deletion_reason) {
                    errorMsg = data.errors.deletion_reason[0];
                }
                errorAlert.textContent = errorMsg;
                errorAlert.style.display = 'block';
                btn.disabled = false;
                btnText.textContent = 'Hapus akun';
                return;
            }

            // Remove row from table
            const row = document.getElementById('user-row-' + currentSelectedUserId);
            if (row) {
                row.remove();
            }

            // Update stats
            if (data.stats) {
                const statActive = document.getElementById('statActiveUsers');
                if (statActive) statActive.textContent = data.stats.activeUsers;
                const statTotal = document.getElementById('statTotalUsers');
                if (statTotal) statTotal.textContent = data.stats.totalUsers;
            }

            closeDeleteModal();
            showToast(data.message || 'Akun karyawan berhasil dihapus.', 'success');
        } catch (err) {
            errorAlert.textContent = 'Gagal menghubungi server: ' + err.message;
            errorAlert.style.display = 'block';
        } finally {
            btn.disabled = false;
            btnText.textContent = 'Hapus akun';
        }
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
