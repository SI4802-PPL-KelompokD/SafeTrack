<?php

namespace Tests\Feature;

use App\Models\InspectionQuestion;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkArea;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DailyInspectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(DemoSeeder::class);
    }

    public function test_admin_cannot_open_create_daily_inspection(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('daily-inspections.create'))
            ->assertForbidden();
    }

    public function test_admin_cannot_store_daily_inspection(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('daily-inspections.store'), $this->validInspectionPayload())
            ->assertForbidden();

        $this->assertDatabaseCount('daily_inspections', 0);
    }

    public function test_admin_can_view_inspections_without_create_action(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('daily-inspections.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('DailyInspections/Index', false)
                ->where('isAdmin', true));
    }

    public function test_hse_can_open_and_store_daily_inspection(): void
    {
        $hse = User::where('username', 'hse')->firstOrFail();

        $this->actingAs($hse)
            ->get(route('daily-inspections.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('DailyInspections/Index', false)
                ->where('isAdmin', false)
                ->where('openCreateModal', true));
        $this->actingAs($hse)
            ->post(route('daily-inspections.store'), $this->validInspectionPayload())
            ->assertRedirect(route('daily-inspections.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('daily_inspections', ['inspector_id' => $hse->id]);
    }

    public function test_non_hse_employee_can_open_and_store_daily_inspection(): void
    {
        $employee = User::where('username', 'karyawan')->firstOrFail();

        $this->actingAs($employee)
            ->get(route('daily-inspections.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('DailyInspections/Index', false)
                ->where('isAdmin', false)
                ->where('openCreateModal', true));
        $this->actingAs($employee)
            ->post(route('daily-inspections.store'), $this->validInspectionPayload())
            ->assertRedirect(route('daily-inspections.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('daily_inspections', ['inspector_id' => $employee->id]);
    }

    public function test_no_answer_requires_photo_and_description(): void
    {
        $user = User::where('username', 'karyawan')->firstOrFail();
        $area = WorkArea::where('name', 'PIT/Front Loading')->firstOrFail();
        $question = InspectionQuestion::whereBelongsTo($area)->firstOrFail();

        $response = $this->actingAs($user)->post(route('daily-inspections.store'), [
            'work_area_id' => $area->id,
            'shift_id' => Shift::firstOrFail()->id,
            'inspection_date' => now()->toDateString(),
            'answers' => [['question_id' => $question->id, 'answer' => 'no']],
        ]);

        $response->assertSessionHasErrors(['answers.0.description', 'answers.0.photo']);
        $this->assertDatabaseCount('daily_inspections', 0);
    }

    public function test_yes_answers_are_saved_with_question_snapshots(): void
    {
        $user = User::where('username', 'karyawan')->firstOrFail();
        $payload = $this->validInspectionPayload();
        $firstQuestion = InspectionQuestion::findOrFail($payload['answers'][0]['question_id']);

        $response = $this->actingAs($user)->post(route('daily-inspections.store'), $payload);

        $response->assertRedirect(route('daily-inspections.index'))->assertSessionHas('success');
        $this->assertDatabaseHas('inspection_answers', [
            'inspection_question_id' => $firstQuestion->id,
            'question_snapshot' => $firstQuestion->question,
            'answer' => 'yes',
        ]);
    }

    private function validInspectionPayload(): array
    {
        $area = WorkArea::where('name', 'PIT/Front Loading')->firstOrFail();
        $answers = InspectionQuestion::whereBelongsTo($area)
            ->activeOrdered()
            ->get()
            ->map(fn (InspectionQuestion $question): array => [
                'question_id' => $question->id,
                'answer' => 'yes',
            ])->all();

        return [
            'work_area_id' => $area->id,
            'shift_id' => Shift::firstOrFail()->id,
            'inspection_date' => now()->toDateString(),
            'answers' => $answers,
        ];
    }
}
