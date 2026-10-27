<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\DailyInspection;
use App\Models\Department;
use App\Models\EmployeeRegistry;
use App\Models\EmploymentStatus;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkArea;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ShiftManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $employeeUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(DemoSeeder::class);

        $this->adminUser = User::where('username', 'admin')->firstOrFail();
        $this->employeeUser = User::where('username', 'karyawan')->firstOrFail();
    }

    public function test_admin_can_access_data_master_shifts_index(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('shifts.index'));

        $response->assertStatus(200);
        $response->assertSee('Data master');
        $response->assertSee('Shift kerja');
        $response->assertSee('+ Tambah shift');
    }

    public function test_employee_cannot_access_shifts_index(): void
    {
        $response = $this->actingAs($this->employeeUser)->get(route('shifts.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_admin_can_create_shift_with_auto_generated_code(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('shifts.store'), [
            'name' => 'Shift Pagi Operasional',
            'starts_at' => '07:00',
            'ends_at' => '15:00',
        ]);

        $response->assertRedirect(route('shifts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift', [
            'name' => 'Shift Pagi Operasional',
            'code' => 'shift-pagi-operasional',
            'starts_at' => '07:00:00',
            'ends_at' => '15:00:00',
        ]);
    }

    public function test_admin_can_create_overnight_shift(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('shifts.store'), [
            'name' => 'Shift Malam Ekstra',
            'starts_at' => '19:00',
            'ends_at' => '07:00',
        ]);

        $response->assertRedirect(route('shifts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift', [
            'name' => 'Shift Malam Ekstra',
            'code' => 'shift-malam-ekstra',
            'starts_at' => '19:00:00',
            'ends_at' => '07:00:00',
        ]);
    }

    public function test_shift_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('shifts.store'), [
            'name' => '',
            'starts_at' => '',
            'ends_at' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'starts_at', 'ends_at']);
    }

    public function test_shift_creation_rejects_identical_start_and_end_times(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('shifts.store'), [
            'name' => 'Shift Invalid',
            'starts_at' => '08:00',
            'ends_at' => '08:00',
        ]);

        $response->assertSessionHasErrors(['ends_at']);
    }

    public function test_admin_can_update_unreferenced_shift(): void
    {
        $shift = Shift::create([
            'name' => 'Shift Siang Awal',
            'code' => 's-siang',
            'starts_at' => '07:00',
            'ends_at' => '15:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('shifts.update', $shift), [
            'name' => 'Shift Siang Diperbarui',
            'starts_at' => '08:00',
            'ends_at' => '16:00',
        ]);

        $response->assertRedirect(route('shifts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift', [
            'id' => $shift->id,
            'name' => 'Shift Siang Diperbarui',
            'starts_at' => '08:00:00',
            'ends_at' => '16:00:00',
        ]);
    }

    public function test_admin_can_delete_unreferenced_shift(): void
    {
        $shift = Shift::create([
            'name' => 'Shift Sementara',
            'code' => 's-temp',
            'starts_at' => '10:00',
            'ends_at' => '18:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('shifts.destroy', $shift));

        $response->assertRedirect(route('shifts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('shift', [
            'id' => $shift->id,
        ]);
    }

    public function test_admin_cannot_delete_shift_referenced_by_daily_inspection(): void
    {
        $shift = Shift::create([
            'name' => 'Shift Aktif Dipakai',
            'code' => 's-active',
            'starts_at' => '07:00',
            'ends_at' => '15:00',
            'is_active' => true,
        ]);

        $workArea = WorkArea::create([
            'name' => 'Area Produksi 1',
            'is_active' => true,
        ]);

        DailyInspection::create([
            'client_request_id' => (string) Str::uuid(),
            'inspector_id' => $this->employeeUser->id,
            'department_id' => $this->employeeUser->department_id,
            'work_area_id' => $workArea->id,
            'shift_id' => $shift->id,
            'inspection_date' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('shifts.destroy', $shift));

        $response->assertRedirect(route('shifts.index'));
        $response->assertSessionHas('error', 'Shift kerja tidak dapat dihapus karena masih terkait dengan data inspeksi harian.');

        $this->assertDatabaseHas('shift', [
            'id' => $shift->id,
        ]);
    }

    public function test_admin_cannot_rename_shift_if_referenced_by_daily_inspection(): void
    {
        $shift = Shift::create([
            'name' => 'Shift Terkunci',
            'code' => 's-lock',
            'starts_at' => '07:00',
            'ends_at' => '15:00',
            'is_active' => true,
        ]);

        $workArea = WorkArea::create([
            'name' => 'Area Produksi 2',
            'is_active' => true,
        ]);

        DailyInspection::create([
            'client_request_id' => (string) Str::uuid(),
            'inspector_id' => $this->employeeUser->id,
            'department_id' => $this->employeeUser->department_id,
            'work_area_id' => $workArea->id,
            'shift_id' => $shift->id,
            'inspection_date' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('shifts.update', $shift), [
            'name' => 'Nama Baru Mau Ganti',
            'starts_at' => '07:00',
            'ends_at' => '15:00',
        ]);

        $response->assertSessionHasErrors(['name']);

        $this->assertDatabaseHas('shift', [
            'id' => $shift->id,
            'name' => 'Shift Terkunci',
        ]);
    }
}
