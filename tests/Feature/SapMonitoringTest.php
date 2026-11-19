<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\SapMonthlySummary;
use App\Models\SapTarget;
use App\Models\User;
use App\Services\SapCalculationService;
use Carbon\Carbon;
use Database\Seeders\DemoSeeder;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SapMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $hseEmployee;

    private Department $hseDepartment;

    private Department $nonHseDepartment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(DemoSeeder::class);

        $this->admin = User::where('username', 'admin')->firstOrFail();
        $this->hseEmployee = User::where('username', 'hse')->firstOrFail();
        $this->hseDepartment = Department::where('code', 'HSE')->firstOrFail();
        $this->nonHseDepartment = Department::where('code', 'OPS')->firstOrFail();
    }

    public function test_admin_page_shows_top_three_non_hse_and_hse_monitoring(): void
    {
        $period = Carbon::now()->startOfMonth();
        $employees = collect([
            ['Non-HSE Lima', 5],
            ['Non-HSE Empat', 4],
            ['Non-HSE Tiga', 3],
            ['Non-HSE Satu', 1],
        ])->map(function (array $record) use ($period): User {
            $employee = $this->makeNonHseEmployee($record[0]);
            $this->createSummary($employee, $this->nonHseDepartment, $period, $record[1]);

            return $employee;
        });

        $this->createSummary($this->hseEmployee, $this->hseDepartment, $period, 3, [
            'hazard_count' => 2,
            'inspection_count' => 1,
            'safety_talk_count' => 0,
        ]);
        $this->createHseTargets($this->hseEmployee, $period);

        $response = $this->actingAs($this->admin)->get(route('sap.index'));

        $response->assertOk()
            ->assertSee('Top 3 Karyawan Non-HSE')
            ->assertSee('Pencapaian HSE')
            ->assertSee('Non-HSE Lima')
            ->assertSee('Non-HSE Empat')
            ->assertSee('Non-HSE Tiga')
            ->assertDontSee('Non-HSE Satu')
            ->assertSee('2 / 4')
            ->assertSee('1 / 3')
            ->assertSee('0 / 1');
    }

    public function test_hse_activity_counts_include_target_percentages(): void
    {
        $period = Carbon::now()->startOfMonth();
        $this->createSummary($this->hseEmployee, $this->hseDepartment, $period, 3, [
            'hazard_count' => 2,
            'inspection_count' => 1,
            'safety_talk_count' => 0,
        ]);
        $this->createHseTargets($this->hseEmployee, $period);

        $response = $this->actingAs($this->admin)->get(route('sap.index'));

        $response->assertOk()
            ->assertSee('50.0%')
            ->assertSee('33.3%')
            ->assertSee('0.0%');
    }

    public function test_admin_top_three_is_sorted_by_points_and_supports_fewer_than_three_rows(): void
    {
        $period = Carbon::now()->startOfMonth();
        $employee = $this->makeNonHseEmployee('Satu Kontributor');
        $this->createSummary($employee, $this->nonHseDepartment, $period, 7);

        $response = $this->actingAs($this->admin)->get(route('sap.index'));

        $response->assertOk()
            ->assertSee('Satu Kontributor')
            ->assertSee('7 pts')
            ->assertDontSee('data-rank="2"', false);
    }

    public function test_admin_can_filter_period_and_defaults_to_current_month(): void
    {
        $currentPeriod = Carbon::now()->startOfMonth();
        $previousPeriod = $currentPeriod->copy()->subMonth();
        $currentEmployee = $this->makeNonHseEmployee('Kontributor Bulan Ini');
        $previousEmployee = $this->makeNonHseEmployee('Kontributor Bulan Lalu');
        $this->createSummary($currentEmployee, $this->nonHseDepartment, $currentPeriod, 7);
        $this->createSummary($previousEmployee, $this->nonHseDepartment, $previousPeriod, 4);

        $defaultResponse = $this->actingAs($this->admin)->get(route('sap.index'));
        $defaultResponse->assertOk()->assertSee('Kontributor Bulan Ini')->assertDontSee('Kontributor Bulan Lalu');

        $periodResponse = $this->actingAs($this->admin)->get(route('sap.index', [
            'month' => $previousPeriod->month,
            'year' => $previousPeriod->year,
        ]));
        $periodResponse->assertOk()->assertSee('Kontributor Bulan Lalu')->assertDontSee('Kontributor Bulan Ini');
    }

    public function test_admin_sees_empty_target_and_hse_history_messages(): void
    {
        $period = Carbon::now()->startOfMonth();

        $noTargetsResponse = $this->actingAs($this->admin)->get(route('sap.index'));
        $noTargetsResponse->assertOk()->assertSee('Belum terdapat data pencapaian untuk periode ini');

        $this->createHseTargets($this->hseEmployee, $period);
        $noHistoryResponse = $this->actingAs($this->admin)->get(route('sap.index'));
        $noHistoryResponse->assertOk()->assertSee('Belum ada data pencapaian HSE untuk periode ini');
    }

    public function test_guest_is_redirected_from_admin_sap_monitoring(): void
    {
        $this->get(route('sap.index'))->assertRedirect(route('login'));
    }

    public function test_hse_and_non_hse_employees_cannot_access_an_admin_sap_page_or_request_other_users(): void
    {
        $otherUser = $this->makeNonHseEmployee('Karyawan Lain');

        $this->actingAs($this->hseEmployee)
            ->get(route('sap.index', ['user_id' => $otherUser->id]))
            ->assertForbidden();

        $nonHseEmployee = $this->makeNonHseEmployee('Karyawan Non-HSE');

        $this->actingAs($nonHseEmployee)
            ->get(route('sap.index', ['user_id' => $this->hseEmployee->id]))
            ->assertForbidden();
    }

    public function test_admin_sees_empty_top_three_when_non_hse_has_no_monthly_summary(): void
    {
        $response = $this->actingAs($this->admin)->get(route('sap.index'));

        $response->assertOk()->assertSee('Belum ada kontribusi Non-HSE pada periode ini');
    }

    public function test_invalid_period_is_rejected_without_crashing(): void
    {
        $response = $this->actingAs($this->admin)->get(route('sap.index', ['month' => 13, 'year' => 'abc']));

        $response->assertSessionHasErrors(['month', 'year']);
    }

    public function test_database_error_shows_message_without_rendering_partial_results(): void
    {
        $period = Carbon::now()->startOfMonth();
        $employee = $this->makeNonHseEmployee('Data Rahasia SAP');
        $this->createSummary($employee, $this->nonHseDepartment, $period, 9);

        DB::listen(function (QueryExecuted $query): void {
            if (str_contains($query->sql, 'history_sap')) {
                throw new \RuntimeException('Simulated SAP query failure.');
            }
        });

        $response = $this->actingAs($this->admin)->get(route('sap.index'));

        $response->assertOk()
            ->assertSee('Data pencapaian SAP gagal dimuat. Coba lagi.')
            ->assertDontSee('Data Rahasia SAP');
    }

    private function makeNonHseEmployee(string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'department_id' => $this->nonHseDepartment->id,
            'role' => 'employee',
        ]);
    }

    /**
     * @param  array{hazard_count?: int, inspection_count?: int, safety_talk_count?: int}  $counts
     */
    private function createSummary(User $user, Department $department, Carbon $period, int $points, array $counts = []): SapMonthlySummary
    {
        return SapMonthlySummary::create(array_merge([
            'user_id' => $user->id,
            'period' => $period->format('Y-m-01'),
            'department_id' => $department->id,
            'is_hse_snapshot' => $department->is_hse,
            'hazard_count' => 0,
            'inspection_count' => 0,
            'safety_talk_count' => 0,
            'resolution_count' => 0,
            'total_points' => $points,
            'target_met' => null,
            'calculated_at' => now(),
        ], $counts));
    }

    private function createHseTargets(User $user, Carbon $period): void
    {
        foreach ([
            SapCalculationService::ACTIVITY_HAZARD => 4,
            SapCalculationService::ACTIVITY_INSPECTION => 3,
            SapCalculationService::ACTIVITY_SAFETY_TALK => 1,
        ] as $activity => $targetCount) {
            SapTarget::create([
                'user_id' => $user->id,
                'period' => $period->format('Y-m-01'),
                'activity' => $activity,
                'target_count' => $targetCount,
                'created_by' => $this->admin->id,
            ]);
        }
    }
}
