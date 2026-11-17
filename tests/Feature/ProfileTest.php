<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(DemoSeeder::class);
    }

    /**
     * 1. Pengguna terautentikasi dapat membuka halaman profil.
     */
    public function test_authenticated_user_can_open_profile_page(): void
    {
        $user = User::where('username', 'admin')->first();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Profil Saya');
        $response->assertSee('Akun & Preferensi');
        $response->assertSee('Simpan Perubahan');
    }

    /**
     * Pengguna belum login (guest) tidak dapat membuka halaman profil.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/profile');

        $response->assertRedirect(route('login'));
    }

    /**
     * 2. Data profil yang ditampilkan sesuai akun yang sedang login.
     */
    public function test_profile_page_displays_authenticated_user_data(): void
    {
        $user = User::where('username', 'hse')->with(['department', 'employeeRegistry'])->first();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($user->email);
        $response->assertSee($user->nik);
        $response->assertSee($user->department->name);
    }

    /**
     * 3 & 4. Pengguna dapat mengubah nama lengkap dan email.
     */
    public function test_user_can_update_name_and_email(): void
    {
        $user = User::where('username', 'karyawan')->first();

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Karyawan Baru Update',
            'email' => 'karyawan.baru@safetrack.test',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success', 'Profil berhasil diperbarui.');

        $user->refresh();
        $this->assertSame('Karyawan Baru Update', $user->name);
        $this->assertSame('karyawan.baru@safetrack.test', $user->email);
    }

    /**
     * Pengguna dapat mempertahankan email miliknya sendiri saat update.
     */
    public function test_user_can_keep_their_current_email(): void
    {
        $user = User::where('username', 'admin')->first();

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Admin Tetap',
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasNoErrors();
        $this->assertSame('Admin Tetap', $user->fresh()->name);
    }

    /**
     * 5. Email duplikat (milik akun pengguna lain) ditolak.
     */
    public function test_duplicate_email_is_rejected(): void
    {
        $user = User::where('username', 'admin')->first();
        $otherUser = User::where('username', 'hse')->first();

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Admin Gagal Email',
            'email' => $otherUser->email,
        ]);

        $response->assertSessionHasErrors(['email' => 'Alamat email sudah digunakan oleh pengguna lain.']);
        $this->assertNotSame('Admin Gagal Email', $user->fresh()->name);
    }

    /**
     * 6. Pengguna dapat mengunggah foto profil yang valid.
     */
    public function test_user_can_upload_valid_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::where('username', 'admin')->first();
        $file = UploadedFile::fake()->image('profile.jpg', 300, 300);

        $response = $this->actingAs($user)->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => $file,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $user->refresh();

        $this->assertNotNull($user->profile_photo_path);
        Storage::disk('public')->assertExists($user->profile_photo_path);
    }

    /**
     * 7. Foto dengan ukuran lebih dari 2 MB atau format tidak valid ditolak.
     */
    public function test_photo_exceeding_2mb_or_invalid_format_is_rejected(): void
    {
        Storage::fake('public');
        $user = User::where('username', 'admin')->first();

        // Kasus 1: File lebih dari 2 MB (misal 3 MB)
        $largeFile = UploadedFile::fake()->create('large.jpg', 3072, 'image/jpeg');
        $response = $this->actingAs($user)->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => $largeFile,
        ]);
        $response->assertSessionHasErrors(['profile_photo' => 'Ukuran foto profil maksimal 2 MB.']);

        // Kasus 2: Format tidak valid (misal dokumen PDF)
        $invalidFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');
        $response = $this->actingAs($user)->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => $invalidFile,
        ]);
        $response->assertSessionHasErrors(['profile_photo' => 'Format foto profil harus JPG, JPEG, PNG, atau WebP.']);
    }

    /**
     * 8. Pengguna dapat mengganti password dengan verifikasi password saat ini.
     */
    public function test_user_can_change_password_with_valid_verification(): void
    {
        $user = User::where('username', 'admin')->first();

        $response = $this->actingAs($user)->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'DemoOnly!2026',
            'password' => 'NewPassword!2026',
            'password_confirmation' => 'NewPassword!2026',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success', 'Profil berhasil diperbarui.');

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword!2026', $user->password));
    }

    /**
     * Penggantian password gagal jika password saat ini salah.
     */
    public function test_password_change_fails_if_current_password_incorrect(): void
    {
        $user = User::where('username', 'admin')->first();

        $response = $this->actingAs($user)->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'PasswordSalah123',
            'password' => 'NewPassword!2026',
            'password_confirmation' => 'NewPassword!2026',
        ]);

        $response->assertSessionHasErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        $this->assertTrue(Hash::check('DemoOnly!2026', $user->fresh()->password));
    }

    /**
     * Jika salah satu kolom password diisi, ketiga kolom wajib divalidasi.
     */
    public function test_password_change_requires_all_three_fields_if_any_filled(): void
    {
        $user = User::where('username', 'admin')->first();

        // Hanya mengisi password baru tanpa current_password dan konfirmasi
        $response = $this->actingAs($user)->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'NewPassword!2026',
        ]);

        $response->assertSessionHasErrors(['current_password', 'password_confirmation']);
    }

    /**
     * 9. Pengguna tidak dapat mengubah profil pengguna lain atau hak akses (role, NIK, dsb).
     */
    public function test_user_cannot_alter_restricted_attributes(): void
    {
        $user = User::where('username', 'karyawan')->first();
        $originalRole = $user->role;
        $originalNik = $user->nik;

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Karyawan Mencoba Jadi Admin',
            'email' => $user->email,
            'role' => 'super_admin',
            'nik' => 'HACKED-NIK',
            'is_active' => false,
            'department_id' => 9999,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $user->refresh();

        $this->assertSame($originalRole, $user->role);
        $this->assertSame($originalNik, $user->nik);
        $this->assertTrue($user->is_active);
    }

    /**
     * Foto lama dibersihkan saat foto baru diunggah.
     */
    public function test_old_photo_is_deleted_when_new_photo_is_uploaded(): void
    {
        Storage::fake('public');
        $user = User::where('username', 'admin')->first();

        // Unggah foto 1
        $file1 = UploadedFile::fake()->image('first.jpg');
        $this->actingAs($user)->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => $file1,
        ]);
        $oldPath = $user->fresh()->profile_photo_path;
        Storage::disk('public')->assertExists($oldPath);

        // Unggah foto 2
        $file2 = UploadedFile::fake()->image('second.png');
        $this->actingAs($user)->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => $file2,
        ]);
        $newPath = $user->fresh()->profile_photo_path;

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    /**
     * 10. Data profil tetap tersimpan setelah dimuat ulang.
     */
    public function test_profile_persists_across_reloads(): void
    {
        $user = User::where('username', 'hse')->first();

        $this->actingAs($user)->put('/profile', [
            'name' => 'Petugas HSE Terverifikasi',
            'email' => 'hse.verified@safetrack.test',
        ]);

        // Muat ulang halaman profil via GET
        $response = $this->actingAs($user->fresh())->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Petugas HSE Terverifikasi');
        $response->assertSee('hse.verified@safetrack.test');
    }
}

