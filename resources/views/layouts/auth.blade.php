<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#17375e">
    <meta name="description" content="SafeTrack — Sistem Manajemen Keselamatan Kerja.">
    <title>@yield('title', 'SafeTrack · Keselamatan dalam satu tempat')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @stack('styles')
</head>
<body>
    <main class="auth-page">
        <section class="auth-hero">
            <div class="brand">
                <div class="brand-symbol">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3 4.5 6v5.3c0 5 7.5 9.7 7.5 9.7s7.5-4.7 7.5-9.7V6L12 3Z"/>
                        <path d="m8.5 12 2.5 2.5 4.5-5"/>
                    </svg>
                </div>
                <div>
                    <div class="brand-name">safe<span>Track</span></div>
                    <div class="brand-sub">SAFETY MANAGEMENT</div>
                </div>
            </div>

            <div class="auth-intro">
                <div class="kicker">PEDULI. LAPORKAN. TUNTASKAN.</div>
                <h1>Tempat kerja aman,<br>dimulai dari <em>kita.</em></h1>
                <p>Kelola setiap temuan dan tumbuhkan budaya keselamatan dalam satu tempat.</p>
                <div class="auth-features">
                    <div>
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m10.3 4-8 14a2 2 0 0 0 1.7 3h16a2 2 0 0 0 1.7-3l-8-14a2 2 0 0 0-3.4 0Z"/>
                            <path d="M12 9v4m0 3v1"/>
                        </svg>
                        Laporkan bahaya dengan mudah
                    </div>
                    <div>
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="5" y="4" width="14" height="17" rx="2"/>
                            <rect x="9" y="2" width="6" height="4" rx="1"/>
                            <path d="m8 13 2 2 5-5"/>
                        </svg>
                        Pantau tindak lanjut secara transparan
                    </div>
                    <div>
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"/>
                            <circle cx="12" cy="12" r="5"/>
                            <circle cx="12" cy="12" r="1"/>
                        </svg>
                        Lihat dampak dari setiap kontribusi
                    </div>
                </div>
            </div>

            <div class="auth-footer">SafeTrack · Sistem Manajemen Keselamatan Kerja</div>
        </section>

        <section class="auth-form-wrap">
            <div class="auth-form">
                @yield('content')
            </div>
        </section>
    </main>

    @stack('scripts')
</body>
</html>
