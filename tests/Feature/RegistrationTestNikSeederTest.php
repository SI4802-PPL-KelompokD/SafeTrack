<?php

namespace Tests\Feature;

use App\Models\EmployeeRegistry;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Database\Seeders\RegistrationTestNikSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Tests\TestCase;

class RegistrationTestNikSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_adds_five_unused_active_niks_without_creating_accounts(): void
    {
        $this->seed(DemoSeeder::class);
        $usersBefore = User::count();

        $this->seed(RegistrationTestNikSeeder::class);

        $registries = EmployeeRegistry::where('nik', 'like', 'EMP-TEST-%')->get();
        $this->assertCount(5, $registries);
        $this->assertTrue($registries->every(fn (EmployeeRegistry $registry): bool => $registry->is_active));
        $this->assertDatabaseCount('users', $usersBefore);
        $this->assertSame(0, User::withTrashed()->whereIn('employee_registry_id', $registries->modelKeys())->count());
    }

    #[TestWith(['EMP-TEST-001', 'Andi Pratama', 'OPS', 'permanent'])]
    #[TestWith(['EMP-TEST-002', 'Budi Santoso', 'OPS', 'contract'])]
    #[TestWith(['EMP-TEST-003', 'Citra Lestari', 'HSE', 'permanent'])]
    #[TestWith(['EMP-TEST-004', 'Dinda Putri', 'OPS', 'permanent'])]
    #[TestWith(['EMP-TEST-005', 'Eko Saputra', 'HSE', 'contract'])]
    public function test_seeded_nik_can_register_an_employee_who_appears_in_management(string $nik, string $name, string $departmentCode, string $statusCode): void
    {
        $this->withoutVite();
        $this->seed(DemoSeeder::class);
        $this->seed(RegistrationTestNikSeeder::class);
        $registry = EmployeeRegistry::where('nik', $nik)->firstOrFail();

        $response = $this->post(route('register.store'), [
            'nik' => $nik,
            'username' => 'karyawan.uji',
            'email' => 'karyawan.uji@safetrack.test',
            'password' => 'TestingOnly!2026',
        ]);

        $response->assertRedirect(route('login'))->assertSessionHasNoErrors();
        $this->assertGuest();
        $employee = User::where('employee_registry_id', $registry->id)->firstOrFail();
        $this->assertSame('employee', $employee->role);
        $this->assertSame($name, $employee->name);
        $this->assertSame($registry->department_id, $employee->department_id);
        $this->assertSame($departmentCode, $employee->department->code);
        $this->assertSame($statusCode, $registry->employmentStatus->code);
        $this->assertTrue($employee->is_active);

        $admin = User::where('username', 'admin')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.pengguna.index'))
            ->assertOk()->assertSee('user-row-'.$employee->id, false)->assertSee($nik);
    }

    public function test_rerunning_seeder_preserves_existing_registry_changes(): void
    {
        $this->seed(DemoSeeder::class);
        $this->seed(RegistrationTestNikSeeder::class);
        $registry = EmployeeRegistry::where('nik', 'EMP-TEST-001')->firstOrFail();
        $registry->forceFill(['full_name' => 'Nama uji diperbarui', 'is_active' => false])->save();
        $usersBefore = User::count();

        $this->seed(RegistrationTestNikSeeder::class);

        $this->assertSame(5, EmployeeRegistry::where('nik', 'like', 'EMP-TEST-%')->count());
        $this->assertDatabaseCount('users', $usersBefore);
        $this->assertSame('Nama uji diperbarui', $registry->fresh()->full_name);
        $this->assertFalse($registry->fresh()->is_active);
    }

    public function test_seeder_rejects_production_environment(): void
    {
        $this->app->instance('env', 'production');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NIK uji registrasi hanya boleh ditambahkan pada lingkungan local/testing.');

        app(RegistrationTestNikSeeder::class)->run();
    }
}
