<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-05 (FR-02): Automated Browser Testing — Login dan Logout
 *
 * Menguji alur autentikasi dan otorisasi sistem dengan simulasi interaksi manusia:
 * - Kecepatan eksekusi disesuaikan ~30 detik per test case agar tampak natural
 * - UC-02 Login: Form login, otentikasi username/email, penolakan akun nonaktif, RBAC
 * - UC-03 Logout: Pengakhiran sesi, pengalihan kembali ke login, dan proteksi rute
 */
class LoginLogoutTest extends DuskTestCase
{
    /**
     * Pastikan status akun selalu aktif di awal setiap skenario pengujian.
     */
    protected function setUp(): void
    {
        parent::setUp();
        DB::table('users')->where('username', 'karyawan')->update(['is_active' => 1]);
    }

    //Helper: buka halaman login dan tunggu form siap (clear session & cookie).

    private function visitLoginPage(Browser $browser): Browser
    {
        $browser->driver->manage()->deleteAllCookies();
        $browser->visit('/login')
            ->waitFor('#login', 10)
            ->waitFor('#password', 10)
            ->pause(1500);

        return $browser;
    }

    //isi form login dan submit dengan jeda pengetikan natural (~9 detik).
    private function submitLogin(Browser $browser, string $login, string $password): Browser
    {
        $browser->clear('login')->pause(500)
            ->type('login', $login)->pause(1500)
            ->clear('password')->pause(500)
            ->type('password', $password)->pause(1500)
            ->pause(1000)
            ->press('Masuk ke workspace')->pause(2500);

        return $browser;
    }

    //proses login penuh dari awal sampai tiba di dashboard 
    private function loginAs(Browser $browser, string $username, string $password = 'DemoOnly!2026'): Browser
    {
        $this->visitLoginPage($browser);
        $this->submitLogin($browser, $username, $password);
        $browser->waitForLocation('/dashboard', 15)->pause(2000);

        return $browser;
    }

    // =====================================================================
    // UC-02: LOGIN — Standard Process (Happy Path)
    // =====================================================================

