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
        $response->assertRedirect(route('dashboard'));
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

        $response = $this->actingAs($employee)->get('/admin/shifts');

        $response->assertForbidden();
    }

    /**
     * Admin dapat mengakses halaman Kelola Shift.
     */
    public function test_admin_can_access_shifts(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/shifts');

        $response->assertStatus(200);
        $response->assertSee('Kelola shift kerja');
    }

    public function test_employee_dashboard_hides_administrative_controls(): void
    {
        $employee = User::where('username', 'karyawan')->firstOrFail();
        $this->actingAs($employee)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('href="'.route('shifts.index').'"', false)
            ->assertDontSee('Akses Cepat Admin/HSE');
    }

    public function test_admin_role_can_access_administration(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->get(route('shifts.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.work-areas.index'))->assertOk();
        $this->actingAs($admin)->postJson(route('daily-inspections.store'), [])->assertForbidden();
    }

    /**
     * TC-05: Sesi aktif setelah login — user dapat mengakses dashboard.
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::where('username', 'admin')->firstOrFail();

        $this->post('/login', [
            'login' => 'admin',
            'password' => 'DemoOnly!2026',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    /**
     * TC-06: User yang sudah login di-redirect dari halaman login ke dashboard.
     */
    public function test_authenticated_user_redirected_from_login_page(): void
    {
        $user = User::where('username', 'admin')->firstOrFail();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect(route('dashboard'));
    }

    /**
     * TC-10: Validasi — field login wajib diisi.
     */
    public function test_login_requires_login_field(): void
    {
        $response = $this->post('/login', [
            'login' => '',
            'password' => 'DemoOnly!2026',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['login' => 'Username atau email wajib diisi.']);
    }

    /**
     * TC-11: Validasi — field password wajib diisi.
     */
    public function test_login_requires_password_field(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin',
            'password' => '',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['password' => 'Password wajib diisi.']);
    }

    /**
     * TC-15: Guest yang mengakses halaman protected di-redirect ke login.
     */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
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

    /**
     * TC-17: Setelah logout, tidak bisa akses halaman protected lagi.
     */
    public function test_cannot_access_dashboard_after_logout(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)->post('/logout');
        $this->assertGuest();

        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    /**
     * TC-18: Guest tidak bisa POST logout — di-redirect ke login.
     */
    public function test_guest_cannot_post_logout(): void
    {
        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
    }
}
