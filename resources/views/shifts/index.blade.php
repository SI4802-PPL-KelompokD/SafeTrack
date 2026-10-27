<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Kelola data shift kerja untuk operasional inspeksi harian SafeTrack.">
    <title>Kelola Shift Kerja – SafeTrack</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        /* ── Design Tokens (SafeTrack System) ── */
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
            --amber-active-bg: #fef9c3;
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

        /* ── Reset & Base ── */
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

        /* ── Layout Shell ── */
        .app-shell {
            display: flex;
            width: 100%;
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

        /* Logo SafeTrack */
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

        /* Workspace Card */
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

        /* Nav Sections */
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
        .sidebar-link.active {
            background-color: #0f2a4a;
            color: #ffffff;
            font-weight: 600;
            border-radius: var(--radius-sm);
        }
        .sidebar-link.active svg {
            color: #ffffff;
        }
        .sidebar-link.active:hover {
            background-color: #163860;
            color: #ffffff;
        }
        .sidebar-link.active:hover svg {
            color: #ffffff;
        }

        /* Sidebar User Footer */
        .sidebar-user-footer {
            margin-top: auto;
            padding: 14px 16px;
            border-top: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
        }
        .user-meta-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow: hidden;
        }
        .user-avatar-badge {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #fef3c7;
            color: #92400e;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
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
            padding: 4px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s ease;
        }
        .btn-user-exit:hover {
            color: #ef4444;
        }

        /* ── Main Canvas Content Area ── */
        .app-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Topbar ── */
        .app-topbar {
            height: var(--topbar-height);
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border-light);
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
            color: var(--text-muted);
        }
        .topbar-breadcrumbs a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .topbar-breadcrumbs a:hover {
            color: var(--text-heading);
        }
        .topbar-breadcrumbs .sep {
            color: #cbd5e1;
            font-size: 12px;
        }
        .topbar-breadcrumbs .current {
            font-weight: 600;
            color: var(--text-heading);
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
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            padding: 7px 12px;
            width: 220px;
            transition: all 0.15s ease;
        }
        .topbar-search-box:focus-within {
            border-color: var(--border-focus);
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
            color: var(--text-body);
            width: 100%;
            outline: none;
        }
        .topbar-search-box input::placeholder {
            color: #94a3b8;
        }

        /* Ganti Akun Dropdown pill */
        .account-switch-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            padding: 6px 12px;
            font-size: 12.5px;
            color: var(--text-heading);
            cursor: pointer;
            font-family: inherit;
            transition: all 0.15s ease;
        }
        .account-switch-btn:hover {
            background: #f1f5f9;
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
        }

        .topbar-icon-btn {
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 6px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: color 0.15s ease;
        }
        .topbar-icon-btn:hover {
            color: var(--text-heading);
        }
        .topbar-icon-btn svg { width: 19px; height: 19px; }

        /* ── Page Canvas Content ── */
        .content-container {
            padding: 32px 36px 48px;
            width: 100%;
        }

        /* Header Title */
        .page-header-group {
            margin-bottom: 24px;
        }
        .page-category-tag {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .page-main-title {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-heading);
            letter-spacing: -0.02em;
            line-height: 1.2;
            margin-bottom: 6px;
        }
        .page-desc-text {
            font-size: 14px;
            color: var(--text-muted);
        }

        /* ── 3 Stat Cards Row ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            padding: 22px 24px;
            box-shadow: var(--shadow-subtle);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .stat-card:hover {
            box-shadow: var(--shadow-card);
        }
        .stat-card-title {
            font-size: 13.5px;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 8px;
        }
        .stat-card-number {
            font-size: 34px;
            font-weight: 800;
            color: var(--text-heading);
            line-height: 1;
            letter-spacing: -0.02em;
        }

        /* ── Filter & Action Row ── */
        .action-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .toolbar-filters {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            max-width: 600px;
        }
        .search-input-wrap {
            position: relative;
            flex: 1;
        }
        .search-input-wrap svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            color: #94a3b8;
            pointer-events: none;
        }
        .search-input-control {
            width: 100%;
            padding: 10px 14px 10px 38px;
            font-size: 13.5px;
            font-family: inherit;
            color: var(--text-body);
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            transition: all 0.15s ease;
        }
        .search-input-control:focus {
            border-color: var(--border-focus);
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.12);
        }
        .select-filter-control {
            padding: 10px 36px 10px 14px;
            font-size: 13.5px;
            font-family: inherit;
            color: var(--text-body);
            background: var(--bg-surface) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E") no-repeat right 10px center/16px;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            appearance: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .select-filter-control:focus {
            border-color: var(--border-focus);
            outline: none;
        }

        .btn-add-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--navy-btn);
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 600;
            padding: 10px 18px;
            border-radius: var(--radius-sm);
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s ease, transform 0.1s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        .btn-add-primary:hover {
            background: var(--navy-btn-hover);
        }
        .btn-add-primary:active {
            transform: scale(0.99);
        }
        .btn-add-primary svg {
            width: 17px;
            height: 17px;
        }

        /* ── Flash Alerts ── */
        .toast-banner {
            padding: 12px 18px;
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: var(--shadow-subtle);
        }
        .toast-banner.success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .toast-banner.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .toast-banner-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .toast-banner svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }
        .toast-banner-close {
            background: none;
            border: none;
            color: currentColor;
            opacity: 0.6;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
        }
        .toast-banner-close:hover { opacity: 1; }

        /* ── Data Table Card ── */
        .table-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-subtle);
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
        .safetrack-table tbody tr:last-child td {
            border-bottom: none;
        }
        .safetrack-table tbody tr:hover {
            background-color: #fafbfc;
        }

        .shift-code-tag {
            font-family: var(--font-mono);
            font-size: 12.5px;
            font-weight: 600;
            color: #0f172a;
            background: #f1f5f9;
            padding: 4px 8px;
            border-radius: 5px;
            display: inline-block;
        }
        .shift-name-text {
            font-weight: 600;
            color: var(--text-heading);
        }

        /* Time Pill (Referencing Foto 4 Time In / Time Out) */
        .time-cell-badge {
            font-family: var(--font-mono);
            font-weight: 600;
            font-size: 13.5px;
            color: #1e293b;
        }
        .duration-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 3px 8px;
            border-radius: 9999px;
        }
        .duration-tag svg {
            width: 12px;
            height: 12px;
            color: #94a3b8;
        }

        /* Status Badge (Referencing Foto 1) */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
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
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }
        .status-badge.active .status-dot {
            background-color: var(--status-green-dot);
        }
        .status-badge.inactive .status-dot {
            background-color: var(--status-gray-dot);
        }

        /* Action Buttons */
        .action-btn-group {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
        }
        .btn-action-text {
            background: none;
            border: none;
            color: #0f2a4a;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-action-text:hover {
            background: #f1f5f9;
            color: #1e40af;
        }
        .btn-action-delete {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.15s ease;
        }
        .btn-action-delete:hover {
            color: #ef4444;
            background: #fef2f2;
        }

        /* Empty State */
        .table-empty-box {
            padding: 48px 24px;
            text-align: center;
            color: var(--text-muted);
        }
        .table-empty-box svg {
            width: 42px;
            height: 42px;
            color: #cbd5e1;
            margin-bottom: 12px;
        }
        .table-empty-box p {
            font-size: 14px;
            font-weight: 500;
        }

        /* Table Card Footer Note (Referencing Foto 1) */
        .table-card-footer {
            padding: 14px 20px;
            border-top: 1px solid var(--border-light);
            background: #fafbfc;
            font-size: 12.5px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Page Bottom Footer (Referencing Foto 1) */
        .page-global-footer {
            margin-top: 36px;
            padding-top: 18px;
            border-top: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text-light);
            flex-wrap: wrap;
            gap: 12px;
        }
        .system-status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .indicator-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: #22c55e;
        }

        /* ── Modal Dialog System (Referencing Foto 2 SafeTrack) ── */
        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            z-index: 100;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .modal-backdrop.open {
            display: flex;
            opacity: 1;
        }
        .modal-box {
            background: var(--bg-surface);
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 540px;
            box-shadow: var(--shadow-modal);
            overflow: visible;
            transform: translateY(8px);
            transition: transform 0.2s ease;
            position: relative;
        }
        .modal-backdrop.open .modal-box {
            transform: translateY(0);
        }
        .modal-header {
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-light);
        }
        .modal-header-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--text-heading);
        }
        .modal-btn-close {
            background: none;
            border: none;
            font-size: 22px;
            line-height: 1;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s ease;
        }
        .modal-btn-close:hover {
            color: var(--text-heading);
        }

        .modal-body {
            padding: 24px;
        }

        /* Top Info Box inside Modal (Referencing Foto 2) */
        .modal-info-banner {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .info-banner-primary {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--text-heading);
        }
        .info-banner-secondary {
            font-size: 12px;
            color: var(--text-muted);
        }

        .form-row-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 18px;
        }
        .form-field-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 18px;
            position: relative;
        }
        .form-row-2col .form-field-group {
            margin-bottom: 0;
        }
        .form-field-label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }
        .form-field-input {
            width: 100%;
            padding: 9px 12px;
            font-size: 13.5px;
            font-family: inherit;
            color: var(--text-body);
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            transition: all 0.15s ease;
        }
        .form-field-input:focus {
            border-color: var(--border-focus);
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.12);
        }
        .form-field-input.has-error {
            border-color: #ef4444;
            background: #fffafa;
        }
        .form-field-error-text {
            font-size: 11.5px;
            color: #ef4444;
            margin-top: 4px;
        }

        /* ── Custom Time Picker Input (Referencing Foto 3 Input Style & User Choice A1) ── */
        .time-picker-wrapper {
            position: relative;
            width: 100%;
        }
        .time-input-container {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            background: #ffffff;
            cursor: pointer;
            transition: all 0.15s ease;
            overflow: hidden;
        }
        .time-input-container:hover {
            border-color: #cbd5e1;
        }
        .time-input-container.active {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.12);
        }
        .time-text-input {
            width: 100%;
            border: none;
            background: transparent;
            padding: 9px 12px;
            font-family: var(--font-mono);
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text-heading);
            outline: none;
            cursor: pointer;
            letter-spacing: 0.05em;
        }
        .time-icon-wrapper {
            padding: 0 12px;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }
        .time-icon-wrapper svg {
            width: 17px;
            height: 17px;
        }

        /* Custom Floating Time Picker Popover (No clunky native OS dropdowns) */
        .time-popover-box {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            width: 250px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: var(--radius-md);
            box-shadow: 0 12px 28px -4px rgba(15, 23, 42, 0.18), 0 4px 10px -2px rgba(15, 23, 42, 0.08);
            z-index: 120;
            padding: 14px;
            animation: popoverFadeIn 0.15s ease forwards;
        }
        @keyframes popoverFadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .time-popover-box.open {
            display: block;
        }
        .popover-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
        }
        .popover-header span {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .popover-preview-badge {
            font-family: var(--font-mono);
            font-size: 12px;
            font-weight: 700;
            background: #eff6ff;
            color: #1d4ed8;
            padding: 2px 7px;
            border-radius: 4px;
        }
        /* Quick Preset Chips */
        .preset-row {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 12px;
        }
        .preset-chip {
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 600;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 3px 6px;
            cursor: pointer;
            color: #475569;
            transition: all 0.12s ease;
        }
        .preset-chip:hover {
            background: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
        }

        /* Jam & Menit Scrollable Picker Columns */
        .time-columns-container {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            height: 160px;
        }
        .time-column {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .time-column-label {
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            text-align: center;
            margin-bottom: 4px;
        }
        .time-column-scroll {
            flex: 1;
            overflow-y: auto;
            border: 1px solid #f1f5f9;
            background: #f8fafc;
            border-radius: 6px;
            padding: 2px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .time-col-sep {
            font-size: 18px;
            font-weight: 700;
            color: #94a3b8;
            padding-top: 50px;
        }
        .time-scroll-item {
            font-family: var(--font-mono);
            font-size: 12.5px;
            font-weight: 500;
            text-align: center;
            padding: 6px 0;
            border-radius: 4px;
            cursor: pointer;
            color: #475569;
            transition: all 0.1s ease;
        }
        .time-scroll-item:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .time-scroll-item.selected {
            background: #0f2a4a;
            color: #ffffff;
            font-weight: 700;
        }
        .popover-footer-actions {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
        }
        .btn-popover-done {
            background: #0f2a4a;
            color: #ffffff;
            font-size: 12px;
            font-weight: 600;
            border: none;
            border-radius: 4px;
            padding: 5px 12px;
            cursor: pointer;
        }
        .btn-popover-done:hover {
            background: #1e293b;
        }

        /* Modal Footer Buttons (Referencing Foto 2) */
        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            background: #fafbfc;
            border-radius: 0 0 var(--radius-lg) var(--radius-lg);
        }
        .btn-modal-cancel {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #475569;
            font-size: 13.5px;
            font-weight: 600;
            padding: 9px 18px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-modal-cancel:hover {
            background: #f1f5f9;
            color: var(--text-heading);
        }
        .btn-modal-submit {
            background: var(--navy-btn);
            border: none;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 600;
            padding: 9px 20px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: background 0.15s ease;
        }
        .btn-modal-submit:hover {
            background: var(--navy-btn-hover);
        }
        .btn-modal-danger {
            background: #dc2626;
            border: none;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 600;
            padding: 9px 20px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .btn-modal-danger:hover {
            background: #b91c1c;
        }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .app-sidebar { transform: translateX(-100%); transition: transform 0.2s ease; }
            .app-sidebar.open { transform: translateX(0); }
            .app-main { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr; }
            .content-container { padding: 20px 16px; }
            .app-topbar { padding: 0 16px; }
        }
    </style>
</head>
<body>
<div class="app-shell">

    <!-- ══ SafeTrack Sidebar (Referencing Foto 1) ══ -->
    <aside class="app-sidebar" id="sidebarNav" role="navigation" aria-label="Navigasi SafeTrack">
        <!-- Brand Logo -->
        <div class="brand-container">
            <div class="brand-logo-icon">
                <!-- SafeTrack Shield Icon -->
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

        <!-- Workspace Demonstrasi Selector -->
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
                <a href="#" class="sidebar-link">
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
        <div class="nav-section-title">Administrasi</div>
        <ul class="sidebar-menu">
            <li>
                <a href="#" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <span>Kelola Karyawan</span>
                </a>
            </li>
            <li>
                <a href="{{ route('shifts.index') }}" class="sidebar-link active">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Kelola Shift Kerja</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.work-areas.index') }}" class="sidebar-link">
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

        <!-- Bottom User Profile Card (Referencing Foto 1) -->
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

    <!-- ══ Canvas Main Content ══ -->
    <main class="app-main">

        <!-- Topbar (Referencing Foto 1) -->
        <header class="app-topbar">
            <!-- Breadcrumbs -->
            <nav class="topbar-breadcrumbs" aria-label="Breadcrumb">
                <a href="#">Workspace</a>
                <span class="sep">&gt;</span>
                <span class="current">Kelola Shift Kerja</span>
            </nav>

            <!-- Actions & User Profile -->
            <div class="topbar-actions">
                <div class="topbar-search-box">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input type="text" placeholder="Cari laporan bahaya..." aria-label="Cari laporan">
                </div>

                <div class="account-switch-btn" title="Ganti Akun">
                    <span class="account-switch-label">Ganti Akun</span>
                    <span class="account-switch-name">{{ Auth::user()->name ?? 'Admin HSE' }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </div>

                <button class="topbar-icon-btn" aria-label="Notifikasi">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                </button>

                <div class="user-avatar-badge" style="width:32px;height:32px;font-size:11px;">{{ strtoupper(substr(Auth::user()->name ?? 'AH', 0, 2)) }}</div>
            </div>
        </header>

        <!-- Page Main Canvas Area -->
        <div class="content-container">

            <!-- Title & Subtitle (Referencing Foto 1) -->
            <div class="page-header-group">
                <div class="page-category-tag">Administrasi</div>
                <h1 class="page-main-title">Kelola shift kerja</h1>
                <p class="page-desc-text">Atur jadwal kerja operasional, jam mulai, jam selesai, dan status shift karyawan.</p>
            </div>

            <!-- 3 Stat Cards (Referencing Foto 1) -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-title">Seluruh shift</div>
                    <div class="stat-card-number" id="statTotalShifts">{{ $shifts->count() }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-title">Shift aktif</div>
                    <div class="stat-card-number" id="statActiveShifts">{{ $shifts->where('is_active', true)->count() }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-title">Shift nonaktif</div>
                    <div class="stat-card-number" id="statInactiveShifts">{{ $shifts->where('is_active', false)->count() }}</div>
                </div>
            </div>

            <!-- Flash Toast Notifications -->
            @if (session('success'))
                <div class="toast-banner success" id="toastSuccess" role="alert">
                    <div class="toast-banner-content">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="toast-banner-close" onclick="this.parentElement.remove()" aria-label="Tutup notifikasi">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="toast-banner error" id="toastError" role="alert">
                    <div class="toast-banner-content">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="toast-banner-close" onclick="this.parentElement.remove()" aria-label="Tutup notifikasi">&times;</button>
                </div>
            @endif

            <!-- Action & Filter Bar (Referencing Foto 1) -->
            <div class="action-toolbar">
                <div class="toolbar-filters">
                    <div class="search-input-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <input type="text" id="filterKeyword" class="search-input-control" placeholder="Cari nama atau kode shift..." oninput="handleTableFilter()" aria-label="Cari nama atau kode shift">
                    </div>
                    <select id="filterStatus" class="select-filter-control" onchange="handleTableFilter()" aria-label="Filter status shift">
                        <option value="all">Semua status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
                <button type="button" class="btn-add-primary" onclick="openAddModal()" id="btnOpenAddModal">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Tambah Shift</span>
                </button>
            </div>

            <!-- ══ Shift Table Card (Referencing Foto 1 & Foto 4) ══ -->
            <div class="table-card">
                @if ($shifts->isEmpty())
                    <div class="table-empty-box">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p>Belum ada data shift kerja yang terdaftar.</p>
                        <p style="font-size:12.5px;color:#94a3b8;margin-top:4px;">Klik tombol "+ Tambah Shift" di atas untuk menambahkan shift baru.</p>
                    </div>
                @else
                    <table class="safetrack-table">
                        <thead>
                            <tr>
                                <th style="width: 140px;">Kode Shift</th>
                                <th>Nama Shift</th>
                                <th style="width: 130px;">Jam Mulai</th>
                                <th style="width: 130px;">Jam Selesai</th>
                                <th style="width: 140px;">Durasi</th>
                                <th style="width: 120px;">Status</th>
                                <th style="width: 120px; text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="shiftTableBody">
                            @foreach ($shifts as $shift)
                                @php
                                    $start = \Carbon\Carbon::parse($shift->starts_at);
                                    $end = \Carbon\Carbon::parse($shift->ends_at);
                                    if ($end->lessThan($start)) {
                                        $end->addDay();
                                    }
                                    $diffMinutes = $start->diffInMinutes($end);
                                    $hours = intdiv($diffMinutes, 60);
                                    $mins = $diffMinutes % 60;
                                    $durationLabel = $hours . ' jam' . ($mins > 0 ? ' ' . $mins . ' mnt' : '');
                                @endphp
                                <tr data-code="{{ strtolower($shift->code) }}" data-name="{{ strtolower($shift->name) }}" data-status="{{ $shift->is_active ? 'active' : 'inactive' }}">
                                    <td>
                                        <span class="shift-code-tag">{{ $shift->code }}</span>
                                    </td>
                                    <td>
                                        <span class="shift-name-text">{{ $shift->name }}</span>
                                    </td>
                                    <td>
                                        <span class="time-cell-badge">{{ $start->format('H:i') }}</span>
                                    </td>
                                    <td>
                                        <span class="time-cell-badge">{{ $end->format('H:i') }}</span>
                                    </td>
                                    <td>
                                        <span class="duration-tag">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $durationLabel }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($shift->is_active)
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
                                    <td>
                                        <div class="action-btn-group">
                                            <button type="button" class="btn-action-text" onclick='openEditModal(@json($shift))' aria-label="Ubah shift {{ $shift->name }}">
                                                Kelola
                                            </button>
                                            <button type="button" class="btn-action-delete" onclick="openDeleteModal({{ $shift->id }}, '{{ addslashes($shift->name) }}')" aria-label="Hapus shift {{ $shift->name }}" title="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:16px;height:16px;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="table-card-footer">
                        <span id="tableEntriesCounter">Menampilkan {{ $shifts->count() }} shift kerja terdaftar.</span>
                        <span>Jadwal operasional SafeTrack tersinkronisasi.</span>
                    </div>
                @endif
            </div>

            <!-- Page Global Footer (Referencing Foto 1) -->
            <footer class="page-global-footer">
                <div>&copy; 2026 SafeTrack. Setiap kepedulian berarti.</div>
                <div class="system-status-indicator">
                    <span class="indicator-dot"></span>
                    <span>Sistem aktif &bull; Data tersimpan otomatis</span>
                </div>
            </footer>

        </div>
    </main>
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- MODAL: Tambah Shift Kerja (Referencing Foto 2 & Foto 3)       -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="addShiftModal" role="dialog" aria-modal="true" aria-labelledby="addModalTitle">
    <div class="modal-box">
        <div class="modal-header">
            <h2 class="modal-header-title" id="addModalTitle">Tambah shift kerja</h2>
            <button type="button" class="modal-btn-close" onclick="closeModal('addShiftModal')" aria-label="Tutup">&times;</button>
        </div>
        <form method="POST" action="{{ route('shifts.store') }}" id="addShiftForm">
            @csrf
            <div class="modal-body">
                <div class="form-row-2col">
                    <div class="form-field-group">
                        <label for="add_code" class="form-field-label">Kode shift</label>
                        <input type="text" id="add_code" name="code" class="form-field-input {{ $errors->has('code') && !old('_edit_id') ? 'has-error' : '' }}" placeholder="Contoh: PAGI" value="{{ old('code') }}" required>
                        @if ($errors->has('code') && !old('_edit_id'))
                            <div class="form-field-error-text">{{ $errors->first('code') }}</div>
                        @endif
                    </div>
                    <div class="form-field-group">
                        <label for="add_name" class="form-field-label">Nama shift</label>
                        <input type="text" id="add_name" name="name" class="form-field-input {{ $errors->has('name') && !old('_edit_id') ? 'has-error' : '' }}" placeholder="Contoh: Shift Pagi 1" value="{{ old('name') }}" required>
                        @if ($errors->has('name') && !old('_edit_id'))
                            <div class="form-field-error-text">{{ $errors->first('name') }}</div>
                        @endif
                    </div>
                </div>

                <!-- Custom Time Picker Row (Foto 3 Clock Icon + Foto 4/5 Clean Picker) -->
                <div class="form-row-2col">
                    <!-- Jam Mulai -->
                    <div class="form-field-group">
                        <label class="form-field-label">Jam mulai</label>
                        <input type="hidden" name="starts_at" id="add_starts_at" value="{{ old('starts_at', '07:00') }}">
                        <div class="time-picker-wrapper">
                            <div class="time-input-container" onclick="openTimePickerPopover(event, 'add_start')">
                                <input type="text" id="add_start_display" class="time-text-input" value="{{ old('starts_at', '07:00') }}" readonly>
                                <div class="time-icon-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <!-- Floating Custom Scroll Picker -->
                            <div class="time-popover-box" id="add_start_popover">
                                <div class="popover-header">
                                    <span>Pilih Jam Mulai</span>
                                    <span class="popover-preview-badge" id="add_start_badge">{{ old('starts_at', '07:00') }}</span>
                                </div>
                                <div class="preset-row">
                                    <span class="preset-chip" onclick="applyPresetTime('add_start', '07', '00')">07:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('add_start', '08', '00')">08:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('add_start', '15', '00')">15:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('add_start', '23', '00')">23:00</span>
                                </div>
                                <div class="time-columns-container">
                                    <div class="time-column">
                                        <div class="time-column-label">JAM</div>
                                        <div class="time-column-scroll" id="add_start_h_list"></div>
                                    </div>
                                    <div class="time-col-sep">:</div>
                                    <div class="time-column">
                                        <div class="time-column-label">MENIT</div>
                                        <div class="time-column-scroll" id="add_start_m_list"></div>
                                    </div>
                                </div>
                                <div class="popover-footer-actions">
                                    <button type="button" class="btn-popover-done" onclick="closeAllTimePickers()">Selesai</button>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('starts_at') && !old('_edit_id'))
                            <div class="form-field-error-text">{{ $errors->first('starts_at') }}</div>
                        @endif
                    </div>

                    <!-- Jam Selesai -->
                    <div class="form-field-group">
                        <label class="form-field-label">Jam selesai</label>
                        <input type="hidden" name="ends_at" id="add_ends_at" value="{{ old('ends_at', '15:00') }}">
                        <div class="time-picker-wrapper">
                            <div class="time-input-container" onclick="openTimePickerPopover(event, 'add_end')">
                                <input type="text" id="add_end_display" class="time-text-input" value="{{ old('ends_at', '15:00') }}" readonly>
                                <div class="time-icon-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <!-- Floating Custom Scroll Picker -->
                            <div class="time-popover-box" id="add_end_popover">
                                <div class="popover-header">
                                    <span>Pilih Jam Selesai</span>
                                    <span class="popover-preview-badge" id="add_end_badge">{{ old('ends_at', '15:00') }}</span>
                                </div>
                                <div class="preset-row">
                                    <span class="preset-chip" onclick="applyPresetTime('add_end', '15', '00')">15:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('add_end', '16', '00')">16:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('add_end', '17', '00')">17:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('add_end', '23', '00')">23:00</span>
                                </div>
                                <div class="time-columns-container">
                                    <div class="time-column">
                                        <div class="time-column-label">JAM</div>
                                        <div class="time-column-scroll" id="add_end_h_list"></div>
                                    </div>
                                    <div class="time-col-sep">:</div>
                                    <div class="time-column">
                                        <div class="time-column-label">MENIT</div>
                                        <div class="time-column-scroll" id="add_end_m_list"></div>
                                    </div>
                                </div>
                                <div class="popover-footer-actions">
                                    <button type="button" class="btn-popover-done" onclick="closeAllTimePickers()">Selesai</button>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('ends_at') && !old('_edit_id'))
                            <div class="form-field-error-text">{{ $errors->first('ends_at') }}</div>
                        @endif
                    </div>
                </div>

                <div class="form-field-group">
                    <label for="add_is_active" class="form-field-label">Status shift</label>
                    <select id="add_is_active" name="is_active" class="select-filter-control" style="width:100%;">
                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('addShiftModal')">Batal</button>
                <button type="submit" class="btn-modal-submit">Simpan Shift</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- MODAL: Ubah Shift Kerja (Referencing Foto 2 SafeTrack Modal)   -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="editShiftModal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
    <div class="modal-box">
        <div class="modal-header">
            <h2 class="modal-header-title" id="editModalTitle">Kelola shift kerja</h2>
            <button type="button" class="modal-btn-close" onclick="closeModal('editShiftModal')" aria-label="Tutup">&times;</button>
        </div>
        <form method="POST" id="editShiftForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="_edit_id" value="1">
            <div class="modal-body">
                <!-- Info Banner (Foto 2) -->
                <div class="modal-info-banner">
                    <span class="info-banner-primary" id="editInfoShiftName">Shift Kerja</span>
                    <span class="info-banner-secondary" id="editInfoShiftCode">KODE &bull; SafeTrack Shift</span>
                </div>

                <div class="form-row-2col">
                    <div class="form-field-group">
                        <label for="edit_code" class="form-field-label">Kode shift</label>
                        <input type="text" id="edit_code" name="code" class="form-field-input" required>
                    </div>
                    <div class="form-field-group">
                        <label for="edit_name" class="form-field-label">Nama shift</label>
                        <input type="text" id="edit_name" name="name" class="form-field-input" required>
                    </div>
                </div>

                <!-- Custom Time Picker Row (Jam Mulai & Jam Selesai) -->
                <div class="form-row-2col">
                    <!-- Jam Mulai -->
                    <div class="form-field-group">
                        <label class="form-field-label">Jam mulai</label>
                        <input type="hidden" name="starts_at" id="edit_starts_at" value="07:00">
                        <div class="time-picker-wrapper">
                            <div class="time-input-container" onclick="openTimePickerPopover(event, 'edit_start')">
                                <input type="text" id="edit_start_display" class="time-text-input" value="07:00" readonly>
                                <div class="time-icon-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="time-popover-box" id="edit_start_popover">
                                <div class="popover-header">
                                    <span>Pilih Jam Mulai</span>
                                    <span class="popover-preview-badge" id="edit_start_badge">07:00</span>
                                </div>
                                <div class="preset-row">
                                    <span class="preset-chip" onclick="applyPresetTime('edit_start', '07', '00')">07:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('edit_start', '08', '00')">08:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('edit_start', '15', '00')">15:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('edit_start', '23', '00')">23:00</span>
                                </div>
                                <div class="time-columns-container">
                                    <div class="time-column">
                                        <div class="time-column-label">JAM</div>
                                        <div class="time-column-scroll" id="edit_start_h_list"></div>
                                    </div>
                                    <div class="time-col-sep">:</div>
                                    <div class="time-column">
                                        <div class="time-column-label">MENIT</div>
                                        <div class="time-column-scroll" id="edit_start_m_list"></div>
                                    </div>
                                </div>
                                <div class="popover-footer-actions">
                                    <button type="button" class="btn-popover-done" onclick="closeAllTimePickers()">Selesai</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Jam Selesai -->
                    <div class="form-field-group">
                        <label class="form-field-label">Jam selesai</label>
                        <input type="hidden" name="ends_at" id="edit_ends_at" value="15:00">
                        <div class="time-picker-wrapper">
                            <div class="time-input-container" onclick="openTimePickerPopover(event, 'edit_end')">
                                <input type="text" id="edit_end_display" class="time-text-input" value="15:00" readonly>
                                <div class="time-icon-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="time-popover-box" id="edit_end_popover">
                                <div class="popover-header">
                                    <span>Pilih Jam Selesai</span>
                                    <span class="popover-preview-badge" id="edit_end_badge">15:00</span>
                                </div>
                                <div class="preset-row">
                                    <span class="preset-chip" onclick="applyPresetTime('edit_end', '15', '00')">15:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('edit_end', '16', '00')">16:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('edit_end', '17', '00')">17:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('edit_end', '23', '00')">23:00</span>
                                </div>
                                <div class="time-columns-container">
                                    <div class="time-column">
                                        <div class="time-column-label">JAM</div>
                                        <div class="time-column-scroll" id="edit_end_h_list"></div>
                                    </div>
                                    <div class="time-col-sep">:</div>
                                    <div class="time-column">
                                        <div class="time-column-label">MENIT</div>
                                        <div class="time-column-scroll" id="edit_end_m_list"></div>
                                    </div>
                                </div>
                                <div class="popover-footer-actions">
                                    <button type="button" class="btn-popover-done" onclick="closeAllTimePickers()">Selesai</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-field-group">
                    <label for="edit_is_active" class="form-field-label">Status shift</label>
                    <select id="edit_is_active" name="is_active" class="select-filter-control" style="width:100%;">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('editShiftModal')">Batal</button>
                <button type="submit" class="btn-modal-submit">Simpan perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- MODAL: Hapus Shift Kerja                                      -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="deleteShiftModal" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
    <div class="modal-box" style="max-width: 440px;">
        <div class="modal-header">
            <h2 class="modal-header-title" id="deleteModalTitle">Hapus shift kerja</h2>
            <button type="button" class="modal-btn-close" onclick="closeModal('deleteShiftModal')" aria-label="Tutup">&times;</button>
        </div>
        <form method="POST" id="deleteShiftForm">
            @csrf
            @method('DELETE')
            <div class="modal-body">
                <p style="font-size:14px;color:var(--text-body);line-height:1.6;">
                    Apakah Anda yakin ingin menghapus shift kerja <strong id="deleteShiftNameText" style="color:var(--text-heading)"></strong>?
                </p>
                <p style="font-size:12.5px;color:var(--text-muted);margin-top:8px;">
                    Catatan: Shift yang telah digunakan dalam inspeksi harian tidak dapat dihapus demi integritas data audit K3.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('deleteShiftModal')">Batal</button>
                <button type="submit" class="btn-modal-danger">Hapus Shift</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- SCRIPT: Modals, Custom Time Pickers, Instant Search & Filters  -->
<!-- ══════════════════════════════════════════════════════════════ -->
<script>
    /* ── Modal Opening & Closing ── */
    function openModal(id) {
        document.getElementById(id).classList.add('open');
        closeAllTimePickers();
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
        closeAllTimePickers();
    }

    function openAddModal() {
        openModal('addShiftModal');
        document.getElementById('add_code').focus();
    }

    function openEditModal(shift) {
        document.getElementById('editShiftForm').action = '/shifts/' + shift.id;
        document.getElementById('edit_code').value = shift.code;
        document.getElementById('edit_name').value = shift.name;
        document.getElementById('editInfoShiftName').textContent = shift.name;
        document.getElementById('editInfoShiftCode').textContent = shift.code + ' • Shift Kerja SafeTrack';
        document.getElementById('edit_is_active').value = shift.is_active ? '1' : '0';

        var sTime = shift.starts_at.substring(0, 5);
        var eTime = shift.ends_at.substring(0, 5);
        var sHour = sTime.substring(0, 2), sMin = sTime.substring(3, 5);
        var eHour = eTime.substring(0, 2), eMin = eTime.substring(3, 5);

        applyPresetTime('edit_start', sHour, sMin);
        applyPresetTime('edit_end', eHour, eMin);

        openModal('editShiftModal');
        document.getElementById('edit_code').focus();
    }

    function openDeleteModal(id, name) {
        document.getElementById('deleteShiftForm').action = '/shifts/' + id;
        document.getElementById('deleteShiftNameText').textContent = '"' + name + '"';
        openModal('deleteShiftModal');
    }

    /* Close modal on backdrop click */
    document.querySelectorAll('.modal-backdrop').forEach(function(modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });

    /* Close on Escape key */
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.open').forEach(function(m) {
                closeModal(m.id);
            });
            closeAllTimePickers();
        }
    });

    /* ── Custom Time Picker Core Logic ── */
    var timePickerPickers = ['add_start', 'add_end', 'edit_start', 'edit_end'];
    var minuteSteps = ['00', '15', '30', '45'];

    function initTimePickers() {
        timePickerPickers.forEach(function(pfx) {
            var hScroll = document.getElementById(pfx + '_h_list');
            var mScroll = document.getElementById(pfx + '_m_list');
            if (!hScroll || !mScroll) return;

            hScroll.innerHTML = '';
            mScroll.innerHTML = '';

            // Generate 00 to 23 Hours
            for (var h = 0; h < 24; h++) {
                var hh = String(h).padStart(2, '0');
                var hItem = document.createElement('div');
                hItem.className = 'time-scroll-item';
                hItem.dataset.hour = hh;
                hItem.textContent = hh;
                hItem.onclick = (function(prefix, val) {
                    return function(ev) {
                        ev.stopPropagation();
                        selectHour(prefix, val);
                    };
                })(pfx, hh);
                hScroll.appendChild(hItem);
            }

            // Generate 00, 15, 30, 45 Minutes
            minuteSteps.forEach(function(mm) {
                var mItem = document.createElement('div');
                mItem.className = 'time-scroll-item';
                mItem.dataset.minute = mm;
                mItem.textContent = mm;
                mItem.onclick = (function(prefix, val) {
                    return function(ev) {
                        ev.stopPropagation();
                        selectMinute(prefix, val);
                    };
                })(pfx, mm);
                mScroll.appendChild(mItem);
            });

            // Set initial selected highlight
            var curVal = document.getElementById(pfx + '_display').value || '07:00';
            var curH = curVal.substring(0, 2);
            var curM = curVal.substring(3, 5);
            applyPresetTime(pfx, curH, curM);
        });
    }

    function openTimePickerPopover(e, pfx) {
        e.stopPropagation();
        var popover = document.getElementById(pfx + '_popover');
        var wasOpen = popover.classList.contains('open');
        closeAllTimePickers();
        if (!wasOpen) {
            popover.classList.add('open');
            // Scroll selected hour into view smoothly
            var activeH = popover.querySelector('.time-column-scroll .time-scroll-item.selected');
            if (activeH) {
                activeH.scrollIntoView({ block: 'nearest' });
            }
        }
    }

    function closeAllTimePickers() {
        document.querySelectorAll('.time-popover-box.open').forEach(function(pop) {
            pop.classList.remove('open');
        });
    }

    // Close picker when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.time-picker-wrapper')) {
            closeAllTimePickers();
        }
    });

    function selectHour(pfx, h) {
        var hScroll = document.getElementById(pfx + '_h_list');
        hScroll.querySelectorAll('.time-scroll-item').forEach(function(item) {
            item.classList.toggle('selected', item.dataset.hour === h);
        });
        updateTimeCombinedValue(pfx);
    }

    function selectMinute(pfx, m) {
        var mScroll = document.getElementById(pfx + '_m_list');
        mScroll.querySelectorAll('.time-scroll-item').forEach(function(item) {
            item.classList.toggle('selected', item.dataset.minute === m);
        });
        updateTimeCombinedValue(pfx);
    }

    function updateTimeCombinedValue(pfx) {
        var hSelected = document.querySelector('#' + pfx + '_h_list .time-scroll-item.selected');
        var mSelected = document.querySelector('#' + pfx + '_m_list .time-scroll-item.selected');
        var h = hSelected ? hSelected.dataset.hour : '07';
        var m = mSelected ? mSelected.dataset.minute : '00';
        var combined = h + ':' + m;

        document.getElementById(pfx + '_display').value = combined;
        document.getElementById(pfx + '_badge').textContent = combined;

        // Sync with actual hidden form input (starts_at / ends_at)
        var formField = (pfx.indexOf('start') !== -1) ? 'starts_at' : 'ends_at';
        var scope = (pfx.indexOf('add') !== -1) ? 'add' : 'edit';
        document.getElementById(scope + '_' + formField).value = combined;
    }

    function applyPresetTime(pfx, h, m) {
        var hScroll = document.getElementById(pfx + '_h_list');
        var mScroll = document.getElementById(pfx + '_m_list');
        if (!hScroll || !mScroll) return;

        hScroll.querySelectorAll('.time-scroll-item').forEach(function(item) {
            item.classList.toggle('selected', item.dataset.hour === h);
        });
        mScroll.querySelectorAll('.time-scroll-item').forEach(function(item) {
            item.classList.toggle('selected', item.dataset.minute === m);
        });
        updateTimeCombinedValue(pfx);
    }

    /* ── Live Client-side Table Search & Filter ── */
    function handleTableFilter() {
        var query = document.getElementById('filterKeyword').value.toLowerCase().trim();
        var statusFilter = document.getElementById('filterStatus').value;
        var rows = document.querySelectorAll('#shiftTableBody tr');
        var visibleCount = 0;

        rows.forEach(function(row) {
            var code = row.getAttribute('data-code') || '';
            var name = row.getAttribute('data-name') || '';
            var status = row.getAttribute('data-status') || '';

            var matchesKeyword = (query === '') || (code.indexOf(query) !== -1) || (name.indexOf(query) !== -1);
            var matchesStatus = (statusFilter === 'all') || (status === statusFilter);

            if (matchesKeyword && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        var counter = document.getElementById('tableEntriesCounter');
        if (counter) {
            counter.textContent = 'Menampilkan ' + visibleCount + ' shift kerja terdaftar.';
        }
    }

    // Initialize custom time pickers on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        initTimePickers();

        @if ($errors->any() && !old('_edit_id'))
            openAddModal();
        @endif
    });
</script>
</body>
</html>
