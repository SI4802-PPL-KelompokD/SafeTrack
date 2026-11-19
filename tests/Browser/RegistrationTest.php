<?php

namespace Tests\Browser;

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RegistrationTest extends DuskTestCase
{
    /**
     * Konfigurasi jeda Slow-Motion (dalam milidetik).
     * Ubah nilai di bawah untuk mempercepat atau memperlambat jalannya test.
     */
    protected const SLOW_MO_FIELD_MS = 450;   // Jeda antar pengisian field
    protected const SLOW_MO_SUBMIT_MS = 600;  // Jeda sebelum menekan tombol submit
    protected const SLOW_MO_RESULT_MS = 1200; // Jeda setelah hasil/error muncul agar mudah diamati

    protected function setUp(): void
    {
        parent::setUp();

        // Bersihkan rate limiter (throttle:6,1) agar 12 test case beruntun tidak terkena HTTP 429
        Cache::flush();

        // Pastikan data demo & NIK DEMO998 siap digunakan
        $this->ensureDemoDataReady();
    }

    /**
     * Memastikan data seed DemoSeeder dan NIK DEMO998 (belum punya akun) tersedia.
     */
    protected function ensureDemoDataReady(): void
    {
        if (! User::where('username', 'admin')->exists()) {
            $this->seed(DemoSeeder::class);
        }

        $nikTable = Schema::hasTable('nik') ? 'nik' : 'employee_registry';
        $statusTable = Schema::hasTable('status_karyawan') ? 'status_karyawan' : 'employment_statuses';

        $opsDeptId = DB::table('departments')->where('code', 'OPS')->value('id')
            ?? DB::table('departments')->value('id');
        $contractStatusId = DB::table($statusTable)->where('code', 'contract')->value('id')
            ?? DB::table($statusTable)->value('id');

        $demo998Id = DB::table($nikTable)->where('nik', 'DEMO998')->value('id');

        if (! $demo998Id) {
            $demo998Id = DB::table($nikTable)->insertGetId([
                'nik' => 'DEMO998',
                'full_name' => 'Calon Karyawan Dusk',
                'department_id' => $opsDeptId,
                'employment_status_id' => $contractStatusId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table($nikTable)->where('id', $demo998Id)->update(['is_active' => true]);
        }

        // Pastikan DEMO998 belum terpakai oleh user dari eksekusi test sebelumnya
        User::withTrashed()->where('employee_registry_id', $demo998Id)->forceDelete();
    }

    /**
     * Helper: Membuka formulir registrasi & mengisi data secara bertahap (slow-mo).
     */
    protected function fillAndSubmitForm(
        Browser $browser,
        string $nik = '',
        string $username = '',
        string $email = '',
        string $password = ''
    ): void {
        $browser->visit('/register')
            ->waitFor('#nik', 10)
            ->pause(self::SLOW_MO_FIELD_MS)
            ->clear('#nik')
            ->clear('#username')
            ->clear('#email')
            ->clear('#password');

        if ($nik !== '') {
            $browser->type('#nik', $nik)
                ->pause(self::SLOW_MO_FIELD_MS);
        }

        if ($username !== '') {
            $browser->type('#username', $username)
                ->pause(self::SLOW_MO_FIELD_MS);
        }

        if ($email !== '') {
            $browser->type('#email', $email)
                ->pause(self::SLOW_MO_FIELD_MS);
        }

        if ($password !== '') {
            $browser->type('#password', $password)
                ->pause(self::SLOW_MO_FIELD_MS);
        }

        $browser->pause(self::SLOW_MO_SUBMIT_MS)
            ->click('button[type="submit"]');
    }

    /**
     * Helper: Mengambil teks error di bawah field tertentu dengan jeda observasi.
     */
    protected function getFieldError(Browser $browser, string $fieldId): string
    {
        $selector = "#error-{$fieldId}";
        $browser->waitFor($selector, 10)
            ->pause(self::SLOW_MO_FIELD_MS);

        return trim($browser->text($selector));
    }

    /**
     * Case 01: Verifikasi Elemen & Atribut Form Registrasi
     */
    public function test_case_01_form_elements_and_attributes_rendered(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->waitFor('#nik', 10)
                ->pause(self::SLOW_MO_FIELD_MS)
                // Verifikasi judul & label
                ->assertSee('REGISTRASI KARYAWAN')
                ->assertSee('Buat akun SafeTrack.')
                // Verifikasi input dan atribut keamanan
                ->assertAttribute('#password', 'type', 'password')
                ->assertAttribute('#password', 'autocomplete', 'new-password')
                // Verifikasi tombol submit & link login
                ->assertSeeIn('button[type="submit"]', 'Buat akun Karyawan')
                ->assertSeeLink('Masuk ke akun')
                ->pause(self::SLOW_MO_RESULT_MS);
        });
    }

    /**
     * Case 02: Validasi Semua Field Wajib Diisi (Form Kosong)
     */
    public function test_case_02_all_required_fields_empty(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmitForm($browser, '', '', '', '');

            $this->assertSame('NIK wajib diisi.', $this->getFieldError($browser, 'nik'));
            $this->assertSame('Username wajib diisi.', $this->getFieldError($browser, 'username'));
            $this->assertSame('Email wajib diisi.', $this->getFieldError($browser, 'email'));
            $this->assertSame('Password wajib diisi.', $this->getFieldError($browser, 'password'));

            // Memastikan seluruh input memiliki class highlight has-error
            foreach (['nik', 'username', 'email', 'password'] as $field) {
                $inputClass = (string) $browser->attribute("#{$field}", 'class');
                $this->assertStringContainsString('has-error', $inputClass);
            }

            $browser->pause(self::SLOW_MO_RESULT_MS);
        });
    }

    /**
     * Case 03: Field NIK Kosong Saja
     */
    public function test_case_03_nik_empty_while_other_fields_filled(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmitForm(
                $browser,
                nik: '',
                username: 'karyawan.ops',
                email: 'karyawan.ops@safetrack.test',
                password: 'Password123!'
            );

            $this->assertSame('NIK wajib diisi.', $this->getFieldError($browser, 'nik'));

            // Field lain tidak memiliki pesan error
            foreach (['username', 'email', 'password'] as $field) {
                $browser->assertMissing("#error-{$field}");
            }

            $browser->pause(self::SLOW_MO_RESULT_MS);
        });
    }

    /**
     * Case 04: NIK Tidak Terdaftar di Registry
     */
    public function test_case_04_nik_not_registered_in_employee_registry(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmitForm(
                $browser,
                nik: 'EMP-999-NOTFOUND',
                username: 'karyawan.baru',
                email: 'baru@safetrack.test',
                password: 'Password123!'
            );

            $errorMsg = $this->getFieldError($browser, 'nik');
            $this->assertSame('NIK tidak terdaftar pada daftar karyawan. Hubungi Admin/HSE.', $errorMsg);

            $browser->pause(self::SLOW_MO_RESULT_MS);
        });
    }

    /**
     * Case 05: NIK Sudah Digunakan oleh Akun Aktif
     */
    public function test_case_05_nik_already_registered_to_an_active_user(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmitForm(
                $browser,
                nik: 'DEMO001',
                username: 'calon.karyawan',
                email: 'calon@safetrack.test',
                password: 'Password123!'
            );

            $errorMsg = $this->getFieldError($browser, 'nik');
            $this->assertSame('NIK sudah digunakan. Silakan masuk ke akun Anda.', $errorMsg);

            $browser->pause(self::SLOW_MO_RESULT_MS);
        });
    }

    /**
     * Case 06: Format Username Tidak Valid
     */
    public function test_case_06_username_invalid_characters(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmitForm(
                $browser,
                nik: 'DEMO998',
                username: 'user spasi!@#',
                email: 'user@safetrack.test',
                password: 'Password123!'
            );

            $errorMsg = $this->getFieldError($browser, 'username');
            $this->assertSame('Format username tidak valid.', $errorMsg);

            $browser->pause(self::SLOW_MO_RESULT_MS);
        });
    }

    /**
     * Case 07: Username Terlalu Pendek (< 3 Karakter)
     */
    public function test_case_07_username_too_short(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmitForm(
                $browser,
                nik: '998',
                username: 'ab',
                email: 'user@safetrack.test',
                password: 'Password123!'
            );

            $errorMsg = $this->getFieldError($browser, 'username');
            $this->assertSame('Username minimal 3 karakter.', $errorMsg);

            $browser->pause(self::SLOW_MO_RESULT_MS);
        });
    }

    /**
     * Case 08: Format Email Tidak Valid
     */
    public function test_case_08_email_invalid_format(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmitForm(
                $browser,
                nik: 'DEMO998',
                username: 'karyawan.valid',
                email: 'bukan-format-email',
                password: 'Password123!'
            );

            $errorMsg = $this->getFieldError($browser, 'email');
            $this->assertSame('Format email tidak valid.', $errorMsg);

            $browser->pause(self::SLOW_MO_RESULT_MS);
        });
    }

    /**
     * Case 09: Email Sudah Terdaftar (Duplikat)
     */
    public function test_case_09_email_already_used_by_another_user(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmitForm(
                $browser,
                nik: 'DEMO998',
                username: 'karyawan.valid',
                email: 'karyawan@safetrack.test', // Email milik akun DEMO003
                password: 'Password123!'
            );

            $errorMsg = $this->getFieldError($browser, 'email');
            $this->assertSame('Email sudah digunakan.', $errorMsg);

            $browser->pause(self::SLOW_MO_RESULT_MS);
        });
    }

    /**
     * Case 10: Password Terlalu Pendek (< 8 Karakter)
     */
    public function test_case_10_password_too_short(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmitForm(
                $browser,
                nik: 'DEMO998',
                username: 'karyawan.valid',
                email: 'user.baru@safetrack.test',
                password: 'rahasia' // 7 karakter
            );

            $errorMsg = $this->getFieldError($browser, 'password');
            $this->assertSame('Password minimal 8 karakter.', $errorMsg);

            $browser->pause(self::SLOW_MO_RESULT_MS);
        });
    }

    /**
     * Case 11: Preservasi Nilai Form & Pengosongan Password pada Error
     */
    public function test_case_11_form_retains_old_inputs_except_password_on_error(): void
    {
        $this->browse(function (Browser $browser) {
            $testNik = 'EMP-999-NOTFOUND';
            $testUser = 'user.retained';
            $testEmail = 'retained@safetrack.test';
            $testPass = 'Password123!';

            $this->fillAndSubmitForm(
                $browser,
                nik: $testNik,
                username: $testUser,
                email: $testEmail,
                password: $testPass
            );

            // Menunggu halaman reload menampilkan error
            $browser->waitFor('#error-nik', 10)
                ->pause(self::SLOW_MO_FIELD_MS)
                // Input teks wajib dipertahankan (old input)
                ->assertInputValue('#nik', $testNik)
                ->assertInputValue('#username', $testUser)
                ->assertInputValue('#email', $testEmail)
                // Password WAJIB kosong demi keamanan
                ->assertInputValue('#password', '')
                ->pause(self::SLOW_MO_RESULT_MS);
        });
    }

    /**
     * Case 12: Registrasi Berhasil & Langsung Diverifikasi Login (End-to-End)
     */
    public function test_case_12_registration_success_and_login_flow(): void
    {
        $this->browse(function (Browser $browser) {
            $uniqueId = time();
            $usernameBaru = "karyawan_{$uniqueId}";
            $emailBaru = "sukses_{$uniqueId}@safetrack.test";
            $passwordBaru = 'PasswordAman2026!';

            // 1. Registrasi akun menggunakan NIK DEMO998 yang belum berakun
            $this->fillAndSubmitForm(
                $browser,
                nik: 'DEMO998',
                username: $usernameBaru,
                email: $emailBaru,
                password: $passwordBaru
            );

            // 2. Verifikasi diarahkan ke halaman /login & 3. Verifikasi flash alert sukses tampil
            $browser->waitForLocation('/login', 10)
                ->waitFor('.auth-alert.success', 10)
                ->pause(self::SLOW_MO_RESULT_MS)
                ->assertSeeIn('.auth-alert.success', 'Registrasi Karyawan berhasil. Silakan masuk.');

            // 4. Uji login menggunakan akun yang baru saja dibuat (slow-mo)
            $browser->clear('#login')
                ->type('#login', $usernameBaru)
                ->pause(self::SLOW_MO_FIELD_MS)
                ->clear('#password')
                ->type('#password', $passwordBaru)
                ->pause(self::SLOW_MO_SUBMIT_MS)
                ->click('#btnSubmitLogin');

            // 5. Memverifikasi berhasil masuk ke dashboard karyawan
            $browser->waitForLocation('/dashboard', 10)
                ->pause(self::SLOW_MO_RESULT_MS)
                ->assertPathIs('/dashboard');
        });
    }
}
