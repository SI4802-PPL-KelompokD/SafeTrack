<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(DemoSeeder::class);
    }

    /**
     * Halaman login dapat dibuka.
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Mulai langkah aman Anda.');
        $response->assertSee('SAFETY MANAGEMENT');
        $response->assertSee('Masuk ke workspace');
    }

    /**
     * Admin dapat login menggunakan username.
     */
    public function test_user_can_login_using_username(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin',
            'password' => 'DemoOnly!2026',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('shifts.index'));
        $response->assertSessionHas('success');
    }

    /**
     * HSE dengan akun pribadi dapat login menggunakan email.
     */
    public function test_user_can_login_using_email(): void
    {
        $response = $this->post('/login', [
            'login' => 'hse@safetrack.test',
            'password' => 'DemoOnly!2026',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Karyawan yang login diarahkan ke dashboard karyawan.
     */
    public function test_employee_is_redirected_to_dashboard(): void
    {
        $response = $this->post('/login', [
            'login' => 'karyawan',
            'password' => 'DemoOnly!2026',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Login gagal jika password salah (Alternatif 3').
     */
    public function test_login_fails_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin',
            'password' => 'PasswordSalah123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['login' => 'Username/email atau password salah.']);
    }

    /**
     * Login gagal jika username/email tidak terdaftar (Alternatif 3').
     */
    public function test_login_fails_with_nonexistent_user(): void
    {
        $response = $this->post('/login', [
            'login' => 'tidak_ada_user',
            'password' => 'PasswordSalah123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['login' => 'Username/email atau password salah.']);
    }

    /**
     * Akun berstatus nonaktif ditolak login (Alternatif 4').
     */
    public function test_inactive_account_cannot_login(): void
    {
        // Set akun karyawan menjadi nonaktif
        $user = User::where('username', 'karyawan')->first();
        $user->is_active = false;
        $user->save();

        $response = $this->post('/login', [
            'login' => 'karyawan',
            'password' => 'DemoOnly!2026',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'login' => 'Akun Anda berstatus nonaktif. Silakan hubungi Administrator atau tim HSE.',
        ]);
    }

    /**
     * Akses halaman admin (Kelola Shift) dibatasi sehingga karyawan tidak dapat mengaksesnya.
     */
    public function test_employee_cannot_access_admin_shifts(): void
    {
        $employee = User::where('username', 'karyawan')->first();

        $response = $this->actingAs($employee)->get('/shifts');

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    /**
     * Admin dapat mengakses halaman Kelola Shift.
     */
    public function test_admin_can_access_shifts(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get('/shifts');

        $response->assertStatus(200);
        $response->assertSee('Kelola shift kerja');
    }

    /**
     * Pengguna dapat logout dan sesi diakhiri (UC-03).
     */
    public function test_user_can_logout(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
