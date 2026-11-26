<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProfileTest extends DuskTestCase
{
    private const STEP_PAUSE_MS = 800;

    private function makeUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Pengguna Profil',
            'email' => 'profil-'.Str::uuid().'@safetrack.test',
            'password' => 'PasswordLama123',
        ], $attributes));
    }

    private function fakePng(string $filename, int $sizeInKilobytes): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($filename, str_pad($png, $sizeInKilobytes * 1024, "\0"));
    }

    public function test_tc01_user_can_view_own_profile(): void
    {
        $user = $this->makeUser(['name' => 'Karyawan Demo']);
        $photoPath = 'profile-photos/'.Str::uuid().'.png';
        Storage::disk('public')->put($photoPath, 'profile-image');
        $user->forceFill(['profile_photo_path' => $photoPath])->save();

        $this->browse(function (Browser $browser) use ($user): void {
            $browser->loginAs($user)->visit('/profile')->pause(self::STEP_PAUSE_MS)
                ->assertSee('Karyawan Demo')
                ->assertInputValue('email', $user->email)
                ->assertPresent('#avatarImagePreview');
        });
    }

    public function test_tc02_and_tc03_user_can_update_name_and_email(): void
    {
        $user = $this->makeUser();
        $email = 'alya-'.Str::uuid().'@safetrack.test';

        $this->browse(function (Browser $browser) use ($user, $email): void {
            $browser->loginAs($user)->visit('/profile')->pause(self::STEP_PAUSE_MS)
                ->clear('name')->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('name', 'Alya Rafidah', 80)->pause(self::STEP_PAUSE_MS)
                ->clear('email')->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('email', $email, 60)->pause(self::STEP_PAUSE_MS)
                ->press('Simpan Perubahan')
                ->waitForText('Profil berhasil diperbarui.')
                ->pause(self::STEP_PAUSE_MS);
        });

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Alya Rafidah',
            'email' => $email,
        ]);
    }

    public function test_tc04_user_can_upload_a_500kb_png_photo(): void
    {
        $user = $this->makeUser();
        $photo = $this->fakePng('profile.png', 500);

        $this->browse(function (Browser $browser) use ($user, $photo): void {
            $browser->loginAs($user)->visit('/profile')->pause(self::STEP_PAUSE_MS)
                ->attach('profile_photo', $photo->getRealPath())->pause(self::STEP_PAUSE_MS)
                ->press('Simpan Perubahan')
                ->waitForText('Profil berhasil diperbarui.')
                ->pause(self::STEP_PAUSE_MS)
                ->assertPresent('#avatarImagePreview');
        });

        $this->assertNotNull($user->fresh()->profile_photo_path);
    }

    public function test_tc05_user_can_change_password(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser, Browser $freshBrowser) use ($user): void {
            $browser->loginAs($user)->visit('/profile')->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('current_password', 'PasswordLama123', 60)->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('password', 'Pass1234', 80)->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('password_confirmation', 'Pass1234', 80)->pause(self::STEP_PAUSE_MS)
                ->press('Simpan Perubahan')
                ->waitForText('Profil berhasil diperbarui.')
                ->pause(self::STEP_PAUSE_MS);

            $freshBrowser->visit('/login')->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('login', $user->username, 60)->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('password', 'Pass1234', 80)->pause(self::STEP_PAUSE_MS)
                ->press('button[type="submit"]')
                ->waitForLocation('/dashboard')
                ->pause(self::STEP_PAUSE_MS)
                ->assertPathIs('/dashboard');
        });

        $this->assertTrue(Hash::check('Pass1234', $user->fresh()->password));
    }

    public function test_tc06_name_is_required_and_remains_unchanged(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user): void {
            $browser->loginAs($user)->visit('/profile')->pause(self::STEP_PAUSE_MS)
                ->clear('name')->pause(self::STEP_PAUSE_MS)
                ->press('Simpan Perubahan')
                ->waitForText('Nama wajib diisi.');
        });

        $this->assertSame('Pengguna Profil', $user->fresh()->name);
    }

    public function test_tc07_invalid_email_format_is_rejected(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user): void {
            $browser->loginAs($user)->visit('/profile')->pause(self::STEP_PAUSE_MS)
                ->clear('email')->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('email', 'alya@', 180)->pause(self::STEP_PAUSE_MS)
                ->press('Simpan Perubahan')
                ->waitForText('Format alamat email tidak valid.');
        });

        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_tc08_duplicate_email_is_rejected(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser(['email' => 'other-'.Str::uuid().'@safetrack.test']);

        $this->browse(function (Browser $browser) use ($user, $other): void {
            $browser->loginAs($user)->visit('/profile')->pause(self::STEP_PAUSE_MS)
                ->clear('email')->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('email', $other->email, 60)->pause(self::STEP_PAUSE_MS)
                ->press('Simpan Perubahan')
                ->waitForText('Email sudah digunakan.');
        });

        $this->assertSame($user->email, $user->fresh()->email);
        $this->assertSame('Pengguna Profil', $user->fresh()->name);
    }

    public function test_tc09_pdf_photo_is_rejected_and_old_photo_is_kept(): void
    {
        $user = $this->makeUser();
        $oldPath = 'profile-photos/old.png';
        Storage::disk('public')->put($oldPath, 'old-photo');
        $user->forceFill(['profile_photo_path' => $oldPath])->save();
        $pdf = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $this->browse(function (Browser $browser) use ($user, $pdf): void {
            $browser->loginAs($user)->visit('/profile')->pause(self::STEP_PAUSE_MS)
                ->attach('profile_photo', $pdf->getRealPath())->pause(self::STEP_PAUSE_MS)
                ->press('Simpan Perubahan')
                ->waitForText('Format foto profil harus JPG, JPEG, PNG, atau WebP.');
        });

        $this->assertSame($oldPath, $user->fresh()->profile_photo_path);
    }

    public function test_tc10_photo_over_5mb_is_rejected_and_old_photo_is_kept(): void
    {
        $user = $this->makeUser();
        $oldPath = 'profile-photos/old-large.png';
        Storage::disk('public')->put($oldPath, 'old-photo');
        $user->forceFill(['profile_photo_path' => $oldPath])->save();
        $photo = $this->fakePng('large.png', 5120);

        $this->browse(function (Browser $browser) use ($user, $photo): void {
            $browser->loginAs($user)->visit('/profile')->pause(self::STEP_PAUSE_MS)
                ->attach('profile_photo', $photo->getRealPath())
                ->waitForDialog()
                ->assertDialogOpened('Ukuran file foto profil melebihi batas maksimal 2 MB.')
                ->acceptDialog()->pause(self::STEP_PAUSE_MS);
        });

        $this->assertSame($oldPath, $user->fresh()->profile_photo_path);
    }

    public function test_tc11_wrong_current_password_is_rejected(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user): void {
            $browser->loginAs($user)->visit('/profile')->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('current_password', 'salah123', 120)->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('password', 'Pass1234', 80)->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('password_confirmation', 'Pass1234', 80)->pause(self::STEP_PAUSE_MS)
                ->press('Simpan Perubahan')
                ->waitForText('Password saat ini tidak sesuai.');
        });

        $this->assertTrue(Hash::check('PasswordLama123', $user->fresh()->password));
    }

    public function test_tc12_new_password_must_have_at_least_8_characters(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user): void {
            $browser->loginAs($user)->visit('/profile')->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('current_password', 'PasswordLama123', 60)->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('password', 'abc', 180)->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('password_confirmation', 'abc', 180)->pause(self::STEP_PAUSE_MS)
                ->press('Simpan Perubahan')
                ->waitForText('Password baru minimal terdiri dari 8 karakter.');
        });

        $this->assertTrue(Hash::check('PasswordLama123', $user->fresh()->password));
    }

    public function test_tc13_password_confirmation_must_match(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user): void {
            $browser->loginAs($user)->visit('/profile')->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('current_password', 'PasswordLama123', 60)->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('password', 'Pass1234', 80)->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('password_confirmation', 'Different1', 80)->pause(self::STEP_PAUSE_MS)
                ->press('Simpan Perubahan')
                ->waitForText('Konfirmasi password baru tidak cocok.');
        });

        $this->assertTrue(Hash::check('PasswordLama123', $user->fresh()->password));
    }

    public function test_tc14_guest_is_redirected_to_login(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/profile')->pause(self::STEP_PAUSE_MS)->assertPathIs('/login');
        });
    }

    public function test_tc15_request_user_id_cannot_change_another_profile(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser(['name' => 'Data Milik User Lain']);

        $this->browse(function (Browser $browser) use ($user, $other): void {
            $browser->loginAs($user)->visit('/profile')->pause(self::STEP_PAUSE_MS);
            $browser->script("const input = document.createElement('input'); input.type='hidden'; input.name='user_id'; input.value='{$other->id}'; document.getElementById('name').form.append(input);");
            $browser->pause(self::STEP_PAUSE_MS)
                ->clear('name')->pause(self::STEP_PAUSE_MS)
                ->typeSlowly('name', 'Nama Pengguna Login', 80)->pause(self::STEP_PAUSE_MS)
                ->press('Simpan Perubahan')
                ->waitForText('Profil berhasil diperbarui.');
        });

        $this->assertSame('Data Milik User Lain', $other->fresh()->name);
        $this->assertSame('Nama Pengguna Login', $user->fresh()->name);
    }
}
