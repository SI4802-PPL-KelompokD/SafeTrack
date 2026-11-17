<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-05 (FR-02): Automated Browser Testing — Login dan Logout
 *
 * UC-02 Login: Autentikasi pengguna dengan username/email dan password,
 *              menolak akun nonaktif, dan membatasi akses sesuai role.
 * UC-03 Logout: Mengakhiri sesi pengguna dan redirect ke halaman login.
 */
class LoginLogoutTest extends DuskTestCase
{
    /**
     * Helper: buka halaman login dan tunggu form siap (clear session dulu).
     */
    private function visitLoginPage(Browser $browser): Browser
    {
        $browser->driver->manage()->deleteAllCookies();
        $browser->visit('/login')
            ->waitFor('#login', 10)
            ->waitFor('#password', 10)
            ->pause(500);

        return $browser;
    }

    /**
     * Helper: isi form login dan submit.
     */
    private function submitLogin(Browser $browser, string $login, string $password): Browser
    {
        $browser->clear('login')->pause(200)
            ->type('login', $login)->pause(200)
            ->clear('password')->pause(200)
            ->type('password', $password)->pause(200)
            ->press('Masuk ke workspace')->pause(1500);

        return $browser;
    }

    /**
     * Helper: login penuh dari halaman login sampai dashboard.
     */
    private function loginAs(Browser $browser, string $username, string $password = 'DemoOnly!2026'): Browser
    {
        $this->visitLoginPage($browser);
        $this->submitLogin($browser, $username, $password);
        $browser->waitForLocation('/dashboard', 15)->pause(500);

        return $browser;
    }

    // =====================================================================
    // UC-02: LOGIN — Standard Process (Happy Path)
    // =====================================================================

