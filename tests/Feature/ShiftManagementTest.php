<?php

namespace Tests\Feature;

use App\Models\Shift;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

        $response->assertStatus(403);
    }

    public function test_index_search_filters_by_name(): void
    {
        Shift::create([
            'name' => 'Shift Alpha Unik',
            'code' => 'shift-alpha-unik',
            'starts_at' => '07:00',
            'ends_at' => '15:00',
            'is_active' => true,
        ]);

        Shift::create([
            'name' => 'Shift Beta Lain',
            'code' => 'shift-beta-lain',
            'starts_at' => '15:00',
            'ends_at' => '23:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('shifts.index', ['search' => 'Alpha']));

        $response->assertStatus(200);
        $response->assertSee('Shift Alpha Unik');
        $response->assertDontSee('Shift Beta Lain');
    }

    public function test_index_paginates_5_per_page(): void
    {
        Shift::query()->delete();

        for ($i = 1; $i <= 7; $i++) {
            Shift::create([
                'name' => "Shift Paginate {$i}",
                'code' => "shift-paginate-{$i}",
                'starts_at' => '07:00',
                'ends_at' => '15:00',
                'is_active' => true,
            ]);
        }

        $response = $this->actingAs($this->adminUser)->get(route('shifts.index'));
        $response->assertStatus(200);

        $shifts = $response->viewData('shifts');
        $this->assertCount(5, $shifts);

        $responsePage2 = $this->actingAs($this->adminUser)->get(route('shifts.index', ['page' => 2]));
        $responsePage2->assertStatus(200);

        $shiftsPage2 = $responsePage2->viewData('shifts');
        $this->assertCount(2, $shiftsPage2);
    }

    public function test_admin_can_create_shift(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('shifts.store'), [
            'name' => 'Shift Pagi Operasional',
            'starts_at' => '07:00',
            'ends_at' => '15:00',
            'admin_name' => 'Admin Test',
            'action_reason' => 'Alasan pengujian otomatis',
        ]);

        $response->assertRedirect(route('shifts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift', [
            'name' => 'Shift Pagi Operasional',
            'code' => 'shift-pagi-operasional',
            'starts_at' => '07:00:00',
            'ends_at' => '15:00:00',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'shift.created',
            'entity_type' => Shift::class,
        ]);
    }

    public function test_admin_can_create_overnight_shift(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('shifts.store'), [
            'name' => 'Shift Malam Ekstra',
            'starts_at' => '19:00',
            'ends_at' => '07:00',
            'admin_name' => 'Admin Test',
            'action_reason' => 'Alasan pengujian otomatis',
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
            'admin_name' => 'Admin Test',
            'action_reason' => 'Alasan pengujian otomatis',
        ]);

        $response->assertSessionHasErrors(['name', 'starts_at', 'ends_at']);
    }

    public function test_shift_creation_rejects_identical_times(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('shifts.store'), [
            'name' => 'Shift Invalid',
            'starts_at' => '08:00',
            'ends_at' => '08:00',
            'admin_name' => 'Admin Test',
            'action_reason' => 'Alasan pengujian otomatis',
        ]);

        $response->assertSessionHasErrors(['ends_at']);
    }

    public function test_shift_creation_rejects_duplicate_name(): void
    {
        Shift::create([
            'name' => 'Shift Duplikat',
            'code' => 'shift-duplikat',
            'starts_at' => '07:00',
            'ends_at' => '15:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('shifts.store'), [
            'name' => 'Shift Duplikat',
            'starts_at' => '08:00',
            'ends_at' => '16:00',
            'admin_name' => 'Admin Test',
            'action_reason' => 'Alasan pengujian otomatis',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_shift_name_max_50_chars(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('shifts.store'), [
            'name' => str_repeat('A', 51),
            'starts_at' => '07:00',
            'ends_at' => '15:00',
            'admin_name' => 'Admin Test',
            'action_reason' => 'Alasan pengujian otomatis',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_admin_can_update_shift(): void
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
            'admin_name' => 'Admin Test',
            'action_reason' => 'Alasan pengujian otomatis',
        ]);

        $response->assertRedirect(route('shifts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift', [
            'id' => $shift->id,
            'name' => 'Shift Siang Diperbarui',
            'starts_at' => '08:00:00',
            'ends_at' => '16:00:00',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'shift.updated',
            'entity_type' => Shift::class,
            'entity_id' => $shift->id,
        ]);
    }

    public function test_admin_can_toggle_shift_status(): void
    {
        $shift = Shift::create([
            'name' => 'Shift Toggle Test',
            'code' => 's-toggle',
            'starts_at' => '07:00',
            'ends_at' => '15:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->patch(route('shifts.toggle-status', $shift), [
            'is_active' => false,
            'admin_name' => 'Admin Test',
            'action_reason' => 'Alasan pengujian otomatis',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Status shift kerja berhasil diperbarui.');

        $this->assertDatabaseHas('shift', [
            'id' => $shift->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'shift.status_changed',
            'entity_type' => Shift::class,
            'entity_id' => $shift->id,
        ]);
    }

    public function test_toggle_writes_audit_log(): void
    {
        $shift = Shift::create([
            'name' => 'Shift Audit Log Test',
            'code' => 's-audit',
            'starts_at' => '07:00',
            'ends_at' => '15:00',
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser)->patch(route('shifts.toggle-status', $shift), [
            'is_active' => false,
            'admin_name' => 'Admin Test',
            'action_reason' => 'Alasan pengujian otomatis',
        ]);

        $auditLog = DB::table('audit_logs')
            ->where('action', 'shift.status_changed')
            ->where('entity_id', $shift->id)
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals($this->adminUser->id, $auditLog->actor_id);
        $this->assertEquals(Shift::class, $auditLog->entity_type);

        $afterValues = json_decode($auditLog->after_values, true);
        $this->assertEquals('Admin Test', $afterValues['admin_name']);
        $this->assertEquals('Alasan pengujian otomatis', $afterValues['action_reason']);

        $beforeValues = json_decode($auditLog->before_values, true);
        $this->assertTrue($beforeValues['is_active']);
    }

    public function test_store_requires_admin_name_and_action_reason(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('shifts.store'), [
            'name' => 'Shift Tanpa Identity',
            'starts_at' => '07:00',
            'ends_at' => '15:00',
        ]);

        $response->assertSessionHasErrors(['admin_name', 'action_reason']);
    }
}
