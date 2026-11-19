<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\HazardCategory;
use App\Models\HazardPhoto;
use App\Models\HazardReport;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HazardReportCreateTest extends TestCase
{
    use RefreshDatabase;

    protected User $employee;
    protected Department $department;
    protected Department $repairDepartment;
    protected HazardCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoSeeder::class);

        $this->department = Department::query()
            ->where('is_active', true)
            ->firstOrFail();

        $this->repairDepartment = Department::query()
            ->where('is_active', true)
            ->where('id', '!=', $this->department->id)
            ->first() ?? $this->department;

        $this->category = HazardCategory::query()->firstOrFail();

        $this->employee = User::factory()->create([
            'role' => 'employee',
            'department_id' => $this->department->id,
        ]);

        Storage::fake('local');
    }

    /**
     * Data laporan valid yang digunakan oleh beberapa test.
     */
    private function validPayload(): array
    {
        return [
            'client_request_id' => (string) Str::uuid(),
            'title' => 'Lantai licin di area produksi',
            'location' => 'Area Produksi A',
            'description' => 'Ditemukan lantai licin yang berpotensi menyebabkan kecelakaan.',
            'category_id' => $this->category->id,
            'reporter_department_id' => $this->department->id,
            'repair_department_id' => $this->repairDepartment->id,
            'immediate_action' => 'Memberikan tanda peringatan pada area tersebut.',
            'photos' => [
                UploadedFile::fake()->image('foto-bahaya.jpg'),
            ],
        ];
    }

    /**
     * TC-01: Karyawan dapat membuka halaman create.
     */
    public function test_employee_can_open_hazard_report_create_page(): void
    {
        $response = $this->actingAs($this->employee)
            ->get(route('hazards.create'));

        $response->assertOk()
            ->assertViewIs('hazards.create')
            ->assertViewHas('categories')
            ->assertViewHas('departments');
    }

    /**
     * TC-02: Laporan valid berhasil disimpan.
     */
    public function test_employee_can_create_valid_hazard_report(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->employee)
            ->post(route('hazards.store'), $payload);

        $response->assertRedirect(route('hazards.create'))
            ->assertSessionHas('success');

        $report = HazardReport::where(
            'client_request_id',
            $payload['client_request_id']
        )->firstOrFail();

        $this->assertSame(HazardReport::STATUS_OPEN, $report->status);
        $this->assertDatabaseCount('hazard_reports', 1);
        $this->assertDatabaseCount('hazard_photos', 1);

        $photo = HazardPhoto::where(
            'hazard_report_id',
            $report->id
        )->firstOrFail();

        $this->assertSame('local', $photo->disk);
        Storage::disk('local')->assertExists($photo->path);
    }

    /**
     * TC-03: reported_at menggunakan waktu server,
     * bukan tanggal yang dikirim oleh client.
     */
    public function test_reported_at_uses_server_time(): void
    {
        $serverTime = now()->startOfSecond();
        $this->travelTo($serverTime);

        $payload = $this->validPayload();

        // Client mencoba mengirim tanggal yang berbeda.
        $payload['reported_at'] = '2020-01-01 00:00:00';

        $this->actingAs($this->employee)
            ->post(route('hazards.store'), $payload)
            ->assertRedirect(route('hazards.create'));

        $report = HazardReport::where(
            'client_request_id',
            $payload['client_request_id']
        )->firstOrFail();

        $this->assertEquals(
            $serverTime->toDateTimeString(),
            $report->reported_at->toDateTimeString()
        );
    }

    /**
     * TC-05: Audit log mencatat action tambah.
     */
    public function test_new_hazard_report_creates_audit_log(): void
    {
        $payload = $this->validPayload();

        $this->actingAs($this->employee)
            ->post(route('hazards.store'), $payload)
            ->assertRedirect(route('hazards.create'));

        $report = HazardReport::where(
            'client_request_id',
            $payload['client_request_id']
        )->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $this->employee->id,
            'action' => 'tambah',
            'entity_type' => 'hazard_report',
            'entity_id' => $report->id,
        ]);
    }

    /**
     * TC-06: client_request_id yang sama tidak membuat laporan duplikat.
     */
    public function test_same_client_request_id_does_not_create_duplicate_report(): void
    {
        $payload = $this->validPayload();

        $this->actingAs($this->employee)
            ->post(route('hazards.store'), $payload)
            ->assertRedirect(route('hazards.create'));

        $this->actingAs($this->employee)
            ->post(route('hazards.store'), $payload)
            ->assertRedirect(route('hazards.create'));

        $this->assertDatabaseCount('hazard_reports', 1);
        $this->assertDatabaseCount('hazard_photos', 1);
    }

    /**
     * TC-07 sampai TC-11: Isian wajib kosong atau ID tidak terdaftar.
     */
    #[DataProvider('invalidPayloadProvider')]
    public function test_invalid_required_fields_are_rejected(
        string $field,
        mixed $value
    ): void {
        $payload = $this->validPayload();

        $payload[$field] = $value;

        $response = $this->actingAs($this->employee)
            ->post(route('hazards.store'), $payload);

        $response->assertSessionHasErrors($field);

        $this->assertDatabaseCount('hazard_reports', 0);
        $this->assertDatabaseCount('hazard_photos', 0);
    }

    public static function invalidPayloadProvider(): array
    {
        return [
            'title kosong' => ['title', ''],
            'location kosong' => ['location', ''],
            'description kosong' => ['description', ''],

            'category tidak terdaftar' => ['category_id', 999999],
            'category kosong' => ['category_id', ''],

            'departemen pelapor tidak terdaftar' => [
                'reporter_department_id',
                999999,
            ],
            'departemen pelapor kosong' => [
                'reporter_department_id',
                '',
            ],

            'departemen perbaikan tidak terdaftar' => [
                'repair_department_id',
                999999,
            ],
            'departemen perbaikan kosong' => [
                'repair_department_id',
                '',
            ],

            'immediate action kosong' => ['immediate_action', ''],
        ];
    }

    /**
     * TC-12: Laporan tanpa foto ditolak.
     */
    public function test_hazard_report_without_photo_is_rejected(): void
    {
        $payload = $this->validPayload();
        unset($payload['photos']);

        $response = $this->actingAs($this->employee)
            ->post(route('hazards.store'), $payload);

        $response->assertSessionHasErrors('photos');

        $this->assertDatabaseCount('hazard_reports', 0);
        $this->assertDatabaseCount('hazard_photos', 0);
    }

    /**
     * TC-13: File PDF ditolak.
     */
    public function test_pdf_file_is_rejected_as_hazard_photo(): void
    {
        $payload = $this->validPayload();

        $payload['photos'] = [
            UploadedFile::fake()->create(
                'dokumen.pdf',
                100,
                'application/pdf'
            ),
        ];

        $response = $this->actingAs($this->employee)
            ->post(route('hazards.store'), $payload);

        $response->assertSessionHasErrors('photos.0');

        $this->assertDatabaseCount('hazard_reports', 0);
        $this->assertDatabaseCount('hazard_photos', 0);
    }

    /**
     * TC-14: Tamu diarahkan ke halaman login.
     */
    public function test_guest_is_redirected_to_login_page(): void
    {
        $response = $this->get(route('hazards.create'));

        $response->assertRedirect(route('login'));
    }
}