    /**
     * TC-01: Halaman login dapat dibuka dan menampilkan form login.
     */
    public function test_login_page_can_be_rendered(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitLoginPage($browser);

            $browser->assertSee('Mulai langkah aman Anda.')
                ->assertSee('SAFETY MANAGEMENT')
                ->assertSee('Masuk ke workspace')
                ->assertVisible('#login')
                ->assertVisible('#password')
                ->screenshot('TC01-login-page-rendered');
        });
    }

    /**
     * TC-02: Admin dapat login menggunakan username.
     */
    public function test_admin_can_login_using_username(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAs($browser, 'admin');

            $browser->assertPathIs('/dashboard')
                ->assertSee('Selamat datang di SafeTrack')
                ->screenshot('TC02-admin-login-username');
        });
    }

    /**
     * TC-03: Pengguna dapat login menggunakan email.
     */
    public function test_user_can_login_using_email(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAs($browser, 'hse@safetrack.test');

            $browser->assertPathIs('/dashboard')
                ->screenshot('TC03-login-via-email');
        });
    }

    /**
     * TC-04: Karyawan yang login diarahkan ke dashboard.
     */
    public function test_employee_redirected_to_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            // Fresh visit tanpa session sebelumnya
            $browser->driver->manage()->deleteAllCookies();
            $browser->visit('/login')
                ->waitFor('#login', 10)
                ->pause(1000)
                ->clear('login')->pause(300)
                ->type('login', 'karyawan')->pause(300)
                ->clear('password')->pause(300)
                ->type('password', 'DemoOnly!2026')->pause(300)
                ->press('Masuk ke workspace')
                ->pause(3000)
                ->waitForLocation('/dashboard', 20)
                ->assertPathIs('/dashboard')
                ->screenshot('TC04-employee-to-dashboard');
        });
    }

    /**
     * TC-05: Sesi aktif setelah login — user dapat mengakses dashboard.
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAs($browser, 'admin');
            $browser->assertPathIs('/dashboard');

            // Navigasi ulang ke dashboard — sesi masih aktif
            $browser->visit('/dashboard')
                ->pause(1500)
                ->assertPathIs('/dashboard')
                ->assertDontSee('Mulai langkah aman Anda.')
                ->screenshot('TC05-session-active-dashboard');
        });
    }

    /**
     * TC-06: User yang sudah login di-redirect dari halaman login ke dashboard.
     */
    public function test_authenticated_user_redirected_from_login_page(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAs($browser, 'admin');

            // Coba buka halaman login lagi — harus redirect ke dashboard
            $browser->visit('/login')
                ->pause(1500)
                ->waitForLocation('/dashboard', 10)
                ->assertPathIs('/dashboard')
                ->screenshot('TC06-redirect-from-login');
        });
    }

    // =====================================================================
    // UC-02: LOGIN — Alternative Process (Error Cases)
    // =====================================================================

    /**
     * TC-07: Login gagal jika password salah (Alternatif 3').
     */
    public function test_login_fails_with_invalid_password(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitLoginPage($browser);
            $this->submitLogin($browser, 'admin', 'PasswordSalah123');

            $browser->assertPathIs('/login')
                ->waitForText('Username/email atau password salah.', 10)
                ->assertSee('Username/email atau password salah.')
                ->screenshot('TC07-wrong-password');
        });
    }

    /**
     * TC-08: Login gagal jika username/email tidak terdaftar (Alternatif 3').
     */
    public function test_login_fails_with_nonexistent_user(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitLoginPage($browser);
            $this->submitLogin($browser, 'tidak_ada_user', 'SembarangPassword');

            $browser->assertPathIs('/login')
                ->waitForText('Username/email atau password salah.', 10)
                ->assertSee('Username/email atau password salah.')
                ->screenshot('TC08-nonexistent-user');
        });
    }

    /**
     * TC-09: Akun berstatus nonaktif ditolak login (Alternatif 4').
     */
    public function test_inactive_account_cannot_login(): void
    {
        // Set karyawan menjadi nonaktif
        DB::table('users')->where('username', 'karyawan')->update(['is_active' => false]);

        $this->browse(function (Browser $browser) {
            $this->visitLoginPage($browser);
            $this->submitLogin($browser, 'karyawan', 'DemoOnly!2026');

            $browser->assertPathIs('/login')
                ->waitForText('Akun Anda berstatus nonaktif', 10)
                ->assertSee('Akun Anda berstatus nonaktif')
                ->screenshot('TC09-inactive-account-rejected');
        });

        // Restore kembali ke aktif
        DB::table('users')->where('username', 'karyawan')->update(['is_active' => true]);
    }

    /**
     * TC-10: Validasi — field login kosong (HTML5 required).
     */
    public function test_login_field_required(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitLoginPage($browser);

            $browser->clear('login')->pause(200)
                ->type('password', 'DemoOnly!2026')->pause(200)
                ->press('Masuk ke workspace')->pause(500)
                ->assertPathIs('/login')
                ->screenshot('TC10-login-field-required');
        });
    }

    /**
     * TC-11: Validasi — field password kosong (HTML5 required).
     */
    public function test_password_field_required(): void
    {
        $this->browse(function (Browser $browser) {
            $this->visitLoginPage($browser);

            $browser->type('login', 'admin')->pause(200)
                ->clear('password')->pause(200)
                ->press('Masuk ke workspace')->pause(500)
                ->assertPathIs('/login')
                ->screenshot('TC11-password-field-required');
        });
    }

    // =====================================================================
    // UC-02: LOGIN — Role-Based Access (Postcondition)
    // =====================================================================

    /**
     * TC-12: Karyawan tidak bisa akses halaman admin (Kelola Shift).
     */
    public function test_employee_cannot_access_admin_shifts(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAs($browser, 'karyawan');

            $browser->visit('/admin/shifts')
                ->pause(1500)
                ->assertSee('403')
                ->screenshot('TC12-employee-forbidden-admin');
        });
    }

    /**
     * TC-13: Admin bisa akses halaman admin (Kelola Shift).
     */
    public function test_admin_can_access_shifts(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAs($browser, 'admin');

            $browser->visit('/admin/shifts')
                ->pause(2000)
                ->assertSee('Shift kerja')
                ->screenshot('TC13-admin-access-shifts');
        });
    }

    /**
     * TC-15: Guest yang mengakses halaman protected di-redirect ke login.
     */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $browser->visit('/dashboard')
                ->pause(1500)
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login')
                ->assertSee('Mulai langkah aman Anda.')
                ->screenshot('TC15-guest-redirect-to-login');
        });
    }

    // =====================================================================
    // UC-03: LOGOUT
    // =====================================================================

    /**
     * TC-16: Pengguna dapat logout dan sesi diakhiri.
     */
    public function test_user_can_logout(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAs($browser, 'admin');

            $browser->click('button[title="Keluar / Logout"]')
                ->pause(1500)
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login')
                ->assertSee('Anda telah berhasil keluar dari sistem.')
                ->screenshot('TC16-logout-success');
        });
    }

    /**
     * TC-17: Setelah logout, tidak bisa akses halaman protected.
     */
    public function test_cannot_access_dashboard_after_logout(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAs($browser, 'admin');

            $browser->click('button[title="Keluar / Logout"]')
                ->pause(1500)
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login');

            $browser->visit('/dashboard')
                ->pause(1500)
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login')
                ->screenshot('TC17-no-access-after-logout');
        });
    }
}
