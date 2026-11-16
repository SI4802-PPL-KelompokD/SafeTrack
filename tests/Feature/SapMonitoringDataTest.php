<?php

namespace Tests\Feature;

use App\Models\SapMonthlySummary;
use App\Models\SapTarget;
use App\Models\User;
use App\Services\SapCalculationService;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SapMonitoringDataTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $hseEmployee;

    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(DemoSeeder::class);

        $this->admin = User::where('username', 'admin')->firstOrFail();
        $this->hseEmployee = User::where('username', 'hse')->firstOrFail();
        $this->employee = User::where('username', 'karyawan')->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function createSummary(User $user, array $overrides = []): SapMonthlySummary
    {
        return SapMonthlySummary::create(array_merge([
            'user_id' => $user->id,
            'period' => now()->startOfMonth()->format('Y-m-01'),
            'department_id' => $user->department_id,
            'is_hse_snapshot' => (bool) $user->department?->is_hse,
            'hazard_count' => 0,
            'inspection_count' => 0,
            'resolution_count' => 0,
            'safety_talk_count' => 0,
            'total_points' => 0,
            'target_met' => null,
            'calculated_at' => now(),
        ], $overrides));
    }

    public function test_admin_sees_hse_employee_with_default_targets_when_no_summary_exists(): void
    {
        $this->actingAs($this->admin)
            ->get(route('sap.monitoring'))
            ->assertOk()
            ->assertSee('Petugas HSE')
            ->assertSeeText('0 / 4')
            ->assertSeeText('0 / 3')
            ->assertSeeText('Belum terdapat data pencapaian');
    }

    public function test_admin_sees_realization_and_percentage_for_hse_employee(): void
    {
        $this->createSummary($this->hseEmployee, [
            'hazard_count' => 2,
            'inspection_count' => 3,
            'safety_talk_count' => 5,
            'total_points' => 7,
        ]);

        $this->actingAs($this->admin)
            ->get(route('sap.monitoring'))
            ->assertOk()
            ->assertSeeText('2 / 4')
            ->assertSeeText('3 / 3')
            ->assertSeeText('71%')
            ->assertDontSeeText('Belum terdapat data pencapaian');
    }

    public function test_admin_sees_custom_target_when_one_is_set_for_the_period(): void
    {
        SapTarget::create([
            'user_id' => $this->hseEmployee->id,
            'period' => now()->startOfMonth()->format('Y-m-01'),
            'activity' => SapCalculationService::ACTIVITY_HAZARD,
            'target_count' => 6,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('sap.monitoring'))
            ->assertOk()
            ->assertSeeText('0 / 6');
    }

    public function test_admin_sees_top_contributors_ranked_by_points_without_admin_accounts(): void
    {
        $this->createSummary($this->hseEmployee, ['total_points' => 5]);
        $this->createSummary($this->employee, ['total_points' => 9]);
        $this->createSummary($this->admin, ['total_points' => 100]);

        $this->actingAs($this->admin)
            ->get(route('sap.monitoring'))
            ->assertOk()
            ->assertSeeInOrder(['Karyawan Demo', 'Petugas HSE'])
            ->assertSeeText('9 poin')
            ->assertDontSeeText('100 poin');
    }

    public function test_admin_can_view_previous_month_with_period_filter(): void
    {
        $previousMonth = now()->startOfMonth()->subMonth();

        $this->createSummary($this->hseEmployee, [
            'period' => $previousMonth->format('Y-m-01'),
            'hazard_count' => 2,
        ]);

        $this->actingAs($this->admin)
            ->get(route('sap.monitoring', ['period' => $previousMonth->format('Y-m')]))
            ->assertOk()
            ->assertSeeText('2 / 4');

        $this->actingAs($this->admin)
            ->get(route('sap.monitoring'))
            ->assertOk()
            ->assertDontSeeText('2 / 4');
    }

    public function test_invalid_period_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->get(route('sap.monitoring', ['period' => 'bukan-periode']))
            ->assertSessionHasErrors('period');
    }

    public function test_hse_employee_sees_own_target_progress(): void
    {
        $this->createSummary($this->hseEmployee, [
            'hazard_count' => 2,
            'inspection_count' => 3,
            'safety_talk_count' => 5,
        ]);

        $this->actingAs($this->hseEmployee)
            ->get(route('sap.monitoring'))
            ->assertOk()
            ->assertSeeText('2 / 4')
            ->assertSeeText('3 / 3')
            ->assertSeeText('71%')
            ->assertSeeText('Sisa target: 2');
    }

    public function test_non_hse_employee_sees_only_own_contribution(): void
    {
        $this->createSummary($this->employee, [
            'hazard_count' => 2,
            'inspection_count' => 1,
            'safety_talk_count' => 3,
            'total_points' => 41,
        ]);
        $this->createSummary($this->hseEmployee, ['total_points' => 77]);

        $this->actingAs($this->employee)
            ->get(route('sap.monitoring'))
            ->assertOk()
            ->assertSeeText('Total kontribusi / bulan')
            ->assertDontSeeText('Poin SAP bulan ini')
            ->assertDontSeeText('Petugas HSE')
            ->assertDontSeeText('Top 3');
    }
}
