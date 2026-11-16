<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SapMonitoringAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(DemoSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('sap.monitoring'))->assertRedirect(route('login'));
    }

    public function test_admin_sees_hse_target_and_top_contributors_page(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('sap.monitoring'))
            ->assertOk()
            ->assertSee('Target HSE & Top 3 Karyawan');
    }

    public function test_hse_employee_sees_monthly_hse_target_page(): void
    {
        $hseEmployee = User::whereNotIn('role', ['super_admin', 'admin'])
            ->whereHas('department', fn ($query) => $query->where('is_hse', true))
            ->firstOrFail();

        $this->actingAs($hseEmployee)
            ->get(route('sap.monitoring'))
            ->assertOk()
            ->assertSee('Target bulanan HSE');
    }

    public function test_non_hse_employee_sees_safety_contribution_page(): void
    {
        $employee = User::where('username', 'karyawan')->firstOrFail();

        $this->actingAs($employee)
            ->get(route('sap.monitoring'))
            ->assertOk()
            ->assertSee('Kontribusi keselamatan');
    }
}