    /**
     * TC-01: Halaman login dapat dibuka dan menampilkan form login (~30 detik).
     */
    public function test_login_page_can_be_rendered(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitLoginPage($browser); // ~3s

            // Mengamati branding SafeTrack di panel kiri
            $browser->assertSee('SAFETY MANAGEMENT')
                ->pause(5000);

            // Membaca headline selamat datang
            $browser->assertSee('Mulai langkah aman Anda.')
                ->pause(5000);

            // Memeriksa keberadaan elemen input form
            $browser->assertVisible('#login')
                ->assertVisible('#password')
                ->assertSee('Masuk ke workspace')
                ->pause(6000);

            // Mengamati tombol opsi akun demonstrasi di bagian bawah
            $browser->assertSee('Karyawan')
                ->assertSee('Admin / HSE')
                ->pause(6000)
                ->screenshot('TC01-login-page-rendered')
                ->pause(4000);
        });
    }

    /**
     * TC-02: Admin dapat login menggunakan username (~30 detik).
     */
    public function test_admin_can_login_using_username(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitLoginPage($browser); // ~3s

            // Mengamati form sebelum mengetik
            $browser->pause(2000);

            // Mengisi kredensial username admin
            $this->submitLogin($browser, 'admin', 'DemoOnly!2026'); // ~9s

            // Mengamati dashboard setelah login
            $browser->waitForLocation('/dashboard', 15)
                ->assertPathIs('/dashboard')
                ->pause(3000)
                ->assertSee('Selamat datang di SafeTrack')
                ->pause(7000);

            // Memeriksa menu Ringkasan
            $browser->assertSee('Ringkasan')
                ->pause(4000)
                ->screenshot('TC02-admin-login-username')
                ->pause(2000);
        });
    }

    /**
     * TC-03: Pengguna dapat login menggunakan email (~30 detik).
     */
    public function test_user_can_login_using_email(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitLoginPage($browser); // ~3s

            // Mengamati form
            $browser->pause(2000);

            // Mengisi kredensial format email
            $this->submitLogin($browser, 'hse@safetrack.test', 'DemoOnly!2026'); // ~9s

            // Mengamati dashboard setelah login
            $browser->waitForLocation('/dashboard', 15)
                ->assertPathIs('/dashboard')
                ->pause(5000)
                ->assertSee('Workspace')
                ->pause(6000)
                ->screenshot('TC03-login-via-email')
                ->pause(3000);
        });
    }

    /**
     * TC-04: Karyawan yang login diarahkan ke dashboard karyawan (~30 detik).
     */
    public function test_employee_redirected_to_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitLoginPage($browser); // ~3s

            // Mengamati form
            $browser->pause(2000);

            // Mengisi akun karyawan
            $this->submitLogin($browser, 'karyawan', 'DemoOnly!2026'); // ~9s

            // Memverifikasi tampilan dashboard karyawan
            $browser->waitForLocation('/dashboard', 20)
                ->assertPathIs('/dashboard')
                ->pause(4000)
                ->assertSee('Selamat datang di SafeTrack')
                ->pause(6000);

            // Memastikan kontrol cepat admin tidak tampil untuk karyawan
            $browser->assertDontSee('Akses Cepat Admin/HSE')
                ->pause(4000)
                ->screenshot('TC04-employee-to-dashboard')
                ->pause(2000);
        });
    }

    /**
     * TC-05: Sesi aktif setelah login — user dapat mengakses dashboard (~30 detik).
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            // Login awal (~14s)
            $this->loginAs($browser, 'admin');
            $browser->assertPathIs('/dashboard')
                ->pause(3000);

            // Navigasi ulang / reload dashboard — sesi tetap bertahan
            $browser->visit('/dashboard')
                ->pause(4000)
                ->assertPathIs('/dashboard')
                ->assertDontSee('Mulai langkah aman Anda.')
                ->pause(5000)
                ->screenshot('TC05-session-active-dashboard')
                ->pause(4000);
        });
    }

    /**
     * TC-06: User yang sudah login di-redirect dari halaman login ke dashboard (~30 detik).
     */
    public function test_authenticated_user_redirected_from_login_page(): void
    {
        $this->browse(function (Browser $browser) {
            // Login awal (~14s)
            $this->loginAs($browser, 'admin');

            // Mengamati dashboard
            $browser->pause(2000);

            // Coba membuka kembali halaman login saat sesi aktif
            $browser->visit('/login')
                ->pause(3000)
                ->waitForLocation('/dashboard', 10)
                ->assertPathIs('/dashboard')
                ->pause(6000)
                ->screenshot('TC06-redirect-from-login')
                ->pause(4000);
        });
    }

    // =====================================================================
    // UC-02: LOGIN — Alternative Process (Error Cases)
    // =====================================================================

    /**
     * TC-07: Login gagal jika password salah (Alternatif 3') (~30 detik).
     */
    public function test_login_fails_with_invalid_password(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitLoginPage($browser); // ~3s

            $browser->pause(2000);

            // Mengisi password keliru
            $this->submitLogin($browser, 'admin', 'PasswordSalah123'); // ~9s

            // Membaca pesan error penolakan autentikasi
            $browser->assertPathIs('/login')
                ->waitForText('Username/email atau password salah.', 10)
                ->pause(4000)
                ->assertSee('Username/email atau password salah.')
                ->pause(6000)
                ->screenshot('TC07-wrong-password')
                ->pause(5000);
        });
    }

    /**
     * TC-08: Login gagal jika username/email tidak terdaftar (Alternatif 3') (~30 detik).
     */
    public function test_login_fails_with_nonexistent_user(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitLoginPage($browser); // ~3s

            $browser->pause(2000);

            // Mengisi user fiktif
            $this->submitLogin($browser, 'tidak_ada_user', 'SembarangPassword'); // ~9s

            // Membaca pesan error
            $browser->assertPathIs('/login')
                ->waitForText('Username/email atau password salah.', 10)
                ->pause(4000)
                ->assertSee('Username/email atau password salah.')
                ->pause(6000)
                ->screenshot('TC08-nonexistent-user')
                ->pause(5000);
        });
    }

    /**
     * TC-09: Akun berstatus nonaktif ditolak login (Alternatif 4') (~30 detik).
     */
    public function test_inactive_account_cannot_login(): void
    {
        // Set karyawan menjadi nonaktif di database
        DB::table('users')->where('username', 'karyawan')->update(['is_active' => false]);

        $this->browse(function (Browser $browser) {
            $this->visitLoginPage($browser); // ~3s

            $browser->pause(2000);

            // Mencoba login dengan akun nonaktif
            $this->submitLogin($browser, 'karyawan', 'DemoOnly!2026'); // ~9s

            // Membaca pesan penolakan status nonaktif
            $browser->assertPathIs('/login')
                ->waitForText('Akun Anda berstatus nonaktif', 10)
                ->pause(4000)
                ->assertSee('Akun Anda berstatus nonaktif')
                ->pause(6000)
                ->screenshot('TC09-inactive-account-rejected')
                ->pause(4000);
        });

        // Restore kembali ke status aktif
        DB::table('users')->where('username', 'karyawan')->update(['is_active' => true]);
    }

    /**
     * TC-10: Validasi — field login kosong (HTML5 required) (~29 detik).
     */
    public function test_login_field_required(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitLoginPage($browser); // ~3s

            $browser->pause(3000);

            // Kosongkan login, isi password saja
            $browser->clear('login')->pause(1000)
                ->type('password', 'DemoOnly!2026')->pause(2000)
                ->pause(2000)
                ->press('Masuk ke workspace')->pause(2000);

            // Browser menolak pengiriman karena HTML5 required — tetap berada di /login
            $browser->assertPathIs('/login')
                ->pause(7000)
                ->screenshot('TC10-login-field-required')
                ->pause(7000);
        });
    }

    /**
     * TC-11: Validasi — field password kosong (HTML5 required) (~29 detik).
     */
    public function test_password_field_required(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitLoginPage($browser); // ~3s

            $browser->pause(3000);

            // Isi login, kosongkan password
            $browser->type('login', 'admin')->pause(2000)
                ->clear('password')->pause(1000)
                ->pause(2000)
                ->press('Masuk ke workspace')->pause(2000);

            // Browser menolak pengiriman karena HTML5 required
            $browser->assertPathIs('/login')
                ->pause(7000)
                ->screenshot('TC11-password-field-required')
                ->pause(7000);
        });
    }

    // =====================================================================
    // UC-02: LOGIN — Role-Based Access (Postcondition)
    // =====================================================================

    /**
     * TC-12: Karyawan tidak bisa akses halaman admin (Kelola Shift) (~30 detik).
     */
    public function test_employee_cannot_access_admin_shifts(): void
    {
        $this->browse(function (Browser $browser) {
            // Login karyawan (~14s)
            $this->loginAs($browser, 'karyawan');

            $browser->pause(2000);

            // Mencoba membobol akses URL admin
            $browser->visit('/admin/shifts')
                ->pause(4000)
                ->assertSee('403')
                ->pause(6000)
                ->screenshot('TC12-employee-forbidden-admin')
                ->pause(4000);
        });
    }

    /**
     * TC-13: Admin bisa akses halaman admin (Kelola Shift) (~30 detik).
     */
    public function test_admin_can_access_shifts(): void
    {
        $this->browse(function (Browser $browser) {
            // Login admin (~14s)
            $this->loginAs($browser, 'admin');

            $browser->pause(2000);

            // Mengakses halaman administrasi shift kerja
            $browser->visit('/admin/shifts')
                ->pause(4000)
                ->assertSee('Shift kerja')
                ->pause(6000)
                ->screenshot('TC13-admin-access-shifts')
                ->pause(4000);
        });
    }

    /**
     * TC-15: Guest yang mengakses halaman protected di-redirect ke login (~30 detik).
     */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $this->browse(function (Browser $browser) {
            // Pastikan tidak ada sesi
            $browser->driver->manage()->deleteAllCookies();
            $browser->pause(3000);

            // Mencoba akses halaman protected secara langsung
            $browser->visit('/dashboard')
                ->pause(4000)
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login')
                ->pause(5000)
                ->assertSee('Mulai langkah aman Anda.')
                ->pause(7000)
                ->screenshot('TC15-guest-redirect-to-login')
                ->pause(6000);
        });
    }

    // =====================================================================
    // UC-03: LOGOUT
    // =====================================================================

    /**
     * TC-16: Pengguna dapat logout dan sesi diakhiri (~30 detik).
     */
    public function test_user_can_logout(): void
    {
        $this->browse(function (Browser $browser) {
            // Login awal (~14s)
            $this->loginAs($browser, 'admin');

            // Mengamati tombol keluar di pojok bawah sidebar
            $browser->pause(3000);

            // Menekan tombol logout
            $browser->click('button[title="Keluar / Logout"]')
                ->pause(3000)
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login')
                ->pause(3000)
                ->assertSee('Anda telah berhasil keluar dari sistem.')
                ->pause(4000)
                ->screenshot('TC16-logout-success')
                ->pause(3000);
        });
    }

    /**
     * TC-17: Setelah logout, tidak bisa akses halaman protected (~30 detik).
     */
    public function test_cannot_access_dashboard_after_logout(): void
    {
        $this->browse(function (Browser $browser) {
            // Login awal (~14s)
            $this->loginAs($browser, 'admin');

            // Logout
            $browser->pause(2000);
            $browser->click('button[title="Keluar / Logout"]')
                ->pause(3000)
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login')
                ->pause(2000);

            // Mencoba kembali mengakses halaman dalam setelah logout
            $browser->visit('/dashboard')
                ->pause(3000)
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login')
                ->pause(3000)
                ->screenshot('TC17-no-access-after-logout')
                ->pause(2000);
        });
    }
}
