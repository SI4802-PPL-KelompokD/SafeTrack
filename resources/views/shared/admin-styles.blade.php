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
            min-width: 0;
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
    @media(max-width:620px){
            .topbar-search-box{display:none}
            .topbar-actions{min-width:0;gap:8px}
            .account-switch-label{display:none}
            .account-switch-btn{max-width:200px;min-width:0}
            .account-switch-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
            .topbar-breadcrumbs{flex-shrink:0}
            .master-tabs-bar{flex-wrap:wrap}
        }
    </style>