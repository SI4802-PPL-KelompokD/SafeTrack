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
    private const STEP_PAUSE_MS = 500;

    private function makeUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Pengguna Profil',
            'email' => 'profil-' . Str::uuid() . '@safetrack.test',
            'password' => 'PasswordLama123',
        ], $attributes));
    }

    private function fakePng(string $filename, int $sizeInKilobytes): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII='
        );

        return UploadedFile::fake()->createWithContent(
            $filename,
            str_pad($png, $sizeInKilobytes * 1024, "\0")
        );
    }

    public function test_tc01_user_can_view_own_profile(): void
    {
        $user = $this->makeUser(['name' => 'Karyawan Demo']);

        $this->browse(function (Browser $browser) use ($user): void {
            $browser->loginAs($user)
                ->visit('/profile')
                ->assertSee('Karyawan Demo')
                ->assertInputValue('email', $user->email)
                ->assertPresent('#avatarImagePreview');
        });
    }

public function test_tc02_user_can_update_name(): void
{
    $user = $this->makeUser();

    $this->browse(function (Browser $browser) use ($user): void {
        $browser->loginAs($user)
            ->visit('/profile')
            ->clear('name')
            ->type('name', 'Alya Rafidah')
            ->press('#btnSaveProfile')
            ->waitForText('Profil berhasil diperbarui.');
    });

    $this->assertSame('Alya Rafidah', $user->fresh()->name);
}

    public function test_tc03_user_can_update_email(): void
    {
        $user = $this->makeUser();
        $newEmail = 'alya-' . Str::uuid() . '@safetrack.test';

        $this->browse(function (Browser $browser) use ($user, $newEmail): void {
            $browser->loginAs($user)
                ->visit('/profile')
                ->clear('email')
                ->type('email', $newEmail)
                ->press('#btnSaveProfile')
                ->waitForText('Profil berhasil diperbarui.');
        });

        $this->assertSame($newEmail, $user->fresh()->email);
    }

    public function test_tc04_user_can_upload_a_500kb_png_photo(): void
    {
        Storage::fake('public');

        $user = $this->makeUser();
        $photo = $this->fakePng('profile.png', 500);

        $this->browse(function (Browser $browser) use ($user, $photo): void {
            $browser->loginAs($user)
                ->visit('/profile')
                ->attach('profile_photo', $photo->getRealPath())
                ->press('#btnSaveProfile')
                ->waitForText('Profil berhasil diperbarui.');
        });

        $photoPath = $user->fresh()->profile_photo_path;

        $this->assertNotNull($photoPath);
        Storage::disk('public')->assertExists($photoPath);
    }

    public function test_tc05_user_can_change_password_and_login_again(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser, Browser $freshBrowser) use ($user): void {
            $browser->loginAs($user)
                ->visit('/profile')
                ->type('current_password', 'PasswordLama123')
                ->type('password', 'Pass1234')
                ->type('password_confirmation', 'Pass1234')
                ->press('#btnSaveProfile')
                ->waitForText('Profil berhasil diperbarui.');

            $freshBrowser->visit('/login')
                ->type('login', $user->username)
                ->type('password', 'Pass1234')
                ->press('#btnSubmitLogin')
                ->waitForLocation('/dashboard')
                ->assertPathIs('/dashboard');
        });

        $this->assertTrue(
            Hash::check('Pass1234', $user->fresh()->password)
        );
    }

    public function test_tc06_name_is_required(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user): void {
            $browser->loginAs($user)
                ->visit('/profile')
                ->clear('name')
                ->press('#btnSaveProfile')
                ->waitForText('Nama wajib diisi.');
        });

        $this->assertSame('Pengguna Profil', $user->fresh()->name);
    }

    public function test_tc07_invalid_email_format_is_rejected(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user): void {
            $browser->loginAs($user)
                ->visit('/profile')
                ->clear('email')
                ->type('email', 'alya@')
                ->press('#btnSaveProfile')
                ->waitForText('Format alamat email tidak valid.');
        });

        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_tc08_duplicate_email_is_rejected(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user, $other): void {
            $browser->loginAs($user)
                ->visit('/profile')
                ->clear('email')
                ->type('email', $other->email)
                ->press('#btnSaveProfile')
                ->waitForText('Email sudah digunakan.');
        });

        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_tc09_invalid_photo_format_is_rejected(): void
    {
        Storage::fake('public');

        $user = $this->makeUser();
        $oldPath = 'profile-photos/old.png';

        Storage::disk('public')->put($oldPath, 'old-photo');

        $user->forceFill([
            'profile_photo_path' => $oldPath,
        ])->save();

        $pdf = UploadedFile::fake()->create(
            'document.pdf',
            500,
            'application/pdf'
        );

        $this->browse(function (Browser $browser) use ($user, $pdf): void {
            $browser->loginAs($user)
                ->visit('/profile')
                ->attach('profile_photo', $pdf->getRealPath())
                ->press('#btnSaveProfile')
                ->waitForText('Format foto profil harus JPG, JPEG, PNG, atau WebP.');
        });

        $this->assertSame($oldPath, $user->fresh()->profile_photo_path);
    }

    public function test_tc10_photo_over_2mb_is_rejected_by_browser(): void
    {
        $user = $this->makeUser();
        $photo = $this->fakePng('large.png', 5120);

        $this->browse(function (Browser $browser) use ($user, $photo): void {
            $browser->loginAs($user)
                ->visit('/profile')
                ->attach('profile_photo', $photo->getRealPath())
                ->waitForDialog()
                ->assertDialogOpened(
                    'Ukuran file foto profil melebihi batas maksimal 2 MB.'
                )
                ->acceptDialog();
        });

        $this->assertNull($user->fresh()->profile_photo_path);
    }

    public function test_tc11_wrong_current_password_is_rejected(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user): void {
            $browser->loginAs($user)
                ->visit('/profile')
                ->type('current_password', 'salah123')
                ->type('password', 'Pass1234')
                ->type('password_confirmation', 'Pass1234')
                ->press('#btnSaveProfile')
                ->waitForText('Password saat ini tidak sesuai.');
        });

        $this->assertTrue(
            Hash::check('PasswordLama123', $user->fresh()->password)
        );
    }

    public function test_tc12_new_password_must_have_at_least_8_characters(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user): void {
            $browser->loginAs($user)
                ->visit('/profile')
                ->type('current_password', 'PasswordLama123')
                ->type('password', 'abc')
                ->type('password_confirmation', 'abc')
                ->press('#btnSaveProfile')
                ->waitForText('Password baru minimal terdiri dari 8 karakter.');
        });

        $this->assertTrue(
            Hash::check('PasswordLama123', $user->fresh()->password)
        );
    }

    public function test_tc13_password_confirmation_must_match(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user): void {
            $browser->loginAs($user)
                ->visit('/profile')
                ->type('current_password', 'PasswordLama123')
                ->type('password', 'Pass1234')
                ->type('password_confirmation', 'Different1')
                ->press('#btnSaveProfile')
                ->waitForText('Konfirmasi password baru tidak cocok.');
        });

        $this->assertTrue(
            Hash::check('PasswordLama123', $user->fresh()->password)
        );
    }

    public function test_tc14_guest_is_redirected_to_login(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/profile')
                ->assertPathIs('/login');
        });
    }

    public function test_tc15_user_cannot_update_another_users_profile(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser([
            'name' => 'Data Milik User Lain',
        ]);

        $this->browse(function (Browser $browser) use ($user, $other): void {
            $browser->loginAs($user)
                ->visit('/profile');

            $browser->script(
                "const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_id';
                input.value = '{$other->id}';
                document.getElementById('name').form.append(input);"
            );

            $browser->clear('name')
                ->type('name', 'Nama Pengguna Login')
                ->press('#btnSaveProfile')
                ->waitForText('Profil berhasil diperbarui.');
        });

        $this->assertSame(
            'Data Milik User Lain',
            $other->fresh()->name
        );

        $this->assertSame(
            'Nama Pengguna Login',
            $user->fresh()->name
        );
    }
}