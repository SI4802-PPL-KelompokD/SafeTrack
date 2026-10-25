<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Masuk ke SafeTrack - Sistem Manajemen Keselamatan Kerja dan Pelaporan K3.">
    <title>Masuk ke Workspace – SafeTrack</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        :root {
            --font-main: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --navy-dark: #0f2a4a;
            --navy-deep: #07192f;
            --navy-btn: #1e293b;
            --navy-btn-hover: #0f172a;
            --amber-accent: #f59e0b;
            --border-light: #e2e8f0;
            --border-focus: #3b82f6;
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --radius-md: 10px;
            --radius-lg: 16px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font-main);
            background-color: #f8fafc;
            color: var(--text-body);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            -webkit-font-smoothing: antialiased;
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 1000px;
            background: #ffffff;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: 0 20px 35px -10px rgba(15, 23, 42, 0.12), 0 4px 12px -2px rgba(15, 23, 42, 0.05);
            border: 1px solid var(--border-light);
            display: grid;
            grid-template-columns: 1fr 1.15fr;
            min-height: 600px;
        }

        /* ── Left Hero Side (Matching Screenshot) ── */
        .login-hero {
            background: linear-gradient(155deg, #07192f 0%, #0f2a4a 60%, #173860 100%);
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }
        .login-hero::after {
            content: '';
            position: absolute;
            right: -80px;
            bottom: -80px;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.12) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        /* Hero Brand */
        .hero-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 48px;
        }
        .hero-logo-box {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .hero-logo-box svg {
            width: 22px;
            height: 22px;
            color: #f59e0b;
        }
        .hero-brand-name {
            font-size: 20px;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.02em;
            color: #ffffff;
        }
        .hero-brand-name span { color: #f59e0b; }
        .hero-brand-sub {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: #94a3b8;
            text-transform: uppercase;
        }

        /* Hero Center Copy (Screenshot: PEDULI. LAPORKAN. TUNTASKAN.) */
        .hero-center-content {
            margin: auto 0;
            position: relative;
            z-index: 2;
        }
        .hero-tagline {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.12em;
            color: #f59e0b;
            text-transform: uppercase;
            margin-bottom: 14px;
        }
        .hero-headline {
            font-size: 32px;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.02em;
            color: #ffffff;
            margin-bottom: 16px;
        }
        .hero-headline .highlight {
            color: #f59e0b;
        }
        .hero-subtext {
            font-size: 14.5px;
            color: #cbd5e1;
            line-height: 1.6;
            max-width: 340px;
        }

        /* ── Right Form Side (Matching Screenshot) ── */
        .login-form-pane {
            padding: 48px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .welcome-pill-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #64748b;
            text-transform: uppercase;
            background: #f1f5f9;
            padding: 5px 12px;
            border-radius: 9999px;
            margin-bottom: 16px;
            align-self: flex-start;
        }
        .form-heading {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-heading);
            letter-spacing: -0.02em;
            line-height: 1.2;
            margin-bottom: 8px;
        }
        .form-subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 28px;
        }

        /* Flash Alerts */
        .auth-alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .auth-alert.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .auth-alert.success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .auth-alert svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        /* Inputs */
        .input-group {
            margin-bottom: 18px;
        }
        .input-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        .input-label .required-star {
            color: #ef4444;
        }
        .form-control-input {
            width: 100%;
            padding: 11px 14px;
            font-size: 14px;
            font-family: inherit;
            color: var(--text-body);
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            transition: all 0.15s ease;
        }
        .form-control-input:focus {
            border-color: var(--border-focus);
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.12);
        }
        .form-control-input.has-error {
            border-color: #ef4444;
            background: #fffafa;
        }
        .input-error-msg {
            font-size: 12px;
            color: #ef4444;
            margin-top: 4px;
        }

        /* Links row below password */
        .form-sublinks {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            margin-top: 4px;
            margin-bottom: 22px;
        }
        .demo-hint-text {
            color: var(--text-muted);
            cursor: default;
        }
        .link-forgot-pass {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }
        .link-forgot-pass:hover { text-decoration: underline; }

        /* Submit Button */
        .btn-login-submit {
            width: 100%;
            background: var(--navy-btn);
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.15s ease, transform 0.1s ease;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1);
        }
        .btn-login-submit:hover {
            background: var(--navy-btn-hover);
        }
        .btn-login-submit:active {
            transform: scale(0.99);
        }

        /* Divider */
        .demo-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 24px 0 16px;
            color: #94a3b8;
            font-size: 12px;
        }
        .demo-divider::before, .demo-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--border-light);
        }
        .demo-divider span {
            padding: 0 12px;
        }

        /* Demo Account Quick Buttons */
        .demo-actions-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }
        .btn-demo-pill {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 12px;
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-demo-pill:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: var(--text-heading);
        }
        .btn-demo-pill svg {
            width: 16px;
            height: 16px;
            color: #64748b;
        }

        /* Bottom Register Link */
        .register-footer-text {
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
        }
        .register-footer-text a {
            color: #0f2a4a;
            font-weight: 700;
            text-decoration: none;
            margin-left: 4px;
        }
        .register-footer-text a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .login-wrapper {
                grid-template-columns: 1fr;
            }
            .login-hero {
                padding: 32px 24px;
            }
            .login-form-pane {
                padding: 32px 24px;
            }
            .hero-headline {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">

    <!-- ══ Left Side: SafeTrack Brand Hero (Referencing Screenshot) ══ -->
    <div class="login-hero">
        <div>
            <!-- Brand Logo -->
            <div class="hero-brand">
                <div class="hero-logo-box">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L4 5V11.5C4 16.5 7.4 21.1 12 22.3C16.6 21.1 20 16.5 20 11.5V5L12 2Z" fill="currentColor" fill-opacity="0.2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 12L11 14L15 9.5" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <div class="hero-brand-name">safe<span>Track</span></div>
                    <div class="hero-brand-sub">SAFETY MANAGEMENT</div>
                </div>
            </div>

            <!-- Tagline & Headline -->
            <div class="hero-center-content">
                <div class="hero-tagline">PEDULI. LAPORKAN. TUNTASKAN.</div>
                <h1 class="hero-headline">
                    Tempat kerja aman,<br>
                    dimulai dari <span class="highlight">kita.</span>
                </h1>
                <p class="hero-subtext">
                    Kelola setiap temuan dan tumbuhkan budaya keselamatan dalam satu tempat.
                </p>
            </div>
        </div>

        <div style="font-size: 11.5px; color: #94a3b8;">
            &copy; 2026 SafeTrack. PT Sentra Industri.
        </div>
    </div>

    <!-- ══ Right Side: Login Form (Referencing Screenshot) ══ -->
    <div class="login-form-pane">
        <span class="welcome-pill-badge">SELAMAT DATANG DI SAFETRACK</span>
        <h2 class="form-heading">Mulai langkah aman Anda.</h2>
        <p class="form-subtitle">Masuk untuk memantau dan berkontribusi pada keselamatan kerja.</p>

        <!-- Flash Success Notification (e.g. after logout) -->
        @if (session('success'))
            <div class="auth-alert success" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Error Notification (UC-02 Alternative 3' & 4') -->
        @if ($errors->has('login'))
            <div class="auth-alert error" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span>{{ $errors->first('login') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <!-- Username atau email -->
            <div class="input-group">
                <label for="login" class="input-label">
                    Username atau email <span class="required-star">*</span>
                </label>
                <input
                    type="text"
                    id="login"
                    name="login"
                    class="form-control-input {{ $errors->has('login') ? 'has-error' : '' }}"
                    placeholder="Masukkan username atau email"
                    value="{{ old('login') }}"
                    required
                    autofocus
                >
            </div>

            <!-- Password -->
            <div class="input-group">
                <label for="password" class="input-label">
                    Password <span class="required-star">*</span>
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control-input {{ $errors->has('password') ? 'has-error' : '' }}"
                    placeholder="Masukkan password"
                    required
                >
            </div>

            <!-- Sublinks: Akun demonstrasi & Lupa password -->
            <div class="form-sublinks">
                <span class="demo-hint-text">Akun demonstrasi</span>
                <a href="#" class="link-forgot-pass" onclick="alert('Silakan hubungi tim Admin/HSE untuk reset password akun lokal.')">Lupa password?</a>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-login-submit" id="btnSubmitLogin">
                <span>Masuk ke workspace</span>
                <span style="font-size: 16px;">&rarr;</span>
            </button>
        </form>

        <!-- Demo Divider -->
        <div class="demo-divider">
            <span>atau jelajahi dengan akun demo</span>
        </div>

        <!-- Demo Account Quick Helpers (Referencing Screenshot) -->
        <div class="demo-actions-grid">
            <button type="button" class="btn-demo-pill" onclick="fillDemo('karyawan')" title="Gunakan akun demo Karyawan">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                <span>Karyawan</span>
            </button>
            <button type="button" class="btn-demo-pill" onclick="fillDemo('admin')" title="Gunakan akun demo Admin/HSE">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
                <span>Admin / HSE</span>
            </button>
        </div>

        <!-- Footer Link -->
        <div class="register-footer-text">
            Belum punya akun? <a href="#">Daftar akun</a>
        </div>
    </div>

</div>

<script>
    function fillDemo(type) {
        var loginInput = document.getElementById('login');
        var passInput = document.getElementById('password');

        if (type === 'karyawan') {
            loginInput.value = 'karyawan';
            passInput.value = 'DemoOnly!2026';
        } else if (type === 'admin') {
            loginInput.value = 'admin';
            passInput.value = 'DemoOnly!2026';
        }
        loginInput.focus();
    }
</script>

</body>
</html>
