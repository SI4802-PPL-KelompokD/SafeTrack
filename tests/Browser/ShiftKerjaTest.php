<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;


class ShiftKerjaTest extends DuskTestCase
{
    //memastikan baseline data bersih dulu
     
    protected function setUp(): void
    {
        parent::setUp();


        DB::table('shift')->where('name', 'like', 'Shift Malam%')->delete();
        DB::table('shift')->where('name', 'like', 'Shift Uji%')->delete();
        DB::table('shift')->where('name', 'like', 'Shift Tidak%')->delete();
        DB::table('shift')->where('name', 'like', 'Shift Cek%')->delete();


        DB::table('shift')->updateOrInsert(
            ['id' => 51],
            [
                'name' => 'Shift Pagi',
                'code' => 'shift-pagi',
                'starts_at' => '07:00:00',
                'ends_at' => '10:00:00',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );


        DB::table('shift')->updateOrInsert(
            ['id' => 52],
            [
                'name' => 'Shift Siang',
                'code' => 'shift-siang',
                'starts_at' => '10:00:00',
                'ends_at' => '14:00:00',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );


        DB::table('shift')->updateOrInsert(
            ['id' => 53],
            [
                'name' => 'Shift Nonaktif Uji',
                'code' => 'shift-nonaktif-uji',
                'starts_at' => '18:00:00',
                'ends_at' => '22:00:00',
                'is_active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    //Login sebagai Administrator dan navigasi ke halaman Kelola Shift.

    private function navigateToShiftsPage(Browser $browser): Browser
    {
        $browser->driver->manage()->deleteAllCookies();

        $browser->visit('/login')
            ->waitFor('#login', 10)
            ->pause(1500)
            ->clear('login')->pause(500)
            ->type('login', 'admin')->pause(1000)
            ->clear('password')->pause(500)
            ->type('password', 'DemoOnly!2026')->pause(1000)
            ->press('Masuk ke workspace')
            ->waitForLocation('/dashboard', 15)
            ->pause(2500);

        // Navigasi ke halaman kelola shift kerja
        $browser->visit('/admin/shifts')
            ->waitFor('.shift-content', 10)
            ->pause(2500);

        return $browser;
    }

    //TC-01: Admin dapat membuka tab Shift kerja dan melihat daftar data master shift 

    public function test_admin_can_view_shifts_page(): void
    {
        $this->browse(function (Browser $browser) {
            $this->navigateToShiftsPage($browser); 

            // Membaca judul halaman dan deskripsi
            $browser->assertSee('Data master')
                ->assertSee('Shift kerja')
                ->pause(3000);

            // Memeriksa tombol aksi utama
            $browser->assertSee('+ Tambah shift')
                ->pause(3000);

            // Memeriksa isi tabel dan baris shift kerja
            $browser->assertVisible('table')
                ->assertSee('Shift Pagi')
                ->pause(5000);

            // Memeriksa status sistem di footer
            $browser->assertSee('Sistem aktif')
                ->pause(5000)
                ->screenshot('TC01-view-shifts-list')
                ->pause(3000);
        });
    }

    /**
     * TC-02: Admin dapat mencari shift berdasarkan nama (~31 detik).
     */
    public function test_admin_can_search_shift_by_name(): void
    {
        $this->browse(function (Browser $browser) {
            $this->navigateToShiftsPage($browser); // ~11s

            // 1. Mengamati kolom pencarian
            $browser->pause(2000);

            // Mencari nama shift yang ada: "Pagi"
            $browser->clear('search')->pause(500)
                ->type('search', 'Pagi')->pause(1500)
                ->press('Cari')
                ->pause(3000)
                ->assertSee('Shift Pagi')
                ->screenshot('TC02-search-shift-found')
                ->pause(2000);

            // 2. Mencari nama shift yang tidak ada: "Shift Tidak Pernah Ada"
            $browser->clear('search')->pause(500)
                ->type('search', 'Shift Tidak Pernah Ada')->pause(1500)
                ->press('Cari')
                ->pause(3000)
                ->assertSee('Tidak ada shift yang sesuai')
                ->screenshot('TC02-search-shift-empty')
                ->pause(2000);

            // Reset kembali ke daftar penuh
            $browser->clickLink('Reset')
                ->pause(3000);
        });
    }

    /**
     * TC-03: Admin dapat menambah shift kerja baru dengan data valid (~32 detik).
     */
    public function test_admin_can_create_valid_shift(): void
    {
        $this->browse(function (Browser $browser) {
            $this->navigateToShiftsPage($browser); // ~11s

            // Mengamati tombol tambah shift
            $browser->pause(2000);

            // Membuka modal tambah shift
            $browser->click('button[data-open-dialog="create-shift"]')
                ->waitFor('#create-shift', 5)
                ->pause(2500);

            // Mengetik nama shift
            $browser->clear('#add_name')
                ->type('#add_name', 'Shift Malam Dusk')->pause(2000);

            // Memilih rentang jam operasional
            $browser->script("
                document.getElementById('add_starts_at').value = '19:00';
                document.getElementById('add_start_display').value = '19:00';
                document.getElementById('add_ends_at').value = '23:00';
                document.getElementById('add_end_display').value = '23:00';
            ");
            $browser->pause(2500);

            // Mengisi identitas petugas Admin
            $browser->type('#create-shift input[name="admin_name"]', 'Admin Utama')->pause(2000);

            // Mengisi alasan penambahan shift
            $browser->type('#create-shift textarea[name="action_reason"]', 'Penambahan shift operasional malam untuk uji coba otomatis')->pause(3000);

            // Menekan tombol Simpan
            $browser->press('Simpan')
                ->pause(3000)
                ->waitForLocation('/admin/shifts', 10)
                ->assertSee('Shift kerja berhasil ditambahkan.')
                ->assertSee('Shift Malam Dusk')
                ->screenshot('TC03-create-shift-success')
                ->pause(4000);
        });
    }

    /**
     * TC-05: Admin dapat mengubah rincian nama dan jam shift kerja (~31 detik).
     */
    public function test_admin_can_update_shift(): void
    {
        // Siapkan shift khusus target update agar terisolasi sempurna
        $shiftId = DB::table('shift')->insertGetId([
            'name' => 'Shift Uji Sebelum Ubah',
            'code' => 's-edit-'.rand(1000, 9999),
            'starts_at' => '13:00:00',
            'ends_at' => '17:00:00',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($shiftId) {
            $this->navigateToShiftsPage($browser); // ~11s

            // Mengamati tabel dan mencari tombol Ubah untuk shift target
            $browser->pause(2000);
            $browser->waitFor("button[data-id='{$shiftId}']", 5);
            $browser->click("button[data-id='{$shiftId}']")
                ->waitFor('#edit-shift', 5)
                ->pause(2500);

            // Memperbarui nama shift
            $browser->clear('#edit-name')
                ->type('#edit-name', 'Shift Uji Sesudah Ubah')->pause(2000);

            // Memperbarui jam operasional
            $browser->script("
                document.getElementById('edit_starts_at').value = '14:00';
                document.getElementById('edit_start_display').value = '14:00';
                document.getElementById('edit_ends_at').value = '22:00';
                document.getElementById('edit_end_display').value = '22:00';
            ");
            $browser->pause(2500);

            // Mengisi identitas petugas dan alasan perubahan
            $browser->type('#edit-admin-name', 'Admin Utama')->pause(2000)
                ->type('#edit-action-reason', 'Penyesuaian jam operasional shift uji secara valid')->pause(3000);

            // Menyimpan perubahan
            $browser->click('#edit-shift-form button[type="submit"]')
                ->pause(3000)
                ->waitForLocation('/admin/shifts', 10)
                ->assertSee('Shift kerja berhasil diperbarui.')
                ->assertSee('Shift Uji Sesudah Ubah')
                ->screenshot('TC05-update-shift-success')
                ->pause(3000);
        });
    }

    /**
     * TC-06: Admin dapat menonaktifkan shift kerja (Toggle ON -> OFF) (~30 detik).
     */
    public function test_admin_can_deactivate_shift(): void
    {
        $this->browse(function (Browser $browser) {
            $this->navigateToShiftsPage($browser); // ~11s

            // Mengamati baris shift yang aktif (ON)
            $browser->pause(2000);
            $browser->waitFor('.shift-switch.is-on', 5);
            $browser->click('.shift-switch.is-on')
                ->waitFor('#admin-confirm', 5)
                ->pause(2500);

            // Membaca pertanyaan dialog modal konfirmasi
            $browser->assertSee('Nonaktifkan shift kerja?')
                ->pause(2000);

            // Mengisi identitas petugas dan alasan penonaktifan
            $browser->type('#admin-confirm input[name="admin_name"]', 'Admin Utama')->pause(2000)
                ->type('#admin-confirm textarea[name="action_reason"]', 'Penonaktifan sementara untuk pemeliharaan sistem')->pause(3000);

            // Menekan tombol konfirmasi nonaktifkan
            $browser->click('#confirm-submit')
                ->pause(3500)
                ->assertSee('Status shift kerja berhasil diperbarui.')
                ->screenshot('TC06-deactivate-shift-success')
                ->pause(4000);
        });
    }

    /**
     * TC-07: Admin dapat mengaktifkan kembali shift kerja (Toggle OFF -> ON) (~30 detik).
     */
    public function test_admin_can_activate_shift(): void
    {
        $this->browse(function (Browser $browser) {
            $this->navigateToShiftsPage($browser); // ~11s

            // Mengamati baris shift yang nonaktif (OFF)
            $browser->pause(2000);
            $browser->waitFor('.shift-switch:not(.is-on)', 5);
            $browser->click('.shift-switch:not(.is-on)')
                ->waitFor('#admin-confirm', 5)
                ->pause(2500);

            // Membaca pertanyaan dialog modal konfirmasi
            $browser->assertSee('Aktifkan shift kerja?')
                ->pause(2000);

            // Mengisi identitas petugas dan alasan pengaktifan kembali
            $browser->type('#admin-confirm input[name="admin_name"]', 'Admin Utama')->pause(2000)
                ->type('#admin-confirm textarea[name="action_reason"]', 'Pengaktifan kembali shift kerja setelah verifikasi')->pause(3000);

            // Menekan tombol konfirmasi aktifkan
            $browser->click('#confirm-submit')
                ->pause(3500)
                ->assertSee('Status shift kerja berhasil diperbarui.')
                ->screenshot('TC07-activate-shift-success')
                ->pause(4000);
        });
    }

    /**
     * TC-09: Menekan tombol Batal atau ✕ pada modal tidak mengubah data (~30 detik).
     */
    public function test_canceling_modal_does_not_change_data(): void
    {
        $this->browse(function (Browser $browser) {
            $this->navigateToShiftsPage($browser); // ~11s

            // Mengamati halaman sebelum membuka modal
            $browser->pause(2000);

            // Membuka modal tambah shift
            $browser->click('button[data-open-dialog="create-shift"]')
                ->waitFor('#create-shift', 5)
                ->pause(2500);

            // Mengetik nama sementara yang nantinya dibatalkan
            $browser->type('#add_name', 'Shift Tidak Jadi Dibuat')->pause(2500);

            // Menimbang keputusan dan menekan tombol Batal
            $browser->pause(2000);
            $browser->click('#create-shift button[data-close-dialog]')
                ->pause(3000);

            // Memastikan data tidak tersimpan ke dalam tabel
            $browser->visit('/admin/shifts')
                ->pause(3000)
                ->assertDontSee('Shift Tidak Jadi Dibuat')
                ->screenshot('TC09-cancel-modal')
                ->pause(4000);
        });
    }

    /**
     * TC-11: Sistem menolak penambahan shift dengan nama duplikat (~31 detik).
     */
    public function test_shift_creation_rejects_duplicate_name(): void
    {
        $this->browse(function (Browser $browser) {
            $this->navigateToShiftsPage($browser); // ~11s

            // Mengamati halaman sebelum membuka modal
            $browser->pause(2000);

            // Membuka modal tambah shift
            $browser->click('button[data-open-dialog="create-shift"]')
                ->waitFor('#create-shift', 5)
                ->pause(2500);

            // Mengisi dengan nama yang sudah pasti ada: "Shift Pagi"
            $browser->clear('#add_name')
                ->type('#add_name', 'Shift Pagi')->pause(2000);

            // Mengisi jam operasional
            $browser->script("
                document.getElementById('add_starts_at').value = '08:00';
                document.getElementById('add_ends_at').value = '16:00';
            ");
            $browser->pause(2000);

            // Mengisi identitas petugas dan alasan
            $browser->type('#create-shift input[name="admin_name"]', 'Admin Utama')->pause(2000)
                ->type('#create-shift textarea[name="action_reason"]', 'Mencoba membuat shift dengan nama kembar')->pause(2500);

            // Menekan tombol Simpan
            $browser->press('Simpan')
                ->pause(3000)
                ->assertSee('Nama shift sudah ada.')
                ->screenshot('TC11-duplicate-name-rejected')
                ->pause(4000);
        });
    }

    /**
     * TC-15: Sistem menolak alasan tindakan jika kurang dari 10 karakter (~30 detik).
     */
    public function test_shift_action_requires_minimum_reason_length(): void
    {
        $this->browse(function (Browser $browser) {
            $this->navigateToShiftsPage($browser); // ~11s

            // Mengamati halaman
            $browser->pause(2000);

            // Membuka modal tambah shift
            $browser->click('button[data-open-dialog="create-shift"]')
                ->waitFor('#create-shift', 5)
                ->pause(2500);

            // Mengisi form dengan alasan yang terlalu pendek (< 10 karakter)
            $browser->type('#add_name', 'Shift Cek Alasan')->pause(2000)
                ->type('#create-shift input[name="admin_name"]', 'Admin Utama')->pause(2000)
                ->type('#create-shift textarea[name="action_reason"]', 'Pendek') // 6 karakter
                ->pause(2500);

            // Menekan tombol Simpan
            $browser->press('Simpan')
                ->pause(3000)
                // Modal tetap terbuka karena validasi HTML5 minlength mencegah submit
                ->assertVisible('#create-shift')
                ->screenshot('TC15-reason-min-length-rejected')
                ->pause(5000);
        });
    }
}
