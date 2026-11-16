<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\RegistrationService;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(DemoSeeder::class);
    }

    /**
     * Halaman registrasi dapat dibuka dan menampilkan elemen UI sesuai acuan.
     */
    public function test_register_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('REGISTRASI KARYAWAN');
        $response->assertSee('Buat akun SafeTrack.');
        $response->assertSee('Registrasi ini khusus akun Karyawan. Akun HSE disediakan oleh sistem.');
        $response->assertSee('Contoh: EMP-001');
        $response->assertSee('Buat akun Karyawan');
        $response->assertSee('Masuk ke akun');
    }

    /**
     * 1. Registrasi sukses:
     * - user terbuat
     * - role 'employee'
     * - name dan department_id diambil dari registry
     * - employee_registry_id terisi
     * - password ter-hash (bukan plaintext, Hash::check valid)
     * - redirect ke login dengan flash sukses
     * - JANGAN auto-login (tetap guest)
     */
    public function test_employee_registration_succeeds_with_valid_data(): void
    {
        $response = $this->post('/register', [
            'nik' => 'DEMO004',
            'username' => 'karyawan.baru',
            'email' => 'baru@safetrack.test',
            'password' => 'Password123!',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success', 'Registrasi Karyawan berhasil. Silakan masuk.');

        $user = User::where('username', 'karyawan.baru')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Calon Karyawan Tetap', $user->name);
        $this->assertEquals('employee', $user->role);
        $this->assertEquals(2, $user->department_id); // OPS department
        $this->assertTrue((bool) $user->is_active);
        $this->assertNotEquals('Password123!', $user->password);
        $this->assertTrue(Hash::check('Password123!', $user->password));

        // Registry link terhubung
        $registry = DB::table('employee_registry')->where('nik', 'DEMO004')->first();
        $this->assertEquals($registry->id, $user->employee_registry_id);
    }

    /**
     * 2. NIK tidak terdaftar → ditolak, tidak ada user baru.
     */
    public function test_registration_fails_when_nik_not_in_registry(): void
    {
        $usersBefore = User::count();

        $response = $this->post('/register', [
            'nik' => 'NIK_TIDAK_ADA_999',
            'username' => 'user.baru',
            'email' => 'user.baru@safetrack.test',
            'password' => 'Password123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'nik' => 'NIK tidak terdaftar pada daftar karyawan. Hubungi Admin/HSE.',
        ]);
        $this->assertEquals($usersBefore, User::count());
    }

    /**
     * 3. NIK registry nonaktif → ditolak.
     */
    public function test_registration_fails_when_registry_is_inactive(): void
    {
        DB::table('employee_registry')->where('nik', 'DEMO004')->update(['is_active' => false]);
        $usersBefore = User::count();

        $response = $this->post('/register', [
            'nik' => 'DEMO004',
            'username' => 'user.baru',
            'email' => 'user.baru@safetrack.test',
            'password' => 'Password123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'nik' => 'NIK tidak terdaftar pada daftar karyawan. Hubungi Admin/HSE.',
        ]);
        $this->assertEquals($usersBefore, User::count());
    }

    /**
     * 4. NIK sudah dipakai (termasuk user soft-deleted) → ditolak dengan pesan yang benar.
     */
    public function test_registration_fails_when_nik_is_already_used_by_active_user(): void
    {
        $usersBefore = User::count();

        $response = $this->post('/register', [
            'nik' => 'DEMO001',
            'username' => 'admin.baru',
            'email' => 'admin.baru@safetrack.test',
            'password' => 'Password123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'nik' => 'NIK sudah digunakan. Silakan masuk ke akun Anda.',
        ]);
        $this->assertEquals($usersBefore, User::count());
    }

    public function test_registration_fails_when_nik_is_already_used_by_soft_deleted_user(): void
    {
        // Soft delete user DEMO003
        $user = User::where('username', 'karyawan')->first();
        $user->delete();
        $this->assertSoftDeleted('users', ['id' => $user->id]);

        $usersBefore = User::withTrashed()->count();

        $response = $this->post('/register', [
            'nik' => 'DEMO003',
            'username' => 'karyawan.reborn',
            'email' => 'reborn@safetrack.test',
            'password' => 'Password123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'nik' => 'NIK sudah digunakan. Silakan masuk ke akun Anda.',
        ]);
        $this->assertEquals($usersBefore, User::withTrashed()->count());
    }

    /**
     * 5. Username diperbolehkan sama, email duplikat ditolak.
     */
    public function test_registration_allows_duplicate_username_with_different_email(): void
    {
        $response = $this->post('/register', [
            'nik' => 'DEMO004',
            'username' => 'karyawan', // Sudah dipakai oleh DEMO003, tapi diperbolehkan sama
            'email' => 'karyawan.lain@safetrack.test',
            'password' => 'Password123!',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $this->assertEquals(2, User::where('username', 'karyawan')->count());
    }

    public function test_registration_fails_when_email_is_duplicated(): void
    {
        $response = $this->post('/register', [
            'nik' => 'DEMO004',
            'username' => 'karyawan.beda',
            'email' => 'karyawan@safetrack.test', // Sudah dipakai oleh DEMO003
            'password' => 'Password123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'email' => 'Email sudah digunakan.',
        ]);
    }

    /**
     * 6. Field kosong / format tidak valid / password < 8 → error validasi per field.
     */
    public function test_registration_fails_when_required_fields_are_missing(): void
    {
        $response = $this->post('/register', []);

        $this->assertGuest();
        $response->assertSessionHasErrors(['nik', 'username', 'email', 'password']);
    }

    public function test_registration_fails_when_username_format_is_invalid(): void
    {
        $response = $this->post('/register', [
            'nik' => 'DEMO004',
            'username' => 'user name with space!@#',
            'email' => 'email@safetrack.test',
            'password' => 'Password123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['username']);
    }

    public function test_registration_fails_when_password_is_too_short(): void
    {
        $response = $this->post('/register', [
            'nik' => 'DEMO004',
            'username' => 'username.valid',
            'email' => 'email@safetrack.test',
            'password' => 'short', // < 8 karakter
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'password' => 'Password minimal 8 karakter.',
        ]);
    }

    /**
     * 7. Request yang menyisipkan role=admin atau department_id tetap menghasilkan role 'employee'
     * dan department dari registry.
     */
    public function test_injected_role_and_department_id_are_ignored(): void
    {
        $response = $this->post('/register', [
            'nik' => 'DEMO004', // Registry department_id = 2 (OPS)
            'username' => 'hacker.admin',
            'email' => 'hacker@safetrack.test',
            'password' => 'Password123!',
            'role' => 'super_admin',
            'department_id' => 1, // Mencoba masuk HSE
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login'));

        $user = User::where('username', 'hacker.admin')->first();
        $this->assertNotNull($user);
        $this->assertEquals('employee', $user->role);
        $this->assertEquals(2, $user->department_id); // Tetap OPS dari registry
    }

    /**
     * 8. Kegagalan di tengah proses (mis. exception) → transaksi rollback, tidak ada user tersimpan.
     */
    public function test_system_failure_during_registration_rolls_back_and_stores_no_user(): void
    {
        $usersBefore = User::count();

        $mockService = Mockery::mock(RegistrationService::class);
        $mockService->shouldReceive('register')
            ->once()
            ->andThrow(new \RuntimeException('Simulated system failure'));

        $this->app->instance(RegistrationService::class, $mockService);

        $response = $this->post('/register', [
            'nik' => 'DEMO004',
            'username' => 'fail.user',
            'email' => 'fail@safetrack.test',
            'password' => 'Password123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['system']);
        $this->assertEquals($usersBefore, User::count());
    }
}
