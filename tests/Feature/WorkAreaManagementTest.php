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
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WorkAreaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(DemoSeeder::class);
    }

    public function test_admin_can_open_work_area_management_page(): void
    {
        $response = $this->actingAs(User::where('username', 'admin')->firstOrFail())
            ->get(route('admin.work-areas.index'));

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('WorkAreas/Index', false)
            ->has('workAreas.data'));
    }

    public function test_hse_and_non_hse_employees_cannot_open_work_area_management_page(): void
    {
        $nonHseEmployee = User::where('username', 'karyawan')->firstOrFail();
        $hseEmployee = User::factory()->create(['role' => 'employee']);
        $hseEmployee->update(['department_id' => User::where('username', 'admin')->value('department_id')]);

        foreach ([$hseEmployee, $nonHseEmployee] as $employee) {
            $response = $this->actingAs($employee)->get(route('admin.work-areas.index'));

            $response->assertForbidden();
        }
    }

    public function test_employee_cannot_call_any_administrative_endpoint_or_change_data(): void
    {
        $employee = User::where('username', 'karyawan')->firstOrFail();
        $area = WorkArea::firstOrFail();
        $question = InspectionQuestion::where('work_area_id', $area->id)->firstOrFail();
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
            ['DELETE', route('shifts.destroy', $shift)],
            ['GET', route('admin.work-areas.index')],
            ['GET', route('admin.work-areas.show', $area)],
            ['POST', route('admin.work-areas.store')],
            ['PUT', route('admin.work-areas.update', $area)],
            ['PATCH', route('admin.work-areas.toggle-status', $area)],
            ['POST', route('admin.work-areas.questions.store', $area)],
            ['PUT', route('admin.work-areas.questions.update', [$area, $question])],
            ['PATCH', route('admin.work-areas.questions.toggle-status', [$area, $question])],
            ['DELETE', route('admin.work-areas.questions.destroy', [$area, $question])],
        ] as [$method, $url]) {
            $this->json($method, $url, ['role' => 'super_admin', 'userId' => $employee->id + 1])->assertForbidden();
        }
        foreach ($before as $table => $snapshot) {
            $this->assertSame($snapshot, DB::table($table)->orderBy('id')->get()->toJson());
        }
    }

    public function test_admin_cannot_mutate_a_question_through_another_area(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $question = InspectionQuestion::firstOrFail();
        $otherArea = WorkArea::where('id', '!=', $question->work_area_id)->firstOrFail();
        $before = $question->toArray();
        $this->actingAs($admin);
        $this->putJson(route('admin.work-areas.questions.update', [$otherArea, $question]), [
            'question' => 'Tidak boleh berubah', 'sort_order' => 1,
        ])->assertNotFound();
        $this->patchJson(route('admin.work-areas.questions.toggle-status', [$otherArea, $question]))->assertNotFound();
        $this->deleteJson(route('admin.work-areas.questions.destroy', [$otherArea, $question]))->assertNotFound();
        $this->assertSame($before, $question->fresh()->toArray());
    }

    public function test_admin_can_create_valid_work_area(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.work-areas.store'), [
            'name' => 'Laboratorium',
            'description' => 'Area pengujian sampel.',
            'questions' => [
                'Apakah meja kerja dalam kondisi bersih?',
                'Apakah alat keselamatan tersedia?',
                'Apakah jalur evakuasi dapat digunakan?',
            ],
        ]);

        $response->assertRedirect(route('admin.work-areas.index'))->assertSessionHas('success');
        $area = WorkArea::where('name', 'Laboratorium')->firstOrFail();
        $this->assertTrue($area->is_active);
        $this->assertSame(3, $area->questions()->count());
        $this->assertDatabaseHas('audit_logs', ['actor_id' => $admin->id, 'action' => 'work_area.created']);
    }

    public function test_duplicate_work_area_is_rejected(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $existing = WorkArea::firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.work-areas.store'), [
            'name' => $existing->name,
            'questions' => ['Pertanyaan satu?', 'Pertanyaan dua?', 'Pertanyaan tiga?'],
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_admin_can_update_work_area(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $area = WorkArea::firstOrFail();

        $response = $this->actingAs($admin)->put(route('admin.work-areas.update', $area), [
            'name' => 'Area Produksi Utama',
            'description' => 'Deskripsi baru',
        ]);

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('area_kerja', ['id' => $area->id, 'name' => 'Area Produksi Utama']);
    }

    public function test_admin_can_delete_work_area(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $area = WorkArea::factory()->create();

        $this->actingAs($admin)->delete(route('admin.work-areas.destroy', $area))
            ->assertRedirect(route('admin.work-areas.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('area_kerja', ['id' => $area->id]);
    }

    public function test_used_work_area_can_be_deactivated_without_deletion(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $area = WorkArea::where('name', 'PIT/Front Loading')->firstOrFail();
        DB::table('daily_inspection')->insert([
            'client_request_id' => Str::uuid(),
            'inspector_id' => User::where('username', 'karyawan')->value('id'),
            'department_id' => $admin->department_id,
            'work_area_id' => $area->id,
            'shift_id' => Shift::firstOrFail()->id,
            'inspection_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->patch(route('admin.work-areas.toggle-status', $area))->assertSessionHas('success');

        $this->assertDatabaseHas('area_kerja', ['id' => $area->id, 'is_active' => false, 'deleted_at' => null]);
    }

    public function test_admin_can_create_and_update_question(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $area = WorkArea::factory()->create(['name' => 'Area Pertanyaan Test']);

        $this->actingAs($admin)->post(route('admin.work-areas.questions.store', $area), [
            'question' => 'Apakah alat pemadam tersedia?', 'sort_order' => 2, 'is_active' => '1',
        ])->assertSessionHas('success');
        $question = InspectionQuestion::where('work_area_id', $area->id)->firstOrFail();

        $this->actingAs($admin)->put(route('admin.work-areas.questions.update', [$area, $question]), [
            'question' => 'Apakah alat pemadam tersedia dan layak?', 'sort_order' => 1, 'is_active' => '1',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('inspection_questions', ['id' => $question->id, 'sort_order' => 1]);
    }

    public function test_duplicate_question_in_same_area_is_rejected(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $question = InspectionQuestion::firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.work-areas.questions.store', $question->workArea), [
            'question' => $question->question, 'sort_order' => 10,
        ]);

        $response->assertSessionHasErrors(['question']);
    }
}
