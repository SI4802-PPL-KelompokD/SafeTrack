<?php

namespace Tests\Browser;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SapMonitoringTest extends DuskTestCase
{
    private const STEP_PAUSE_MS = 1500;

    private const READ_PAUSE_MS = 2500;

    private const SECTION_PAUSE_MS = 3000;

    protected function setUp(): void
    {
        parent::setUp();

        // Bersihkan data SAP test sebelumnya
        DB::table('sap_log_perhitungan')->delete();
        DB::table('pencapaian')->delete();
        DB::table('history_sap')->delete();
        DB::table('hazard_reports')->delete();

        // Pastikan admin user aktif ada
        $adminId = DB::table('users')->where('username', 'admin')->value('id');
        if (! $adminId) {
            $adminId = DB::table('users')->insertGetId([
                'name' => 'Admin SafeTrack',
                'username' => 'admin',
                'email' => 'admin@safetrack.test',
                'password' => bcrypt('DemoOnly!2026'),
                'role' => 'super_admin',
                'department_id' => $this->ensureDepartment('ADM', 'Administrasi', false),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Pastikan departemen OPS (non-HSE) ada
        $opsDeptId = $this->ensureDepartment('OPS', 'Operasional', false);

        // Pastikan karyawan non-HSE aktif ada
        $employeeId = DB::table('users')->where('username', 'karyawan')->value('id');
        if (! $employeeId) {
            $employeeId = DB::table('users')->insertGetId([
                'name' => 'Karyawan Demo',
                'username' => 'karyawan',
                'email' => 'karyawan@safetrack.test',
                'password' => bcrypt('DemoOnly!2026'),
                'role' => 'employee',
                'department_id' => $opsDeptId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')->where('id', $employeeId)->update([
                'department_id' => $opsDeptId,
                'is_active' => true,
            ]);
        }

        // Pastikan SAP point rules ada
        $this->ensurePointRules();
    }

    private function ensureDepartment(string $code, string $name, bool $isHse): int
    {
        $dept = DB::table('departments')->where('code', $code)->first();
        if ($dept) {
            return $dept->id;
        }

        return DB::table('departments')->insertGetId([
            'code' => $code,
            'name' => $name,
            'is_hse' => $isHse,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensurePointRules(): void
    {
        $activities = ['hazard_verified', 'inspection_reviewed', 'safety_talk_published', 'resolution_accepted'];
        foreach ($activities as $activity) {
            DB::table('sap_point_rules')->updateOrInsert(
                ['activity' => $activity, 'effective_from' => '2020-01-01'],
                ['points' => 1, 'effective_until' => null, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    private function loginAsAdmin(Browser $browser): Browser
    {
        $browser->driver->manage()->deleteAllCookies();

        return $browser->visit('/login')
            ->waitFor('#login', 10)
            ->pause(self::STEP_PAUSE_MS)
            ->clear('login')->pause(500)
            ->type('login', 'admin')->pause(1000)
            ->clear('password')->pause(500)
            ->type('password', 'DemoOnly!2026')->pause(1000)
            ->press('Masuk ke workspace')
            ->waitForLocation('/dashboard', 15)
            ->pause(self::READ_PAUSE_MS);
    }

    private function navigateToSapPage(Browser $browser): Browser
    {
        return $this->loginAsAdmin($browser)
            ->visit('/sap')
            ->waitFor('.page-title', 10)
            ->pause(self::READ_PAUSE_MS);
    }

    private function seedHazardReportForEmployee(int $employeeId, int $deptId): int
    {
        $now = Carbon::now();
        $categoryId = DB::table('kategori_temuan')->value('id')
            ?? DB::table('kategori_temuan')->insertGetId([
                'code' => 'GEN',
                'name' => 'Umum',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        $areaId = DB::table('area_kerja')->value('id')
            ?? DB::table('area_kerja')->insertGetId([
                'name' => 'Area Produksi',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

        return DB::table('hazard_reports')->insertGetId([
            'client_request_id' => (string) Str::uuid(),
            'reporter_id' => $employeeId,
            'reporter_department_id' => $deptId,
            'category_id' => $categoryId,
            'work_area_id' => $areaId,
            'title' => 'Kabel Terkelupas Dusk Test',
            'location' => 'Lantai 1',
            'description' => 'Deskripsi hazard untuk testing SAP',
            'risk_level' => 'low',
            'status' => 'open',
            'verified_at' => $now,
            'reported_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    // ──────────────────────────────────────────────────────
    // TC-01: Admin membuka halaman Pencapaian SAP
    // UC-28 Standard Process langkah 1-2
    // ──────────────────────────────────────────────────────
    public function test_tc01_admin_can_open_sap_page_and_see_elements(): void
    {
        $this->browse(function (Browser $browser) {
            $this->navigateToSapPage($browser);

            // Melihat judul halaman
            $browser->waitForText('Pencapaian SAP Karyawan', 10)
                ->assertSee('Pencapaian SAP Karyawan')
                ->pause(self::READ_PAUSE_MS);

            // Melihat tag Safety Achievement Points (CSS text-transform: uppercase)
            $browser->assertPresent('.page-tag')
                ->assertSeeIn('.page-tag', 'SAFETY ACHIEVEMENT POINTS')
                ->pause(self::STEP_PAUSE_MS);

            // Melihat stats grid (4 kartu statistik)
            $browser->assertSee('Total Poin Terdistribusi')
                ->assertSee('Karyawan Berpartisipasi')
                ->assertSee('Rata-rata Poin')
                ->assertSee('Konversi Terpenuhi')
                ->pause(self::READ_PAUSE_MS);

            // Melihat tombol Hitung Ulang SAP
            $browser->assertSee('Hitung Ulang SAP')
                ->pause(self::STEP_PAUSE_MS);

            // Melihat filter periode
            $browser->assertPresent('#selectMonth')
                ->assertPresent('#selectYear')
                ->pause(self::READ_PAUSE_MS);

            // Melihat Top 3 Non-HSE section
            $browser->assertSee('Top 3 Karyawan Non-HSE')
                ->pause(self::SECTION_PAUSE_MS)
                ->screenshot('TC01-sap-page-overview');
        });
    }

    // ──────────────────────────────────────────────────────
    // TC-02: Admin melihat label "belum pernah diperbarui"
    // UC-28 Standard Process langkah 2 (empty state)
    // ──────────────────────────────────────────────────────
    public function test_tc02_admin_sees_belum_pernah_label_when_no_data(): void
    {
        $this->browse(function (Browser $browser) {
            $this->navigateToSapPage($browser);

            // Label waktu terakhir menunjukkan "belum pernah diperbarui"
            $browser->assertSee('Terakhir diperbarui:')
                ->assertSee('belum pernah diperbarui')
                ->pause(self::READ_PAUSE_MS);

            // Tabel Non-HSE menampilkan empty state
            $browser->assertSee('Belum ada kontribusi Non-HSE pada periode ini')
                ->pause(self::READ_PAUSE_MS);

            // Statistik menunjukkan 0
            $browser->assertSeeIn('#statTotalPoints', '0')
                ->pause(self::SECTION_PAUSE_MS)
                ->screenshot('TC02-sap-empty-state');
        });
    }

    // ──────────────────────────────────────────────────────
    // TC-03: Admin menekan tombol Refresh dan berhasil
    // UC-28 Standard Process langkah 3-6
    // ──────────────────────────────────────────────────────
    public function test_tc03_admin_can_refresh_sap_and_see_success(): void
    {
        // Siapkan data sumber: hazard report milik karyawan
        $employeeId = DB::table('users')->where('username', 'karyawan')->value('id');
        $deptId = DB::table('users')->where('id', $employeeId)->value('department_id');
        $this->seedHazardReportForEmployee($employeeId, $deptId);

        $this->browse(function (Browser $browser) {
            $this->navigateToSapPage($browser);

            // Langkah 3: Admin menekan tombol Refresh
            $browser->assertSee('Hitung Ulang SAP')
                ->pause(self::STEP_PAUSE_MS);

            $browser->press('#btnRecalculateSap')
                ->pause(1000);

            // Dialog konfirmasi muncul
            $browser->waitForDialog()
                ->acceptDialog()
                ->pause(self::STEP_PAUSE_MS);

            // Langkah 5-6: Menunggu redirect dan pesan sukses
            $browser->waitForText('Rekap pencapaian SAP berhasil diperbarui', 15)
                ->pause(self::READ_PAUSE_MS);

            // Pesan sukses tampil
            $browser->assertSee('Rekap pencapaian SAP berhasil diperbarui')
                ->pause(self::READ_PAUSE_MS);

            // Waktu terakhir diperbarui sudah tidak "belum pernah" lagi
            $browser->assertDontSee('belum pernah diperbarui')
                ->pause(self::SECTION_PAUSE_MS)
                ->screenshot('TC03-sap-refresh-success');
        });
    }

    // ──────────────────────────────────────────────────────
    // TC-04: Admin melihat Top 3 Non-HSE setelah refresh
    // UC-28 Standard Process langkah 6
    // ──────────────────────────────────────────────────────
    public function test_tc04_admin_sees_top3_nonhse_after_refresh(): void
    {
        // Siapkan data: 2 hazard report untuk karyawan
        $employeeId = DB::table('users')->where('username', 'karyawan')->value('id');
        $deptId = DB::table('users')->where('id', $employeeId)->value('department_id');
        $this->seedHazardReportForEmployee($employeeId, $deptId);

        // Tambah satu hazard report lagi
        $now = Carbon::now();
        $categoryId = DB::table('kategori_temuan')->value('id');
        $areaId = DB::table('area_kerja')->value('id');
        DB::table('hazard_reports')->insert([
            'client_request_id' => (string) Str::uuid(),
            'reporter_id' => $employeeId,
            'reporter_department_id' => $deptId,
            'category_id' => $categoryId,
            'work_area_id' => $areaId,
            'title' => 'Genangan Air Dusk Test',
            'location' => 'Gudang',
            'description' => 'Hazard kedua untuk testing SAP',
            'risk_level' => 'medium',
            'status' => 'open',
            'verified_at' => $now,
            'reported_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->browse(function (Browser $browser) {
            $this->navigateToSapPage($browser);

            // Tekan refresh
            $browser->press('#btnRecalculateSap')
                ->pause(1000)
                ->waitForDialog()
                ->acceptDialog()
                ->waitForText('Rekap pencapaian SAP berhasil diperbarui', 15)
                ->pause(self::READ_PAUSE_MS);

            // Melihat karyawan di Top 3 dengan poin
            $browser->assertSee('Karyawan Demo')
                ->pause(self::STEP_PAUSE_MS);

            // Melihat total poin (2 hazard = 2 pts)
            $browser->assertSee('2 pts')
                ->pause(self::READ_PAUSE_MS);

            // Kolom Terakhir Dihitung per baris berisi tanggal
            $browser->assertSee(now()->format('d/m/Y'))
                ->pause(self::SECTION_PAUSE_MS)
                ->screenshot('TC04-sap-top3-nonhse');
        });
    }

    // ──────────────────────────────────────────────────────
    // TC-05: Admin menggunakan filter periode bulan/tahun
    // ──────────────────────────────────────────────────────
    public function test_tc05_admin_can_filter_by_period(): void
    {
        $this->browse(function (Browser $browser) {
            $this->navigateToSapPage($browser);

            // Memilih bulan sebelumnya
            $prevMonth = Carbon::now()->subMonth();
            $browser->select('#selectMonth', $prevMonth->month)
                ->pause(self::STEP_PAUSE_MS);

            $browser->select('#selectYear', $prevMonth->year)
                ->pause(self::STEP_PAUSE_MS);

            // Tekan tombol Tampilkan
            $browser->press('#btnFilterSubmit')
                ->pause(self::READ_PAUSE_MS);

            // Halaman dimuat ulang dengan periode baru
            $browser->waitFor('.page-title', 10)
                ->pause(self::READ_PAUSE_MS);

            // Empty state karena tidak ada data di bulan sebelumnya
            $browser->assertSee('Belum ada kontribusi Non-HSE pada periode ini')
                ->pause(self::SECTION_PAUSE_MS)
                ->screenshot('TC05-sap-filter-period');
        });
    }

    // ──────────────────────────────────────────────────────
    // TC-06: Admin mencari karyawan berdasarkan nama
    // ──────────────────────────────────────────────────────
    public function test_tc06_admin_can_search_employee(): void
    {
        // Siapkan data SAP agar ada hasil
        $employeeId = DB::table('users')->where('username', 'karyawan')->value('id');
        $deptId = DB::table('users')->where('id', $employeeId)->value('department_id');
        $this->seedHazardReportForEmployee($employeeId, $deptId);

        // Buat summary langsung agar muncul di Top 3
        DB::table('history_sap')->insert([
            'user_id' => $employeeId,
            'period' => Carbon::now()->startOfMonth()->format('Y-m-01'),
            'department_id' => $deptId,
            'is_hse_snapshot' => false,
            'hazard_count' => 1,
            'inspection_count' => 0,
            'resolution_count' => 0,
            'safety_talk_count' => 0,
            'total_points' => 1,
            'calculated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->browse(function (Browser $browser) {
            $this->navigateToSapPage($browser);

            // Ketik nama karyawan di kolom pencarian
            $browser->type('#inputSearch', 'Karyawan Demo')
                ->pause(self::STEP_PAUSE_MS);

            // Tekan Tampilkan
            $browser->press('#btnFilterSubmit')
                ->pause(self::READ_PAUSE_MS);

            // Hasil pencarian menampilkan karyawan yang dicari
            $browser->waitFor('.page-title', 10)
                ->assertSee('Karyawan Demo')
                ->pause(self::READ_PAUSE_MS);

            // Ketik pencarian yang tidak ada
            $browser->clear('#inputSearch')
                ->pause(500)
                ->type('#inputSearch', 'XYZTidakAdaNama')
                ->pause(self::STEP_PAUSE_MS);

            $browser->press('#btnFilterSubmit')
                ->pause(self::READ_PAUSE_MS);

            // Tidak ada hasil
            $browser->waitFor('.page-title', 10)
                ->assertSee('Belum ada kontribusi Non-HSE pada periode ini')
                ->pause(self::SECTION_PAUSE_MS)
                ->screenshot('TC06-sap-search');
        });
    }

    // ──────────────────────────────────────────────────────
    // TC-07: Karyawan tidak dapat mengakses halaman SAP
    // UC-28 Precondition (hanya Admin)
    // ──────────────────────────────────────────────────────
    public function test_tc07_employee_cannot_access_sap_page(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();

            // Login sebagai karyawan
            $browser->visit('/login')
                ->waitFor('#login', 10)
                ->pause(self::STEP_PAUSE_MS)
                ->clear('login')->pause(500)
                ->type('login', 'karyawan')->pause(1000)
                ->clear('password')->pause(500)
                ->type('password', 'DemoOnly!2026')->pause(1000)
                ->press('Masuk ke workspace')
                ->waitForLocation('/dashboard', 15)
                ->pause(self::READ_PAUSE_MS);

            // Coba akses halaman SAP langsung
            $browser->visit('/sap')
                ->pause(self::READ_PAUSE_MS);

            // Harus melihat 403 atau redirect, bukan halaman SAP
            $browser->assertDontSee('Pencapaian SAP Karyawan')
                ->pause(self::SECTION_PAUSE_MS)
                ->screenshot('TC07-sap-employee-denied');
        });
    }

    // ──────────────────────────────────────────────────────
    // TC-08: Guest diredirect ke halaman login
    // UC-28 Precondition (harus login)
    // ──────────────────────────────────────────────────────
    public function test_tc08_guest_redirected_to_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();

            // Akses halaman SAP tanpa login
            $browser->visit('/sap')
                ->pause(self::READ_PAUSE_MS);

            // Harus diredirect ke login
            $browser->waitForLocation('/login', 10)
                ->assertPathIs('/login')
                ->pause(self::READ_PAUSE_MS);

            $browser->assertSee('Masuk ke workspace')
                ->pause(self::SECTION_PAUSE_MS)
                ->screenshot('TC08-sap-guest-redirect');
        });
    }

    // ──────────────────────────────────────────────────────
    // TC-09: Admin melihat pesan sukses sesuai proposal
    // UC-28 Standard Process langkah 6 (pesan)
    // ──────────────────────────────────────────────────────
    public function test_tc09_admin_refresh_shows_correct_success_message(): void
    {
        // Siapkan data sumber
        $employeeId = DB::table('users')->where('username', 'karyawan')->value('id');
        $deptId = DB::table('users')->where('id', $employeeId)->value('department_id');
        $this->seedHazardReportForEmployee($employeeId, $deptId);

        $this->browse(function (Browser $browser) {
            $this->navigateToSapPage($browser);

            // Tekan tombol refresh
            $browser->press('#btnRecalculateSap')
                ->pause(1000)
                ->waitForDialog()
                ->acceptDialog()
                ->pause(self::STEP_PAUSE_MS);

            // Pesan sukses sesuai proposal: "Rekap pencapaian SAP berhasil diperbarui."
            $browser->waitForText('Rekap pencapaian SAP berhasil diperbarui', 15)
                ->assertSee('Rekap pencapaian SAP berhasil diperbarui')
                ->pause(self::READ_PAUSE_MS);

            // Waktu terakhir diperbarui sudah tercatat (format dd/mm/yyyy)
            $browser->assertSee(now()->format('d/m/Y'))
                ->pause(self::SECTION_PAUSE_MS)
                ->screenshot('TC09-sap-success-message');
        });
    }
}
