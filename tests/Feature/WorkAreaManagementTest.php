<?php

namespace Tests\Feature;

use App\Models\InspectionQuestion;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkArea;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkAreaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
    }

    private function identity(array $data = []): array
    {
        return [...$data, 'admin_name' => 'Petugas Test', 'action_reason' => 'Penyesuaian checklist keselamatan kerja.'];
    }

    private function admin(): User
    {
        return User::where('username', 'admin')->firstOrFail();
    }

    public function test_admin_opens_blade_pages_without_delete_buttons(): void
    {
        $area = WorkArea::firstOrFail();
        $this->actingAs($this->admin())->get(route('admin.work-areas.index'))->assertOk()->assertViewIs('work-areas.index')->assertSee('role="switch"', false)->assertDontSee('Hapus');
        $this->get(route('admin.work-areas.show', $area))->assertOk()->assertViewIs('work-areas.show')->assertSee('Alasan perubahan')->assertDontSee('Hapus');
    }

    public function test_employee_roles_cannot_access_or_change_master_data(): void
    {
        $hse = User::factory()->create(['role' => 'employee', 'department_id' => $this->admin()->department_id]);
        foreach ([$hse, User::where('username', 'karyawan')->firstOrFail()] as $user) {
            $this->actingAs($user)->get(route('admin.work-areas.index'))->assertForbidden();
            $this->post(route('admin.work-areas.store'), $this->identity(['name' => 'Terlarang']))->assertForbidden();
        }
        $this->assertDatabaseMissing('area_kerja', ['name' => 'Terlarang']);
    }

    public function test_create_area_saves_three_questions_and_operator_audit(): void
    {
        $this->actingAs($this->admin())->post(route('admin.work-areas.store'), $this->identity(['name' => 'Laboratorium', 'description' => 'Area sampel.', 'questions' => ['Meja bersih?', 'APD tersedia?', 'Jalur aman?']]))->assertRedirect(route('admin.work-areas.index'))->assertSessionHas('success');
        $area = WorkArea::where('name', 'Laboratorium')->firstOrFail();
        $this->assertSame(3, $area->questions()->count());
        $this->assertTrue($area->is_active);
        $this->assertAudit('work_area.created', $area->id);
    }

    public function test_duplicate_area_and_invalid_initial_questions_are_rejected(): void
    {
        $this->actingAs($this->admin())->post(route('admin.work-areas.store'), $this->identity(['name' => WorkArea::firstOrFail()->name, 'questions' => ['Satu?', 'Dua?', 'Tiga?']]))->assertSessionHasErrors('name');
        foreach ([['Satu?', 'Dua?'], ['Sama?', 'Sama?', 'Tiga?']] as $questions) {
            $this->post(route('admin.work-areas.store'), $this->identity(['name' => 'Area Invalid', 'questions' => $questions]))->assertSessionHasErrors();
        }
        $this->assertDatabaseMissing('area_kerja', ['name' => 'Area Invalid']);
    }

    public function test_update_area_records_identity_and_reason(): void
    {
        $area = WorkArea::firstOrFail();
        $this->actingAs($this->admin())->put(route('admin.work-areas.update', $area), $this->identity(['name' => 'Area Diperbarui', 'description' => 'Deskripsi baru.']))->assertSessionHas('success');
        $this->assertSame('Area Diperbarui', $area->fresh()->name);
        $this->assertAudit('work_area.updated', $area->id);
    }

    public function test_missing_or_short_operator_and_reason_leave_data_unchanged(): void
    {
        $area = WorkArea::firstOrFail();
        foreach ([['admin_name' => '', 'action_reason' => 'Alasan lengkap perubahan.'], ['admin_name' => '  ', 'action_reason' => 'Alasan lengkap perubahan.'], ['admin_name' => 'Admin Test', 'action_reason' => ''], ['admin_name' => 'Admin Test', 'action_reason' => 'pendek']] as $identity) {
            $this->actingAs($this->admin())->put(route('admin.work-areas.update', $area), [...$identity, 'name' => 'Tidak Tersimpan'])->assertSessionHasErrors();
        }
        $this->assertDatabaseMissing('area_kerja', ['name' => 'Tidak Tersimpan']);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'work_area.updated']);
    }

    public function test_used_area_can_be_disabled_and_enabled_with_history_intact(): void
    {
        $area = WorkArea::firstOrFail();
        $inspection = DB::table('daily_inspection')->insertGetId(['client_request_id' => Str::uuid(), 'inspector_id' => User::where('username', 'karyawan')->value('id'), 'department_id' => $this->admin()->department_id, 'work_area_id' => $area->id, 'shift_id' => Shift::firstOrFail()->id, 'inspection_date' => now()->toDateString(), 'created_at' => now(), 'updated_at' => now()]);
        foreach ([false, true] as $enabled) {
            $this->actingAs($this->admin())->patch(route('admin.work-areas.toggle-status', $area), $this->identity(['is_active' => $enabled]))->assertSessionHas('success');
            $this->assertSame($enabled, $area->fresh()->is_active);
            $this->assertNull($area->fresh()->deleted_at);
            $this->assertDatabaseHas('daily_inspection', ['id' => $inspection, 'work_area_id' => $area->id]);
        }
    }

    public function test_repeated_status_request_does_not_toggle_back(): void
    {
        $area = WorkArea::firstOrFail();
        $this->actingAs($this->admin())->patch(route('admin.work-areas.toggle-status', $area), $this->identity())->assertSessionHasErrors('is_active');
        for ($i = 0; $i < 2; $i++) {
            $this->patch(route('admin.work-areas.toggle-status', $area), $this->identity(['is_active' => false]))->assertSessionHas('success');
        }
        $this->assertFalse($area->fresh()->is_active);
    }

    public function test_inactive_records_are_listed_last(): void
    {
        WorkArea::factory()->create(['name' => 'AAA Nonaktif', 'is_active' => false]);
        $enabled = WorkArea::factory()->create(['name' => 'AAA Aktif', 'is_active' => true]);
        $this->actingAs($this->admin())->get(route('admin.work-areas.index'))->assertViewHas('workAreas', fn ($areas) => $areas->first()->is_active);
        $off = InspectionQuestion::factory()->create(['work_area_id' => $enabled->id, 'sort_order' => 1, 'is_active' => false]);
        $on = InspectionQuestion::factory()->create(['work_area_id' => $enabled->id, 'sort_order' => 9, 'is_active' => true]);
        $this->get(route('admin.work-areas.show', $enabled))->assertViewHas('questions', fn ($questions) => $questions->pluck('id')->all() === [$on->id, $off->id]);
    }

    public function test_add_update_and_disable_question_preserves_record_and_audit(): void
    {
        $area = WorkArea::factory()->create();
        $this->actingAs($this->admin())->post(route('admin.work-areas.questions.store', $area), $this->identity(['question' => 'APAR tersedia?', 'sort_order' => 1]))->assertSessionHas('success');
        $question = $area->questions()->firstOrFail();
        $this->patch(route('admin.work-areas.questions.toggle-status', [$area, $question]), $this->identity(['is_active' => false]))->assertSessionHas('success');
        $this->put(route('admin.work-areas.questions.update', [$area, $question]), $this->identity(['question' => 'APAR layak?', 'sort_order' => 2]))->assertSessionHas('success');
        $this->assertFalse($question->fresh()->is_active);
        $this->assertNull($question->fresh()->deleted_at);
        $this->assertAudit('inspection_question.status_changed', $question->id);
    }

    public function test_duplicate_question_order_and_owner_validation(): void
    {
        $question = InspectionQuestion::firstOrFail();
        $data = $this->identity(['question' => $question->question, 'sort_order' => 1]);
        $this->actingAs($this->admin())->post(route('admin.work-areas.questions.store', $question->workArea), $data)->assertSessionHasErrors('question');
        $other = WorkArea::factory()->create();
        $this->post(route('admin.work-areas.questions.store', $other), $data)->assertSessionHas('success');
        $this->put(route('admin.work-areas.questions.update', [$question->workArea, $question]), $this->identity(['question' => 'Test?', 'sort_order' => 0]))->assertSessionHasErrors('sort_order');
        $this->put(route('admin.work-areas.questions.update', [$other, $question]), $data)->assertNotFound();
    }

    public function test_delete_endpoints_are_unavailable_and_records_remain(): void
    {
        $area = WorkArea::factory()->create();
        $question = InspectionQuestion::factory()->create(['work_area_id' => $area->id]);
        $this->actingAs($this->admin())->delete('/admin/work-areas/'.$area->id, $this->identity())->assertStatus(405);
        $this->delete('/admin/work-areas/'.$area->id.'/questions/'.$question->id, $this->identity())->assertStatus(405);
        $this->assertNotSoftDeleted('area_kerja', ['id' => $area->id]);
        $this->assertNotSoftDeleted('inspection_questions', ['id' => $question->id]);
    }

    public function test_employee_cannot_call_administrative_endpoints_or_change_data(): void
    {
        $employee = User::where('username', 'karyawan')->firstOrFail();
        $area = WorkArea::firstOrFail();
        $question = $area->questions()->firstOrFail();
        $shift = Shift::firstOrFail();
        $before = [];
        foreach (['area_kerja', 'inspection_questions', 'shift', 'audit_logs'] as $table) {
            $before[$table] = DB::table($table)->orderBy('id')->get()->toJson();
        }
        $this->actingAs($employee);
        foreach ([
            ['GET', route('shifts.index')],
            ['POST', route('shifts.store')],
            ['PUT', route('shifts.update', $shift)],
            ['PATCH', route('shifts.toggle-status', $shift)],
            ['GET', route('admin.work-areas.index')],
            ['GET', route('admin.work-areas.show', $area)],
            ['POST', route('admin.work-areas.store')],
            ['PUT', route('admin.work-areas.update', $area)],
            ['PATCH', route('admin.work-areas.toggle-status', $area)],
            ['POST', route('admin.work-areas.questions.store', $area)],
            ['PUT', route('admin.work-areas.questions.update', [$area, $question])],
            ['PATCH', route('admin.work-areas.questions.toggle-status', [$area, $question])],
        ] as [$method, $url]) {
            $this->json($method, $url, ['role' => 'super_admin', 'userId' => $employee->id + 1])->assertForbidden();
        }
        foreach ($before as $table => $snapshot) {
            $this->assertSame($snapshot, DB::table($table)->orderBy('id')->get()->toJson());
        }
    }

    private function assertAudit(string $action, int $id): void
    {
        $audit = DB::table('audit_logs')->where('action', $action)->where('entity_id', $id)->latest('id')->first();
        $this->assertNotNull($audit);
        $values = json_decode($audit->after_values, true);
        $this->assertSame('Petugas Test', $values['admin_name']);
        $this->assertSame('Penyesuaian checklist keselamatan kerja.', $values['action_reason']);
        $this->assertEquals($this->admin()->id, $audit->actor_id);
    }
}
