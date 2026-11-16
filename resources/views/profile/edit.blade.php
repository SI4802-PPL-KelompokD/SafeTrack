<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Kelola informasi profil akun Anda di SafeTrack.">
    <title>Profil Saya – SafeTrack</title>
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
            --amber-text: #92400e;
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --status-green-bg: #dcfce7;
            --status-green-text: #15803d;
            --status-red-bg: #fef2f2;
            --status-red-text: #991b1b;
            --sidebar-width: 260px;
            --topbar-height: 64px;
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 14px;
            --shadow-subtle: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.04);
            --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -2px rgba(0, 0, 0, 0.03);
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

        /* SafeTrack Brand Header */
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

        /* Workspace Demonstrasi Card */
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
        }
        .sidebar-link.active svg {
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
            text-decoration: none;
            color: inherit;
        }
        .user-avatar-badge {
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
            overflow: hidden;
        }
        .user-avatar-badge img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
            font-family: inherit;
            text-decoration: none;
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
            max-width: 1200px;
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

        /* ── Alert Notifications ── */
        .alert-box {
            padding: 14px 18px;
            border-radius: var(--radius-sm);
            margin-bottom: 24px;
            font-size: 13.5px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .alert-box.success {
            background-color: var(--status-green-bg);
            border: 1px solid #bbf7d0;
            color: var(--status-green-text);
        }
        .alert-box.error {
            background-color: var(--status-red-bg);
            border: 1px solid #fecaca;
            color: var(--status-red-text);
        }
        .alert-box svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* ── Main Profile Card ── */
        .profile-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-lg);
            padding: 36px 40px;
            box-shadow: var(--shadow-subtle);
        }

        /* Profile Header Info */
        .profile-hero {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 32px;
        }
        .profile-hero-avatar {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: #dbeafe;
            color: #1e40af;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 800;
            flex-shrink: 0;
            overflow: hidden;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }
        .profile-hero-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .profile-hero-name {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-heading);
            line-height: 1.2;
            margin-bottom: 4px;
        }
        .profile-hero-meta {
            font-size: 13.5px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .profile-hero-meta .sep {
            color: #cbd5e1;
        }

        /* ── Form Controls & Grid ── */
        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
            margin-bottom: 20px;
        }
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-heading);
        }
        .form-label .required-mark {
            color: #ef4444;
            margin-left: 2px;
        }
        .form-input {
            width: 100%;
            padding: 10px 14px;
            font-size: 13.5px;
            font-family: inherit;
            color: var(--text-body);
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            transition: all 0.15s ease;
        }
        .form-input:focus {
            border-color: var(--border-focus);
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.12);
        }
        .form-input.is-invalid {
            border-color: #ef4444;
            background-color: #fffaf0;
        }
        .form-input:disabled,
        .form-input[readonly] {
            background-color: #f8fafc;
            color: #64748b;
            cursor: not-allowed;
            border-color: #e2e8f0;
        }
        .form-error-text {
            font-size: 12px;
            color: #dc2626;
            margin-top: 2px;
        }
        .form-hint-text {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 4px;
        }

        /* Custom File Input Upload Container */
        .file-upload-box {
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            padding: 8px 12px;
            background: #ffffff;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: border-color 0.15s ease;
        }
        .file-upload-box:focus-within {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }
        .file-upload-box input[type="file"] {
            font-size: 13px;
            color: var(--text-muted);
            width: 100%;
        }
        .photo-actions-wrap {
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 12.5px;
        }
        .checkbox-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #dc2626;
            cursor: pointer;
            user-select: none;
        }

        /* Divider & Section Subtitles */
        .form-divider {
            border: none;
            border-top: 1px solid var(--border-light);
            margin: 32px 0 28px;
        }
        .section-subheading {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-heading);
            margin-bottom: 4px;
        }
        .section-subdesc {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        /* Action Buttons */
        .form-actions-bar {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 32px;
        }
        .btn-submit-primary {
            background-color: var(--navy-dark);
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 600;
            font-family: inherit;
            padding: 11px 26px;
            border-radius: var(--radius-sm);
            border: none;
            cursor: pointer;
            transition: background-color 0.15s ease, transform 0.1s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        .btn-submit-primary:hover {
            background-color: #163860;
        }
        .btn-submit-primary:active {
            transform: scale(0.99);
        }
        .btn-cancel-secondary {
            display: inline-flex;
            align-items: center;
            padding: 10px 18px;
            font-size: 13.5px;
            font-weight: 500;
            color: #64748b;
            text-decoration: none;
            border-radius: var(--radius-sm);
            transition: all 0.15s ease;
        }
        .btn-cancel-secondary:hover {
            background-color: #f1f5f9;
            color: var(--text-heading);
        }

        /* ── Responsive Rules ── */
        @media (max-width: 900px) {
            .app-sidebar { display: none; }
            .app-main { margin-left: 0; }
            .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; }
            .content-container { padding: 20px 16px; }
            .profile-card { padding: 24px 20px; }
            .topbar-search-box { display: none; }
        }
    </style>
</head>
<body>
<div class="app-shell">

    <!-- ══ SafeTrack Sidebar ══ -->
    <aside class="app-sidebar" id="sidebarNav" role="navigation" aria-label="Navigasi SafeTrack">
        <!-- Brand Logo -->
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

        <!-- Workspace Demonstrasi Card -->
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

        <!-- ADMINISTRASI Menu Group (Hanya ditampilkan untuk Super Admin & Admin) -->
        @if (in_array($user->role, ['super_admin', 'admin'], true))
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
                <a href="{{ route('shifts.index') }}" class="sidebar-link">
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
        @endif

        <!-- Bottom User Profile Card -->
        <div class="sidebar-user-footer">
            <a href="{{ route('profile.edit') }}" class="user-meta-wrap" title="Buka Profil Saya">
                <div class="user-avatar-badge" id="sidebarAvatar">
                    @if ($user->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}">
                    @else
                        <span>{{ $user->initials }}</span>
                    @endif
                </div>
                <div class="user-name-title">
                    <span class="user-full-name">{{ $user->name }}</span>
                    <span class="user-role-label">{{ $user->role_label }}</span>
                </div>
            </a>
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

        <!-- Topbar Component (Shared) -->
        @include('partials.topbar', ['currentTitle' => 'Profil Saya'])

        <!-- Page Main Canvas Area -->
        <div class="content-container">

            <!-- Title & Subtitle -->
            <div class="page-header-group">
                <div class="page-category-tag">Akun & Preferensi</div>
                <h1 class="page-main-title">Profil Saya</h1>
                <p class="page-desc-text">Kelola identitas dan informasi akun demo Anda.</p>
            </div>

            <!-- Flash Success Notification -->
            @if (session('success'))
                <div class="alert-box success" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <strong>Berhasil!</strong> {{ session('success') }}
                    </div>
                </div>
            @endif

            <!-- Flash Error Notification -->
            @if (isset($errors) && $errors->any())
                <div class="alert-box error" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <div>
                        <strong>Terdapat kesalahan pada input form:</strong>
                        <ul style="margin-top: 4px; margin-left: 18px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Main Profile Form Card -->
            <div class="profile-card">

                <!-- Profile Hero Header -->
                <div class="profile-hero">
                    <div class="profile-hero-avatar" id="heroAvatar">
                        @if ($user->profile_photo_url)
                            <img id="avatarImagePreview" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}">
                        @else
                            <img id="avatarImagePreview" src="" alt="{{ $user->name }}" style="display:none;">
                            <span id="avatarInitialsText">{{ $user->initials }}</span>
                        @endif
                    </div>
                    <div>
                        <h2 class="profile-hero-name" id="displayProfileName">{{ $user->name }}</h2>
                        <div class="profile-hero-meta">
                            <span>{{ $user->nik ?? '-' }}</span>
                            <span class="sep">·</span>
                            <span>{{ $user->department?->name ?? 'Dept Health, Safety & Environment (HSE)' }}</span>
                            <span class="sep">·</span>
                            <span>{{ $user->role_label }}</span>
                        </div>
                    </div>
                </div>

                <!-- Profile Update Form -->
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    @method('PUT')

                    <!-- Baris 1: Nama Lengkap & Email -->
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="name">
                                Nama lengkap <span class="required-mark">*</span>
                            </label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-input @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}"
                                required
                                autocomplete="name"
                            >
                            @error('name')
                                <span class="form-error-text">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">
                                Email <span class="required-mark">*</span>
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}"
                                required
                                autocomplete="email"
                            >
                            @error('email')
                                <span class="form-error-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Baris 2: Foto Profil Upload -->
                    <div class="form-group">
                        <label class="form-label" for="profile_photo">
                            Foto profil
                        </label>
                        <div class="file-upload-box">
                            <input
                                type="file"
                                id="profile_photo"
                                name="profile_photo"
                                accept="image/jpeg,image/png,image/webp"
                            >
                        </div>
                        <div class="form-hint-text">JPG/PNG/WebP, maksimal 2 MB.</div>
                        @error('profile_photo')
                            <span class="form-error-text">{{ $message }}</span>
                        @enderror

                        @if ($user->profile_photo_path)
                            <div class="photo-actions-wrap">
                                <label class="checkbox-label" for="remove_photo">
                                    <input type="checkbox" id="remove_photo" name="remove_photo" value="1" {{ old('remove_photo') ? 'checked' : '' }}>
                                    Hapus foto profil saat ini
                                </label>
                            </div>
                        @endif
                    </div>

                    <!-- Baris 3: NIK & Departemen (Readonly / Non-editable) -->
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="nik_field">
                                NIK
                            </label>
                            <input
                                type="text"
                                id="nik_field"
                                class="form-input"
                                value="{{ $user->nik ?? '-' }}"
                                disabled
                                readonly
                                tabindex="-1"
                                title="Informasi NIK ditetapkan oleh sistem master data karyawan."
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="dept_field">
                                Departemen
                            </label>
                            <input
                                type="text"
                                id="dept_field"
                                class="form-input"
                                value="{{ $user->department?->name ?? 'Dept Health, Safety & Environment (HSE)' }}"
                                disabled
                                readonly
                                tabindex="-1"
                                title="Informasi departemen ditetapkan oleh sistem organisasi."
                            >
                        </div>
                    </div>

                    <hr class="form-divider">

                    <!-- Bagian Penggantian Password -->
                    <div>
                        <h3 class="section-subheading">Ganti Password</h3>
                        <p class="section-subdesc">Kosongkan seluruh kolom di bawah ini jika Anda tidak ingin mengubah password akun.</p>

                        <div class="form-grid-3">
                            <div class="form-group">
                                <label class="form-label" for="current_password">
                                    Password saat ini
                                </label>
                                <input
                                    type="password"
                                    id="current_password"
                                    name="current_password"
                                    class="form-input @error('current_password') is-invalid @enderror"
                                    placeholder="Masukkan password saat ini"
                                    autocomplete="current-password"
                                >
                                @error('current_password')
                                    <span class="form-error-text">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="password">
                                    Password baru
                                </label>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-input @error('password') is-invalid @enderror"
                                    placeholder="Minimal 8 karakter"
                                    autocomplete="new-password"
                                >
                                @error('password')
                                    <span class="form-error-text">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="password_confirmation">
                                    Konfirmasi password baru
                                </label>
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    class="form-input @error('password_confirmation') is-invalid @enderror"
                                    placeholder="Ulangi password baru"
                                    autocomplete="new-password"
                                >
                                @error('password_confirmation')
                                    <span class="form-error-text">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Simpan -->
                    <div class="form-actions-bar">
                        <button type="submit" class="btn-submit-primary" id="btnSaveProfile">
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn-cancel-secondary">
                            Batal
                        </a>
                    </div>
                </form>

            </div>

        </div>

    </main>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const photoInput = document.getElementById('profile_photo');
        const imgPreview = document.getElementById('avatarImagePreview');
        const initialsText = document.getElementById('avatarInitialsText');
        const nameInput = document.getElementById('name');
        const displayName = document.getElementById('displayProfileName');

        // Live preview foto yang baru dipilih
        if (photoInput) {
            photoInput.addEventListener('change', function (e) {
                const file = e.target.files && e.target.files[0];
                if (file) {
                    // Validasi ukuran sisi klien (maksimal 2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file foto profil melebihi batas maksimal 2 MB.');
                        photoInput.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (event) {
                        if (imgPreview) {
                            imgPreview.src = event.target.result;
                            imgPreview.style.display = 'block';
                        }
                        if (initialsText) {
                            initialsText.style.display = 'none';
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Live update nama pada header kartu saat user mengetik
        if (nameInput && displayName) {
            nameInput.addEventListener('input', function () {
                const trimmed = nameInput.value.trim();
                displayName.textContent = trimmed || '{{ $user->name }}';
            });
        }
    });
</script>
</body>
</html>
