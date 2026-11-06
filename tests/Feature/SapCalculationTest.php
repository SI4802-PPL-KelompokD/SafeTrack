<?php

namespace Tests\Feature;

use App\Models\DailyInspection;
use App\Models\Department;
use App\Models\HazardCategory;
use App\Models\HazardReport;
use App\Models\HazardResolution;
use App\Models\Reward;
use App\Models\SafetyTalk;
use App\Models\SapEntry;
use App\Models\SapMonthlySummary;
use App\Models\SapPointRule;
use App\Models\SapTarget;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkArea;
use App\Services\SapCalculationService;
use Carbon\Carbon;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SapCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected SapCalculationService $sapService;
    protected User $adminUser;
    protected User $employeeUser;
    protected Department $opsDept;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(DemoSeeder::class);

        $this->sapService = app(SapCalculationService::class);
        $this->adminUser = User::where('username', 'admin')->first();
        $this->employeeUser = User::where('username', 'karyawan')->first();
        $this->opsDept = Department::where('code', 'OPS')->first();
    }

    /**
     * UC-25: Perhitungan poin SAP otomatis dari 4 sumber kegiatan valid.
     */
    public function test_sap_service_calculates_points_from_all_valid_sources(): void
    {
        $now = Carbon::now();
        $periodDate = $now->copy()->startOfMonth()->format('Y-m-01');

        $category = HazardCategory::first() ?? HazardCategory::create([
            'code' => 'GEN',
            'name' => 'Umum',
            'is_active' => true,
        ]);
        $area = WorkArea::first();
        $shift = Shift::first();

        // 1. Buat Hazard Report valid milik karyawan
        $hazard = HazardReport::create([
            'client_request_id' => (string) Str::uuid(),
            'reporter_id' => $this->employeeUser->id,
            'reporter_department_id' => $this->opsDept->id,
            'category_id' => $category->id,
            'work_area_id' => $area->id,
            'title' => 'Kabel Terkelupas di Area Mesin',
            'location' => 'Lantai 1 Bengkel',
            'description' => 'Terdapat kabel terkelupas dekat mesin bubut',
            'risk_level' => 'low',
            'status' => 'open',
            'verified_at' => $now,
            'reported_at' => $now,
        ]);

        // 2. Buat Daily Inspection valid
        $inspection = DailyInspection::create([
            'client_request_id' => (string) Str::uuid(),
            'inspector_id' => $this->employeeUser->id,
            'department_id' => $this->opsDept->id,
            'work_area_id' => $area->id,
            'shift_id' => $shift->id,
            'inspection_date' => $now->toDateString(),
            'status' => 'reviewed',
            'reviewed_at' => $now,
        ]);

        // 3. Buat Safety Talk terverifikasi dan karyawan hadir sebagai attendee
        $talk = SafetyTalk::create([
            'client_request_id' => (string) Str::uuid(),
            'created_by' => $this->adminUser->id,
            'department_id' => $this->opsDept->id,
            'topic' => 'Pentingnya Pemakaian Helm dan APD',
            'content' => 'Materi briefing APD harian.',
            'status' => 'published',
            'held_at' => $now,
            'published_at' => $now,
            'verified_at' => $now,
        ]);
        DB::table('safety_talk_attendees')->insert([
            'safety_talk_id' => $talk->id,
            'user_id' => $this->employeeUser->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 4. Buat Hazard Resolution yang accepted
        $resolution = HazardResolution::create([
            'hazard_report_id' => $hazard->id,
            'submitted_by' => $this->employeeUser->id,
            'department_id' => $this->opsDept->id,
            'client_request_id' => (string) Str::uuid(),
            'description' => 'Kabel sudah diisolasi dan dirapikan.',
            'status' => 'accepted',
            'reviewed_at' => $now,
            'submitted_at' => $now,
        ]);

        // Jalankan perhitungan otomatis UC-25
        $summary = $this->sapService->calculateForUser($this->employeeUser, $now);

        $this->assertInstanceOf(SapMonthlySummary::class, $summary);
        $this->assertEquals($this->employeeUser->id, $summary->user_id);
        $this->assertEquals($periodDate, $summary->period->format('Y-m-01'));
        $this->assertEquals(1, $summary->hazard_count);
        $this->assertEquals(1, $summary->inspection_count);
        $this->assertEquals(1, $summary->safety_talk_count);
        $this->assertEquals(1, $summary->resolution_count);
        $this->assertEquals(4, $summary->total_points);

        // Pastikan ledger sap_entries mencatat 4 baris unik
        $this->assertDatabaseCount('pencapaian', 4);
        $this->assertDatabaseHas('pencapaian', [
            'user_id' => $this->employeeUser->id,
            'activity' => 'hazard_verified',
            'source_id' => $hazard->id,
            'is_void' => false,
        ]);
        $this->assertDatabaseHas('pencapaian', [
            'user_id' => $this->employeeUser->id,
            'activity' => 'inspection_reviewed',
            'source_id' => $inspection->id,
            'is_void' => false,
        ]);
        $this->assertDatabaseHas('pencapaian', [
            'user_id' => $this->employeeUser->id,
            'activity' => 'safety_talk_published',
            'source_id' => $talk->id,
            'is_void' => false,
        ]);
        $this->assertDatabaseHas('pencapaian', [
            'user_id' => $this->employeeUser->id,
            'activity' => 'resolution_accepted',
            'source_id' => $resolution->id,
            'is_void' => false,
        ]);
    }

    /**
     * UC-25: Idempotensi - Perhitungan berulang tidak menduplikasi poin atau baris ledger.
     */
    public function test_sap_calculation_is_idempotent(): void
    {
        $now = Carbon::now();
        $category = HazardCategory::first() ?? HazardCategory::create(['code' => 'GEN', 'name' => 'Umum', 'is_active' => true]);
        $area = WorkArea::first();

        HazardReport::create([
            'client_request_id' => (string) Str::uuid(),
            'reporter_id' => $this->employeeUser->id,
            'reporter_department_id' => $this->opsDept->id,
            'category_id' => $category->id,
            'work_area_id' => $area->id,
            'title' => 'Tumpahan Oli',
            'location' => 'Lantai 1',
            'description' => 'Oli tumpah',
            'risk_level' => 'low',
            'status' => 'open',
            'verified_at' => $now,
            'reported_at' => $now,
        ]);

        // Hitung pertama kali
        $firstSummary = $this->sapService->calculateForUser($this->employeeUser, $now);
        $this->assertEquals(1, $firstSummary->total_points);
        $this->assertDatabaseCount('pencapaian', 1);

        // Hitung kedua kali (re-run)
        $secondSummary = $this->sapService->calculateForUser($this->employeeUser, $now);
        $this->assertEquals(1, $secondSummary->total_points);
        $this->assertDatabaseCount('pencapaian', 1);
        $this->assertDatabaseCount('history_sap', 1);
    }

    /**
     * UC-25: Evaluasi target SAP (target_met).
     */
    /**
     * UC-26: Karyawan Non-HSE dikonversi menjadi berhak atas reward saat poin mencapai ambang.
     */
    public function test_uc26_non_hse_employee_converted_to_reward_when_points_reach_threshold(): void
    {
        $now = Carbon::now();
        $category = HazardCategory::first() ?? HazardCategory::create(['code' => 'GEN', 'name' => 'Umum', 'is_active' => true]);
        $area = WorkArea::first();

        // Buat aturan konversi reward (UC-27): Sepeda (ambang 2 poin)
        $reward = Reward::create([
            'name' => 'Hadiah Sepeda',
            'required_points' => 2,
            'is_active' => true,
        ]);

        // Karyawan non-HSE memperoleh 2 hazard report (2 poin)
        for ($i = 1; $i <= 2; $i++) {
            HazardReport::create([
                'client_request_id' => (string) Str::uuid(),
                'reporter_id' => $this->employeeUser->id,
                'reporter_department_id' => $this->opsDept->id,
                'category_id' => $category->id,
                'work_area_id' => $area->id,
                'title' => "Hazard Non-HSE {$i}",
                'location' => 'Area Produksi',
                'description' => "Desc {$i}",
                'risk_level' => 'low',
                'status' => 'open',
                'verified_at' => $now,
                'reported_at' => $now,
            ]);
        }

        $summary = $this->sapService->calculateForUser($this->employeeUser, $now);

        $this->assertEquals(2, $summary->total_points);
        $this->assertFalse($summary->is_hse_snapshot);
        $this->assertTrue($summary->reward_eligible); // Tertandai berhak atas reward
        $this->assertEquals($reward->id, $summary->eligible_reward_id);
        $this->assertNull($summary->target_met); // Non-HSE tidak dikonversi ke target KPI
    }

    /**
     * UC-26: Karyawan Non-HSE belum berhak atas reward jika poin belum mencapai ambang (Alur Alternatif 3').
     */
    public function test_uc26_non_hse_employee_not_eligible_when_points_below_threshold(): void
    {
        $now = Carbon::now();
        $category = HazardCategory::first() ?? HazardCategory::create(['code' => 'GEN', 'name' => 'Umum', 'is_active' => true]);
        $area = WorkArea::first();

        // Aturan reward ambang 10 poin
        Reward::create([
            'name' => 'Hadiah Sepeda',
            'required_points' => 10,
            'is_active' => true,
        ]);

        // Karyawan hanya memiliki 1 poin
        HazardReport::create([
            'client_request_id' => (string) Str::uuid(),
            'reporter_id' => $this->employeeUser->id,
            'reporter_department_id' => $this->opsDept->id,
            'category_id' => $category->id,
            'work_area_id' => $area->id,
            'title' => 'Hazard 1 pt',
            'location' => 'Lantai 1',
            'description' => 'Desc',
            'risk_level' => 'low',
            'status' => 'open',
            'verified_at' => $now,
            'reported_at' => $now,
        ]);

        $summary = $this->sapService->calculateForUser($this->employeeUser, $now);

        $this->assertEquals(1, $summary->total_points);
        $this->assertFalse($summary->reward_eligible); // Belum mencapai ambang 10 poin
        $this->assertNull($summary->eligible_reward_id);
    }

    /**
     * UC-26: Karyawan Non-HSE tidak dikonversi jika belum ada aturan reward aktif (Alur Alternatif 2').
     */
    public function test_uc26_non_hse_employee_no_conversion_when_no_active_rewards(): void
    {
        $now = Carbon::now();
        $category = HazardCategory::first() ?? HazardCategory::create(['code' => 'GEN', 'name' => 'Umum', 'is_active' => true]);
        $area = WorkArea::first();

        // Karyawan memiliki 5 poin, tetapi tabel rewards kosong
        for ($i = 1; $i <= 5; $i++) {
            HazardReport::create([
                'client_request_id' => (string) Str::uuid(),
                'reporter_id' => $this->employeeUser->id,
                'reporter_department_id' => $this->opsDept->id,
                'category_id' => $category->id,
                'work_area_id' => $area->id,
                'title' => "Hazard {$i}",
                'location' => 'Lantai 1',
                'description' => "Desc {$i}",
                'risk_level' => 'low',
                'status' => 'open',
                'verified_at' => $now,
                'reported_at' => $now,
            ]);
        }

        $summary = $this->sapService->calculateForUser($this->employeeUser, $now);

        $this->assertEquals(5, $summary->total_points);
        $this->assertFalse($summary->reward_eligible);
        $this->assertNull($summary->eligible_reward_id);
    }

    /**
     * UC-26: Karyawan HSE dikonversi menjadi target_met = true saat poin mencapai ambang aturan konversi.
     */
    public function test_uc26_hse_employee_converted_to_target_met_when_points_reach_threshold(): void
    {
        $now = Carbon::now();
        $periodDate = $now->copy()->startOfMonth()->format('Y-m-01');
        $hseUser = User::where('username', 'hse')->first();
        $hseDept = Department::where('code', 'HSE')->first();
        $category = HazardCategory::first() ?? HazardCategory::create(['code' => 'GEN', 'name' => 'Umum', 'is_active' => true]);
        $area = WorkArea::first();

        // Aturan konversi HSE (UC-27 / PBI-23): target terpenuhi jika mencapai ambang 2 poin
        SapPointRule::create([
            'activity' => 'hse_target_threshold',
            'points' => 2,
            'effective_from' => '2026-01-01',
        ]);

        // Karyawan HSE memperoleh 2 hazard report (2 poin)
        for ($i = 1; $i <= 2; $i++) {
            HazardReport::create([
                'client_request_id' => (string) Str::uuid(),
                'reporter_id' => $hseUser->id,
                'reporter_department_id' => $hseDept->id,
                'category_id' => $category->id,
                'work_area_id' => $area->id,
                'title' => "Hazard HSE {$i}",
                'location' => 'Area Kantor',
                'description' => "Desc {$i}",
                'risk_level' => 'low',
                'status' => 'open',
                'verified_at' => $now,
                'reported_at' => $now,
            ]);
        }

        $summary = $this->sapService->calculateForUser($hseUser, $now);

        $this->assertEquals(2, $summary->total_points);
        $this->assertTrue($summary->is_hse_snapshot);
        $this->assertTrue($summary->target_met); // Target KPI terpenuhi (2 >= 2)
        $this->assertFalse($summary->reward_eligible); // HSE tidak dikonversi ke reward barang
    }

    /**
     * UC-26: Karyawan HSE target_met = false jika poin belum mencapai ambang aturan konversi (Alur Alternatif 3').
     */
    public function test_uc26_hse_employee_target_not_met_when_points_below_threshold(): void
    {
        $now = Carbon::now();
        $hseUser = User::where('username', 'hse')->first();
        $hseDept = Department::where('code', 'HSE')->first();
        $category = HazardCategory::first() ?? HazardCategory::create(['code' => 'GEN', 'name' => 'Umum', 'is_active' => true]);
        $area = WorkArea::first();

        // Aturan konversi HSE membutuhkan 10 poin
        SapPointRule::create([
            'activity' => 'hse_target_threshold',
            'points' => 10,
            'effective_from' => '2026-01-01',
        ]);

        // Karyawan HSE hanya memperoleh 1 hazard report (1 poin)
        HazardReport::create([
            'client_request_id' => (string) Str::uuid(),
            'reporter_id' => $hseUser->id,
            'reporter_department_id' => $hseDept->id,
            'category_id' => $category->id,
            'work_area_id' => $area->id,
            'title' => 'Hazard HSE 1 pt',
            'location' => 'Area Kantor',
            'description' => 'Desc',
            'risk_level' => 'low',
            'status' => 'open',
            'verified_at' => $now,
            'reported_at' => $now,
        ]);

        $summary = $this->sapService->calculateForUser($hseUser, $now);

        $this->assertEquals(1, $summary->total_points);
        $this->assertTrue($summary->is_hse_snapshot);
        $this->assertFalse($summary->target_met); // Target belum terpenuhi (1 < 10)
    }

    /**
     * UC-26: Karyawan HSE tidak dikonversi jika aturan konversi belum ditetapkan (Alur Alternatif 2').
     * Sistem tidak mengubah target_met menjadi true jika aturan belum ada.
     */
    public function test_uc26_hse_employee_no_conversion_when_rule_not_configured(): void
    {
        $now = Carbon::now();
        $hseUser = User::where('username', 'hse')->first();
        $hseDept = Department::where('code', 'HSE')->first();
        $category = HazardCategory::first() ?? HazardCategory::create(['code' => 'GEN', 'name' => 'Umum', 'is_active' => true]);
        $area = WorkArea::first();

        // Pastikan tidak ada aturan hse_target_threshold di database
        SapPointRule::whereIn('activity', ['hse_target_threshold', 'hse_target', 'target_threshold'])->delete();

        // Karyawan HSE memperoleh 5 poin
        for ($i = 1; $i <= 5; $i++) {
            HazardReport::create([
                'client_request_id' => (string) Str::uuid(),
                'reporter_id' => $hseUser->id,
                'reporter_department_id' => $hseDept->id,
                'category_id' => $category->id,
                'work_area_id' => $area->id,
                'title' => "Hazard HSE {$i}",
                'location' => 'Area Kantor',
                'description' => "Desc {$i}",
                'risk_level' => 'low',
                'status' => 'open',
                'verified_at' => $now,
                'reported_at' => $now,
            ]);
        }

        $summary = $this->sapService->calculateForUser($hseUser, $now);

        $this->assertEquals(5, $summary->total_points);
        $this->assertTrue($summary->is_hse_snapshot);
        $this->assertNull($summary->target_met); // Aturan belum ada, target_met tidak diubah jadi true
    }

    /**
     * UC-28: Admin dapat membuka menu pencapaian SAP.
     */
    public function test_admin_can_access_sap_index(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('sap.index'));

        $response->assertStatus(200);
        $response->assertSee('Pencapaian SAP');
        $response->assertSee('Total Poin Terdistribusi');
        $response->assertSee('Hitung Ulang SAP');
    }

    /**
     * UC-28: Karyawan biasa tidak boleh mengakses halaman rekap admin SAP.
     */
    public function test_employee_cannot_access_sap_index(): void
    {
        // Web request di-redirect ke dashboard dengan notifikasi error
        $response = $this->actingAs($this->employeeUser)->get(route('sap.index'));
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');

        // JSON request mengembalikan 403 Forbidden
        $jsonResponse = $this->actingAs($this->employeeUser)->getJson(route('sap.index'));
        $jsonResponse->assertStatus(403);
    }

    /**
     * UC-28: Admin dapat menjalankan hitung ulang SAP (Standard in Process).
     */
    public function test_admin_can_trigger_sap_recalculate(): void
    {
        $now = Carbon::now();
        $category = HazardCategory::first() ?? HazardCategory::create(['code' => 'GEN', 'name' => 'Umum', 'is_active' => true]);
        $area = WorkArea::first();

        // Beri karyawan satu hazard report
        HazardReport::create([
            'client_request_id' => (string) Str::uuid(),
            'reporter_id' => $this->employeeUser->id,
            'reporter_department_id' => $this->opsDept->id,
            'category_id' => $category->id,
            'work_area_id' => $area->id,
            'title' => 'Bahaya Tes Hitung Ulang',
            'location' => 'Gudang',
            'description' => 'Penumpukan palet',
            'risk_level' => 'low',
            'status' => 'open',
            'verified_at' => $now,
            'reported_at' => $now,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('sap.recalculate'), [
            'month' => $now->month,
            'year' => $now->year,
        ]);

        $response->assertRedirect(route('sap.index', [
            'year' => $now->year,
            'month' => $now->month,
        ]));
        $response->assertSessionHas('success');

        // Pastikan rekap bulanan terbuat
        $periodDate = $now->copy()->startOfMonth()->format('Y-m-01');
        $this->assertDatabaseHas('history_sap', [
            'user_id' => $this->employeeUser->id,
            'period' => $periodDate,
            'hazard_count' => 1,
            'total_points' => 1,
        ]);
    }

    /**
     * Dashboard Karyawan menampilkan poin SAP bulanan hasil perhitungan.
     */
    public function test_employee_dashboard_displays_calculated_sap_points(): void
    {
        $now = Carbon::now();
        $periodDate = $now->copy()->startOfMonth()->format('Y-m-01');

        SapMonthlySummary::create([
            'user_id' => $this->employeeUser->id,
            'period' => $periodDate,
            'department_id' => $this->opsDept->id,
            'is_hse_snapshot' => false,
            'hazard_count' => 3,
            'inspection_count' => 2,
            'resolution_count' => 1,
            'safety_talk_count' => 4,
            'total_points' => 10,
            'target_met' => true,
            'calculated_at' => $now,
        ]);

        $response = $this->actingAs($this->employeeUser)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('10'); // Total points
        $response->assertSee('Poin SAP K3');
    }

    /**
     * UC-28 Alur Alternatif (3'): Jika terjadi kesalahan sistem saat hitung ulang,
     * sistem menampilkan pesan error dan rekap SAP tetap tidak berubah.
     */
    public function test_sap_recalculate_handles_system_error_gracefully(): void
    {
        $now = Carbon::now();

        // Mock SapCalculationService agar melempar exception
        $mockService = $this->createMock(SapCalculationService::class);
        $mockService->method('recalculateAll')
            ->willThrowException(new \RuntimeException('Database disk full / connection timeout'));

        $this->app->instance(SapCalculationService::class, $mockService);

        $response = $this->actingAs($this->adminUser)->post(route('sap.recalculate'), [
            'month' => $now->month,
            'year' => $now->year,
        ]);

        $response->assertRedirect(route('sap.index', [
            'year' => $now->year,
            'month' => $now->month,
        ]));
        $response->assertSessionHas('error', 'Terjadi kesalahan sistem saat memproses hitung ulang SAP. Hitung ulang dibatalkan dan data rekap tetap seperti semula.');
    }
}

