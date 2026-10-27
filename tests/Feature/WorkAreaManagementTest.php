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

            $response->assertRedirect(route('dashboard'))->assertSessionHas('error');
        }
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
        $this->assertDatabaseHas('work_areas', ['id' => $area->id, 'name' => 'Area Produksi Utama']);
    }

    public function test_admin_can_delete_work_area(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $area = WorkArea::factory()->create(['department_id' => $admin->department_id]);

        $this->actingAs($admin)->delete(route('admin.work-areas.destroy', $area))
            ->assertRedirect(route('admin.work-areas.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('work_areas', ['id' => $area->id]);
    }

    public function test_used_work_area_can_be_deactivated_without_deletion(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $area = WorkArea::where('name', 'PIT/Front Loading')->firstOrFail();
        DB::table('daily_inspections')->insert([
            'client_request_id' => Str::uuid(),
            'inspector_id' => User::where('username', 'karyawan')->value('id'),
            'department_id' => $area->department_id,
            'work_area_id' => $area->id,
            'shift_id' => Shift::firstOrFail()->id,
            'inspection_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->patch(route('admin.work-areas.toggle-status', $area))->assertSessionHas('success');

        $this->assertDatabaseHas('work_areas', ['id' => $area->id, 'is_active' => false, 'deleted_at' => null]);
    }

    public function test_admin_can_create_and_update_question(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $area = WorkArea::factory()->create([
            'department_id' => $admin->department_id,
            'name' => 'Area Pertanyaan Test',
            'code' => 'QUESTION-TEST',
        ]);

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
