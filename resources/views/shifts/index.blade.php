<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Data master SafeTrack - Kelola shift kerja, departemen, kategori temuan, dan area pertanyaan keselamatan.">
    <title>Data Master – SafeTrack</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        :root {
            --font-main: 'Inter', system-ui, -apple-system, sans-serif;
            --bg-page: #f8fafc;
            --bg-surface: #ffffff;
            --bg-sidebar: #ffffff;
            --border-light: #e2e8f0;
            --border-dark: #cbd5e1;
            --border-focus: #2563eb;
            --navy-dark: #0f2a4a;
            --navy-deep: #0a1c32;
            --navy-pill: #102a45;
            --navy-btn-hover: #163860;
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --sidebar-width: 260px;
            --topbar-height: 64px;
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 14px;
            --shadow-subtle: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.04);
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
            letter-spacing: 0.1em;
            color: var(--text-muted);
        }

        /* Workspace Switcher Card */
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
            width: 28px;
            height: 28px;
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
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
            width: 17px;
            height: 17px;
            flex-shrink: 0;
            color: #64748b;
        }
        .sidebar-link:hover {
            background-color: #f1f5f9;
            color: var(--text-heading);
        }
        .sidebar-link:hover svg { color: var(--text-heading); }
        .sidebar-link.active {
            background-color: #0f2a4a;
            color: #ffffff;
            font-weight: 600;
        }
        .sidebar-link.active svg { color: #ffffff; }
        .sidebar-link.active:hover {
            background-color: #163860;
            color: #ffffff;
        }
        .sidebar-link.active:hover svg { color: #ffffff; }

        /* Sidebar Footer Profile */
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
            background: #e2e8f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
            color: #334155;
            flex-shrink: 0;
        }
        .user-name-title { display: flex; flex-direction: column; overflow: hidden; }
        .user-full-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-heading);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .user-role-label { font-size: 11.5px; color: var(--text-muted); }
        .btn-user-exit {
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 6px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s ease;
        }
        .btn-user-exit:hover { color: #ef4444; }

        /* ── Canvas Main ── */
        .app-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: var(--bg-page);
        }

        /* Topbar */
        .app-topbar {
            height: var(--topbar-height);
            background: #ffffff;
            border-bottom: 1px solid var(--border-light);
            padding: 0 36px;
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
            font-size: 13px;
            color: var(--text-muted);
        }
        .topbar-breadcrumbs a { color: var(--text-muted); text-decoration: none; }
        .topbar-breadcrumbs a:hover { color: var(--text-heading); }
        .topbar-breadcrumbs .sep { color: #cbd5e1; font-size: 12px; }
        .topbar-breadcrumbs .current { color: var(--text-heading); font-weight: 500; }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .topbar-search-box {
            position: relative;
            width: 240px;
        }
        .topbar-search-box svg {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            width: 15px;
            height: 15px;
            color: #94a3b8;
        }
        .topbar-search-box input {
            width: 100%;
            height: 34px;
            padding: 0 12px 0 34px;
            background-color: #f1f5f9;
            border: 1px solid transparent;
            border-radius: 20px;
            font-size: 12.5px;
            color: var(--text-heading);
            outline: none;
            transition: all 0.15s ease;
        }
        .topbar-search-box input:focus {
            background-color: #ffffff;
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .account-switch-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            padding: 5px 10px;
            font-size: 12px;
            cursor: pointer;
        }
        .account-switch-label { color: #94a3b8; font-weight: 600; text-transform: uppercase; font-size: 10px; }
        .account-switch-name { color: var(--text-heading); font-weight: 600; }

        /* ── Content Container (100% Fluid Full Stretch) ── */
        .content-container {
            padding: 32px 36px 48px;
            width: 100%;
            flex: 1;
        }

        /* Header Title & Button */
        .page-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
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

        .btn-add-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 40px;
            padding: 0 18px;
            background-color: var(--navy-dark);
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 600;
            border-radius: var(--radius-md);
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(15, 42, 74, 0.15);
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .btn-add-primary:hover {
            background-color: var(--navy-btn-hover);
            transform: translateY(-1px);
        }
        .btn-add-primary svg { width: 16px; height: 16px; }

        /* ── Sub-tabs Nav Pills ── */
        .master-tabs-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .tab-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            font-weight: 500;
            color: #475569;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .tab-pill:hover {
            border-color: var(--border-dark);
            color: var(--text-heading);
        }
        .tab-pill.active {
            background-color: var(--navy-pill);
            border-color: var(--navy-pill);
            color: #ffffff;
            font-weight: 600;
        }
        .tab-pill-badge {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
        }
        .tab-pill.active .tab-pill-badge {
            color: #94a3b8;
        }

        /* ── Alert Toasts ── */
        .toast-banner {
            padding: 12px 18px;
            border-radius: var(--radius-md);
            font-size: 13.5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .toast-banner.success {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        .toast-banner.error {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .toast-banner-content { display: flex; align-items: center; gap: 10px; }
        .toast-banner-content svg { width: 18px; height: 18px; flex-shrink: 0; }
        .toast-banner-close {
            background: none;
            border: none;
            font-size: 18px;
            line-height: 1;
            color: inherit;
            cursor: pointer;
            opacity: 0.7;
        }
        .toast-banner-close:hover { opacity: 1; }

        /* ── Master Card ── */
        .master-card {
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-subtle);
        }
        .master-card-header {
            padding: 24px 28px 16px;
            display: flex;
            align-items: baseline;
            justify-content: space-between;
        }
        .card-title-group h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-heading);
            margin-bottom: 2px;
        }
        .card-title-group p {
            font-size: 13px;
            color: var(--text-muted);
        }

        /* ── Clean Table ── */
        .master-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .master-table th {
            padding: 14px 28px;
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: capitalize;
            border-bottom: 1px solid var(--border-light);
            background-color: #ffffff;
        }
        .master-table td {
            padding: 20px 28px;
            font-size: 13.5px;
            color: var(--text-body);
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .master-table tbody tr:hover td {
            background-color: #fafbfc;
        }

        .shift-title-block {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .shift-primary-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-heading);
        }
        .shift-code-sub {
            font-size: 12px;
            color: #94a3b8;
            font-family: var(--font-main);
        }

        .shift-time-text {
            font-size: 13.5px;
            font-weight: 500;
            color: #334155;
        }

        .ref-status-text {
            font-size: 13px;
            color: #64748b;
        }
        .ref-status-text.active {
            color: #0284c7;
            font-weight: 500;
        }

        .table-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 20px;
        }
        .btn-table-action {
            background: none;
            border: none;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            padding: 0;
            transition: color 0.15s ease;
        }
        .btn-table-action.edit {
            color: #0f172a;
        }
        .btn-table-action.edit:hover {
            color: #2563eb;
            text-decoration: underline;
        }
        .btn-table-action.delete {
            color: #b91c1c;
        }
        .btn-table-action.delete:hover {
            color: #7f1d1d;
            text-decoration: underline;
        }

        /* Card Bottom Note */
        .card-bottom-note {
            padding: 18px 28px;
            font-size: 12.5px;
            color: #94a3b8;
            background: #ffffff;
            border-top: 1px solid #f1f5f9;
        }

        /* ── Page Footer ── */
        .page-sys-footer {
            margin-top: 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: #94a3b8;
        }
        .sys-live-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .dot-live {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: #f59e0b;
        }

        /* ── Modal Popups (Matching Foto 2 Clean Style) ── */
        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(3px);
            z-index: 100;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-backdrop.open { display: flex; }

        .modal-card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 520px;
            box-shadow: var(--shadow-modal);
            overflow: visible;
            animation: modalFadeIn 0.2s ease-out;
            position: relative;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.97) translateY(-8px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-header {
            padding: 24px 28px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--text-heading);
        }
        .modal-close-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 1px solid var(--border-light);
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .modal-close-circle:hover {
            background-color: #f1f5f9;
            color: var(--text-heading);
            border-color: var(--border-dark);
        }
        .modal-close-circle svg { width: 16px; height: 16px; }

        .modal-body {
            padding: 0 28px 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }
        .form-group-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-heading);
            margin-bottom: 8px;
        }
        .form-text-input {
            width: 100%;
            height: 44px;
            padding: 0 14px;
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            font-size: 14px;
            font-family: inherit;
            color: var(--text-heading);
            outline: none;
            transition: all 0.15s ease;
        }
        .form-text-input:focus {
            border-color: var(--navy-dark);
            box-shadow: 0 0 0 3px rgba(15, 42, 74, 0.08);
        }
        .form-text-input.has-error {
            border-color: #ef4444;
            background-color: #fffafb;
        }
        .form-error-msg {
            font-size: 12px;
            color: #ef4444;
            margin-top: 5px;
        }

        .form-row-time {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 8px;
        }

        .time-helper-note {
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .modal-footer-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 12px;
        }
        .btn-modal-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 40px;
            padding: 0 20px;
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            font-size: 13.5px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .btn-modal-cancel:hover {
            background-color: #f8fafc;
            border-color: var(--border-dark);
            color: var(--text-heading);
        }
        .btn-modal-save {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 40px;
            padding: 0 24px;
            background-color: var(--navy-dark);
            border: none;
            border-radius: var(--radius-md);
            font-size: 13.5px;
            font-weight: 600;
            color: #ffffff;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .btn-modal-save:hover {
            background-color: var(--navy-btn-hover);
        }

        /* ── PRESERVED CUSTOM TIME PICKER POPOVER ── */
        .time-picker-wrapper {
            position: relative;
        }
        .time-input-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 44px;
            padding: 0 14px;
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .time-input-box:hover {
            border-color: var(--border-dark);
        }
        .time-val-display {
            font-size: 14.5px;
            font-weight: 700;
            color: var(--text-heading);
            border: none;
            background: transparent;
            outline: none;
            cursor: pointer;
            width: 80px;
        }
        .time-clock-icon {
            color: #94a3b8;
            display: flex;
            align-items: center;
        }
        .time-clock-icon svg { width: 18px; height: 18px; }

        /* Floating Custom Popover */
        .time-popover-box {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            width: 260px;
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            box-shadow: 0 12px 24px -4px rgba(15, 23, 42, 0.15), 0 4px 6px -2px rgba(15, 23, 42, 0.05);
            z-index: 200;
            padding: 14px 14px 12px;
            animation: modalFadeIn 0.15s ease-out;
        }
        .time-popover-box.open { display: block; }

        .popover-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        .popover-preview-badge {
            background-color: #f1f5f9;
            color: var(--navy-dark);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 800;
        }

        .preset-row {
            display: flex;
            gap: 6px;
            margin-bottom: 12px;
        }
        .preset-chip {
            flex: 1;
            padding: 4px 0;
            text-align: center;
            background: #f8fafc;
            border: 1px solid var(--border-light);
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.1s ease;
        }
        .preset-chip:hover {
            background: var(--navy-dark);
            border-color: var(--navy-dark);
            color: #ffffff;
        }

        .time-columns-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: var(--radius-sm);
            padding: 8px 12px;
            margin-bottom: 12px;
        }
        .time-column {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .time-column-label {
            font-size: 10px;
            font-weight: 700;
            color: #94a3b8;
            margin-bottom: 6px;
            letter-spacing: 0.05em;
        }
        .time-col-sep {
            font-size: 18px;
            font-weight: 700;
            color: #cbd5e1;
            padding: 0 6px;
        }
        .time-column-scroll {
            width: 100%;
            height: 140px;
            overflow-y: auto;
            scroll-behavior: smooth;
            padding: 2px;
        }
        .time-column-scroll::-webkit-scrollbar { width: 3px; }
        .time-column-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }

        .time-scroll-item {
            padding: 6px 0;
            text-align: center;
            font-size: 13.5px;
            font-weight: 600;
            color: #64748b;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.1s ease;
            margin-bottom: 2px;
        }
        .time-scroll-item:hover {
            background-color: #e2e8f0;
            color: var(--text-heading);
        }
        .time-scroll-item.selected {
            background-color: var(--navy-dark) !important;
            color: #ffffff !important;
            font-weight: 700;
        }

        .popover-footer-actions {
            display: flex;
            justify-content: flex-end;
            padding-top: 4px;
        }
        .btn-popover-done {
            padding: 5px 14px;
            background-color: var(--navy-dark);
            color: #ffffff;
            border: none;
            border-radius: 4px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .btn-popover-done:hover {
            background-color: var(--navy-btn-hover);
        }

        @media (max-width: 900px) {
            .app-sidebar { display: none; }
            .app-main { margin-left: 0; }
            .content-container { padding: 20px 16px 40px; }
            .app-topbar { padding: 0 16px; }
            .page-header-row { flex-direction: column; gap: 16px; }
            .btn-add-primary { width: 100%; justify-content: center; }
            .master-table th, .master-table td { padding: 14px 16px; }
        }
    </style>
</head>
<body>
<div class="app-shell">

    <!-- ══ Sidebar SafeTrack ══ -->
    <aside class="app-sidebar" aria-label="Navigasi Utama">
        <div class="brand-container">
            <div class="brand-logo-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-3zm0 2.18l7 2.33v4.49c0 4.54-3.07 8.79-7 9.9-3.93-1.11-7-5.36-7-9.9V6.51l7-2.33z" />
                    <circle cx="12" cy="11.5" r="3.2" fill="#f59e0b" />
                </svg>
            </div>
            <div class="brand-text">
                <span class="brand-title">safe<span>Track</span></span>
                <span class="brand-subtitle">SAFETY MANAGEMENT</span>
            </div>
        </div>

        <div class="workspace-card">
            <div class="workspace-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
            </div>
            <div class="workspace-info">
                <span class="workspace-name">PT Sentra Industri</span>
                <span class="workspace-desc">Workspace demonstrasi</span>
            </div>
        </div>

        <!-- Section Menu Utama -->
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Inspeksi Harian</span>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                    </svg>
                    <span>Safety Talk</span>
                </a>
            </li>
            <li>
                <a href="{{ route('sap.index') }}" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" />
                        <circle cx="12" cy="12" r="5" stroke="currentColor" />
                        <circle cx="12" cy="12" r="1.5" fill="currentColor" />
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

        <!-- Section Administrasi -->
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
                <!-- MENU UTAMA DATA MASTER (ACTIVE) -->
                <a href="{{ route('shifts.index') }}" class="sidebar-link active">
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

        <!-- Bottom User Profile Footer -->
        <div class="sidebar-user-footer">
            <div class="user-meta-wrap">
                <div class="user-avatar-badge">{{ strtoupper(substr(Auth::user()->name ?? 'AH', 0, 2)) }}</div>
                <div class="user-name-title">
                    <span class="user-full-name">{{ Auth::user()->name ?? 'Admin HSE' }}</span>
                    <span class="user-role-label">
                        {{ (Auth::user() && Auth::user()->role === 'super_admin') ? 'Super Admin' : 'Admin' }}
                    </span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn-user-exit" title="Keluar / Logout">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:17px;height:17px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- ══ Canvas Main ══ -->
    <main class="app-main">

        <!-- Topbar -->
        <header class="app-topbar">
            <nav class="topbar-breadcrumbs" aria-label="Breadcrumb">
                <a href="#">Workspace</a>
                <span class="sep">&gt;</span>
                <span class="current">Data Master</span>
            </nav>

            <div class="topbar-actions">
                <div class="topbar-search-box">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input type="text" placeholder="Cari laporan bahaya..." aria-label="Cari laporan">
                </div>

                <div class="account-switch-btn" title="Ganti Akun">
                    <span class="account-switch-label">Ganti Akun</span>
                    <span class="account-switch-name">{{ Auth::user()->name ?? 'Admin HSE' }} - Admin</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </div>

                <button class="btn-user-exit" aria-label="Notifikasi" style="padding:4px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:19px;height:19px;color:#64748b;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                </button>

                <div class="user-avatar-badge" style="width:32px;height:32px;font-size:11px;">
                    {{ strtoupper(substr(Auth::user()->name ?? 'AH', 0, 2)) }}
                </div>
            </div>
        </header>

        <!-- Canvas Container -->
        <div class="content-container">

            <!-- Title Header Row (Foto 1) -->
            <div class="page-header-row">
                <div class="page-header-text">
                    <div class="page-category-tag">Administrasi</div>
                    <h1 class="page-main-title">Data master</h1>
                    <p class="page-desc-text">Satu referensi data untuk aktivitas keselamatan yang konsisten.</p>
                </div>
                <button type="button" class="btn-add-primary" onclick="openAddModal()" id="btnTambahShift">
                    + Tambah shift
                </button>
            </div>

            <!-- 4 Sub-Tabs Nav Pills (Foto 1) -->
            <div class="master-tabs-bar">
                <button type="button" class="tab-pill" onclick="alert('Modul Departemen dalam integrasi Data Master.')">
                    <span>Departemen</span>
                    <span class="tab-pill-badge">{{ $tabCounts['departments'] ?? 2 }}</span>
                </button>
                <button type="button" class="tab-pill active">
                    <span>Shift kerja</span>
                    <span class="tab-pill-badge">{{ $shifts->count() }}</span>
                </button>
                <button type="button" class="tab-pill" onclick="alert('Modul Kategori Temuan dalam integrasi Data Master.')">
                    <span>Kategori temuan</span>
                    <span class="tab-pill-badge">{{ $tabCounts['hazard_categories'] ?? 2 }}</span>
                </button>
                <a href="{{ route('admin.work-areas.index') }}" class="tab-pill" style="text-decoration:none;">
                    <span>Area &amp; pertanyaan</span>
                    <span class="tab-pill-badge">{{ $tabCounts['work_areas'] ?? 16 }}</span>
                </a>
            </div>

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="toast-banner success" role="alert">
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
                <div class="toast-banner error" role="alert">
                    <div class="toast-banner-content">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="toast-banner-close" onclick="this.parentElement.remove()" aria-label="Tutup notifikasi">&times;</button>
                </div>
            @endif

            <!-- Master Card Table (Foto 1) -->
            <div class="master-card">
                <div class="master-card-header">
                    <div class="card-title-group">
                        <h2>Shift kerja</h2>
                        <p>{{ $shifts->count() }} data tersedia</p>
                    </div>
                </div>

                <table class="master-table">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Nama</th>
                            <th style="width: 30%;">Jam kerja</th>
                            <th style="width: 20%;">Referensi</th>
                            <th style="width: 15%; text-align: right;"></th>
                        </tr>
                    </thead>
                    <tbody id="shiftTableBody">
                        @forelse ($shifts as $shift)
                            <tr>
                                <td>
                                    <div class="shift-title-block">
                                        <span class="shift-primary-name">{{ $shift->name }}</span>
                                        <span class="shift-code-sub">{{ $shift->code }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="shift-time-text">
                                        {{ substr($shift->starts_at, 0, 5) }} &ndash; {{ substr($shift->ends_at, 0, 5) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($shift->daily_inspections_count > 0)
                                        <span class="ref-status-text active">
                                            Digunakan ({{ $shift->daily_inspections_count }} inspeksi)
                                        </span>
                                    @else
                                        <span class="ref-status-text">Belum digunakan</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button" class="btn-table-action edit" onclick="openEditModal({{ json_encode($shift) }})">Ubah</button>
                                        <button type="button" class="btn-table-action delete" onclick="confirmDeleteShift({{ $shift->id }}, '{{ addslashes($shift->name) }}', {{ $shift->daily_inspections_count }})">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align:center; padding: 48px; color: #94a3b8;">
                                    Belum ada data shift kerja. Klik "+ Tambah shift" untuk menambahkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="card-bottom-note">
                    Data yang sudah dirujuk aktivitas tidak dapat dihapus atau diganti namanya agar riwayat tetap konsisten.
                </div>
            </div>

            <!-- Page System Footer -->
            <footer class="page-sys-footer">
                <span>&copy; 2026 SafeTrack. Setiap kepedulian berarti.</span>
                <span class="sys-live-indicator">
                    <span class="dot-live"></span>
                    Sistem aktif &bull; Data tersimpan otomatis
                </span>
            </footer>

        </div>
    </main>

</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- MODAL: Tambah Shift (Matching Foto 2 dengan Time Picker Foto) -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="addShiftModal" role="dialog" aria-modal="true" aria-labelledby="addModalTitle">
    <div class="modal-card">
        <div class="modal-header">
            <h2 class="modal-title" id="addModalTitle">Tambah shift</h2>
            <button type="button" class="modal-close-circle" onclick="closeModal('addShiftModal')" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('shifts.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="add_name" class="form-group-label">Nama shift</label>
                    <input type="text" id="add_name" name="name" class="form-text-input {{ $errors->has('name') && !old('_edit_id') ? 'has-error' : '' }}" placeholder="Masukkan nama shift..." value="{{ old('name') }}" required autofocus>
                    @if ($errors->has('name') && !old('_edit_id'))
                        <div class="form-error-msg">{{ $errors->first('name') }}</div>
                    @endif
                </div>

                <div class="form-row-time">
                    <!-- Jam Mulai -->
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-group-label">Jam mulai</label>
                        <input type="hidden" name="starts_at" id="add_starts_at" value="{{ old('starts_at', '07:00') }}">
                        <div class="time-picker-wrapper">
                            <div class="time-input-box" onclick="openTimePickerPopover(event, 'add_start')">
                                <input type="text" id="add_start_display" class="time-val-display" value="{{ old('starts_at', '07:00') }}" readonly>
                                <div class="time-clock-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <!-- Popover Pemilih Jam Sesuai Foto -->
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
                            <div class="form-error-msg">{{ $errors->first('starts_at') }}</div>
                        @endif
                    </div>

                    <!-- Jam Selesai -->
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-group-label">Jam selesai</label>
                        <input type="hidden" name="ends_at" id="add_ends_at" value="{{ old('ends_at', '15:00') }}">
                        <div class="time-picker-wrapper">
                            <div class="time-input-box" onclick="openTimePickerPopover(event, 'add_end')">
                                <input type="text" id="add_end_display" class="time-val-display" value="{{ old('ends_at', '15:00') }}" readonly>
                                <div class="time-clock-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <!-- Popover Pemilih Jam Selesai Sesuai Foto -->
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
                            <div class="form-error-msg">{{ $errors->first('ends_at') }}</div>
                        @endif
                    </div>
                </div>

                <div class="time-helper-note">
                    Jam selesai yang lebih awal dari jam mulai berarti shift melewati tengah malam.
                </div>

                <div class="modal-footer-actions">
                    <button type="button" class="btn-modal-cancel" onclick="closeModal('addShiftModal')">Batal</button>
                    <button type="submit" class="btn-modal-save">Simpan data</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- MODAL: Ubah Shift (Matching Foto 2 dengan Time Picker)        -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="editShiftModal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
    <div class="modal-card">
        <div class="modal-header">
            <h2 class="modal-title" id="editModalTitle">Ubah shift</h2>
            <button type="button" class="modal-close-circle" onclick="closeModal('editShiftModal')" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" id="editShiftForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="_edit_id" value="1">
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_name" class="form-group-label">Nama shift</label>
                    <input type="text" id="edit_name" name="name" class="form-text-input" required>
                    <div id="edit_name_warning" style="display:none; font-size:12px; color:#d97706; margin-top:4px;">
                        Nama shift terkunci karena sudah dirujuk inspeksi harian.
                    </div>
                </div>

                <div class="form-row-time">
                    <!-- Jam Mulai -->
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-group-label">Jam mulai</label>
                        <input type="hidden" name="starts_at" id="edit_starts_at" value="07:00">
                        <div class="time-picker-wrapper">
                            <div class="time-input-box" onclick="openTimePickerPopover(event, 'edit_start')">
                                <input type="text" id="edit_start_display" class="time-val-display" value="07:00" readonly>
                                <div class="time-clock-icon">
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
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-group-label">Jam selesai</label>
                        <input type="hidden" name="ends_at" id="edit_ends_at" value="15:00">
                        <div class="time-picker-wrapper">
                            <div class="time-input-box" onclick="openTimePickerPopover(event, 'edit_end')">
                                <input type="text" id="edit_end_display" class="time-val-display" value="15:00" readonly>
                                <div class="time-clock-icon">
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

                <div class="time-helper-note">
                    Jam selesai yang lebih awal dari jam mulai berarti shift melewati tengah malam.
                </div>

                <div class="modal-footer-actions">
                    <button type="button" class="btn-modal-cancel" onclick="closeModal('editShiftModal')">Batal</button>
                    <button type="submit" class="btn-modal-save">Simpan data</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- MODAL: Hapus Shift Konfirmasi                                -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="deleteShiftModal" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
    <div class="modal-card" style="max-width: 440px;">
        <div class="modal-header">
            <h2 class="modal-title" id="deleteModalTitle">Hapus shift kerja</h2>
            <button type="button" class="modal-close-circle" onclick="closeModal('deleteShiftModal')" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" id="deleteShiftForm">
            @csrf
            @method('DELETE')
            <div class="modal-body">
                <div id="deleteWarningMessage" style="font-size:13.5px; color:#475569; line-height:1.5;">
                    Apakah Anda yakin ingin menghapus shift kerja ini? Tindakan ini tidak dapat dibatalkan.
                </div>
                <div class="modal-footer-actions" style="margin-top:20px;">
                    <button type="button" class="btn-modal-cancel" onclick="closeModal('deleteShiftModal')" id="btnCancelDelete">Batal</button>
                    <button type="submit" class="btn-modal-save" id="btnConfirmDelete" style="background-color:#b91c1c;">Hapus shift</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.add('open');
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
    }
    function openAddModal() {
        openModal('addShiftModal');
        setTimeout(function() {
            var input = document.getElementById('add_name');
            if (input) input.focus();
        }, 100);
    }

    function openEditModal(shift) {
        var form = document.getElementById('editShiftForm');
        form.action = '/shifts/' + shift.id;
        document.getElementById('edit_name').value = shift.name;

        var startVal = (shift.starts_at || '07:00').substring(0, 5);
        var endVal = (shift.ends_at || '15:00').substring(0, 5);

        document.getElementById('edit_starts_at').value = startVal;
        document.getElementById('edit_start_display').value = startVal;
        document.getElementById('edit_start_badge').textContent = startVal;

        document.getElementById('edit_ends_at').value = endVal;
        document.getElementById('edit_end_display').value = endVal;
        document.getElementById('edit_end_badge').textContent = endVal;

        applyPresetTime('edit_start', startVal.substring(0, 2), startVal.substring(3, 5));
        applyPresetTime('edit_end', endVal.substring(0, 2), endVal.substring(3, 5));

        // Lock name if referenced
        var warning = document.getElementById('edit_name_warning');
        var nameInput = document.getElementById('edit_name');
        if (shift.daily_inspections_count > 0) {
            warning.style.display = 'block';
            nameInput.readOnly = true;
            nameInput.style.backgroundColor = '#f8fafc';
        } else {
            warning.style.display = 'none';
            nameInput.readOnly = false;
            nameInput.style.backgroundColor = '#ffffff';
        }

        openModal('editShiftModal');
    }

    function confirmDeleteShift(id, name, inspectionsCount) {
        var form = document.getElementById('deleteShiftForm');
        var msg = document.getElementById('deleteWarningMessage');
        var confirmBtn = document.getElementById('btnConfirmDelete');
        var cancelBtn = document.getElementById('btnCancelDelete');

        if (inspectionsCount > 0) {
            form.action = '#';
            form.onsubmit = function(e) { e.preventDefault(); closeModal('deleteShiftModal'); };
            msg.innerHTML = '<span style="color:#ef4444; font-weight:700;">Tidak dapat dihapus!</span><br>Shift <strong>' + name + '</strong> sudah dirujuk dalam ' + inspectionsCount + ' inspeksi harian. Sesuai regulasi data keselamatan, data yang telah dirujuk tidak dapat dihapus.';
            confirmBtn.style.display = 'none';
            cancelBtn.textContent = 'Mengerti';
        } else {
            form.action = '/shifts/' + id;
            form.onsubmit = null;
            msg.innerHTML = 'Apakah Anda yakin ingin menghapus shift <strong>' + name + '</strong>?<br>Tindakan ini tidak dapat dibatalkan.';
            confirmBtn.style.display = 'inline-flex';
            cancelBtn.textContent = 'Batal';
        }

        openModal('deleteShiftModal');
    }

    /* Close on backdrop or escape */
    document.querySelectorAll('.modal-backdrop').forEach(function(m) {
        m.addEventListener('click', function(e) {
            if (e.target === this) closeModal(this.id);
        });
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.open').forEach(function(m) { closeModal(m.id); });
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

            var curVal = document.getElementById(pfx + '_display').value || '07:00';
            applyPresetTime(pfx, curVal.substring(0, 2), curVal.substring(3, 5));
        });
    }

    function openTimePickerPopover(e, pfx) {
        e.stopPropagation();
        var popover = document.getElementById(pfx + '_popover');
        var wasOpen = popover.classList.contains('open');
        closeAllTimePickers();
        if (!wasOpen) {
            popover.classList.add('open');
            var activeH = popover.querySelector('.time-column-scroll .time-scroll-item.selected');
            if (activeH) activeH.scrollIntoView({ block: 'nearest' });
        }
    }

    function closeAllTimePickers() {
        document.querySelectorAll('.time-popover-box.open').forEach(function(pop) {
            pop.classList.remove('open');
        });
    }

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

    document.addEventListener('DOMContentLoaded', function() {
        initTimePickers();
        @if ($errors->any() && !old('_edit_id'))
            openAddModal();
        @endif
    });
</script>
</body>
</html>
