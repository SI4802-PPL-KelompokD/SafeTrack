<?php

namespace Tests\Feature;

use App\Models\EmployeeRegistry;
use App\Models\HazardReport;
use App\Models\User;
use Database\Seeders\Pbi9EmployeeFixtureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Tests\TestCase;

class Pbi9EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): void
    {
        $this->seed(Pbi9EmployeeFixtureSeeder::class);
    }

    private function user(string $alias): User
    {
        return User::withTrashed()->where('username', strtolower($alias))->firstOrFail();
    }

    /** @return array{users: array, registry: array} */
    private function snapshot(): array
    {
        return [
            'users' => DB::table('users')->orderBy('id')->get()->map(fn (object $row): array => (array) $row)->all(),
            'registry' => DB::table('nik')->orderBy('id')->get()->map(fn (object $row): array => (array) $row)->all(),
        ];
    }

    /** @return array{department_id: int, employment_status_id: int, is_active: bool} */
    private function payload(string $mutation, ?User $employee = null): array
    {
        $employee ??= $this->user('K1');

        return [
            'department_id' => $mutation === 'department' ? DB::table('departments')->where('code', 'ENG')->value('id') : $employee->department_id,
            'employment_status_id' => $employee->employeeRegistry->employment_status_id,
            'is_active' => $mutation !== 'deactivate',
        ];
    }

    private function mutate(string $mutation, int $id): TestResponse
    {
        if ($mutation === 'delete') {
            return $this->deleteJson(route('admin.pengguna.destroy', $id), ['deletion_reason' => 'Karyawan sudah tidak bekerja.']);
        }

        return $this->putJson(route('admin.pengguna.update', $id), $this->payload($mutation, User::withTrashed()->find($id)));
    }

    #[TestWith([null])]
    #[TestWith([999999])]
    public function test_tc10_07_rejects_empty_or_unknown_department_without_changes(?int $department): void
    {
        $this->fixture();
        $this->actingAs($this->user('H1'));
        $before = $this->snapshot();
        $payload = $this->payload('department');
        $payload['department_id'] = $department;

        $response = $this->putJson(route('admin.pengguna.update', $this->user('K1')), $payload);

        $response->assertUnprocessable()->assertJsonValidationErrors('department_id');
        $this->assertSame($before, $this->snapshot());
    }

    #[TestWith([''])]
    #[TestWith(['   '])]
    public function test_tc10_12_rejects_empty_or_whitespace_reason_without_changes(string $reason): void
    {
        $this->fixture();
        $this->actingAs($this->user('H1'));
        $before = $this->snapshot();

        $response = $this->deleteJson(route('admin.pengguna.destroy', $this->user('K1')), ['deletion_reason' => $reason]);

        $response->assertUnprocessable()->assertJsonValidationErrors('deletion_reason');
        $this->assertSame($before, $this->snapshot());
        $this->assertNotSoftDeleted($this->user('K1'));
    }

    public function test_tc10_14_soft_delete_preserves_hazard_report_and_reporter_relationship(): void
    {
        $this->fixture();
        $employee = $this->user('K3');
        $report = HazardReport::where('reporter_id', $employee->id)->firstOrFail();
        $before = $report->getRawOriginal();

        $response = $this->actingAs($this->user('H1'))->deleteJson(route('admin.pengguna.destroy', $employee), [
            'deletion_reason' => 'Karyawan sudah tidak bekerja.',
        ]);

        $response->assertOk();
        $this->assertSoftDeleted($employee);
        $this->assertSame($before, $report->fresh()->getRawOriginal());
        $this->assertSame($employee->id, $report->fresh()->reporter->id);
        $this->assertTrue($report->fresh()->reporter->trashed());
    }

    #[TestWith(['list'])]
    #[TestWith(['department'])]
    #[TestWith(['deactivate'])]
    #[TestWith(['activate'])]
    #[TestWith(['delete'])]
    public function test_tc10_15_employee_cannot_access_management_or_mutate(string $mutation): void
    {
        $this->fixture();
        $this->actingAs($this->user('K3'));
        $before = $this->snapshot();

        $response = $mutation === 'list' ? $this->get(route('admin.pengguna.index')) : $this->mutate($mutation, $this->user('K1')->id);

        $response->assertForbidden();
        $this->assertSame($before, $this->snapshot());
    }

    #[TestWith(['list'])]
    #[TestWith(['department'])]
    #[TestWith(['deactivate'])]
    #[TestWith(['activate'])]
    #[TestWith(['delete'])]
    public function test_tc10_16_guests_must_authenticate_before_management(string $mutation): void
    {
        $this->fixture();
        $before = $this->snapshot();

        $response = $mutation === 'list' ? $this->get(route('admin.pengguna.index')) : $this->mutate($mutation, $this->user('K1')->id);

        if ($mutation === 'list') {
            $response->assertRedirect(route('login'));
        } else {
            $response->assertUnauthorized();
        }
        $this->assertSame($before, $this->snapshot());
        $this->assertGuest();
    }

    /** @return array<string, array{string, string}> */
    public static function protectedTargets(): array
    {
        $cases = [];
        foreach (['H1', 'H2', 'KX', 'KD'] as $alias) {
            foreach (['department', 'deactivate', 'activate', 'delete'] as $mutation) {
                $cases[$alias.' '.$mutation] = [$alias, $mutation];
            }
        }

        return $cases;
    }

    #[DataProvider('protectedTargets')]
    public function test_tc10_17_tc10_18_rejects_admin_unknown_and_deleted_targets(string $alias, string $mutation): void
    {
        $this->fixture();
        $this->actingAs($this->user('H1'));
        $before = $this->snapshot();
        $targetId = $alias === 'KX' ? 999999 : $this->user($alias)->id;

        $response = $this->mutate($mutation, $targetId);

        if (in_array($alias, ['KX', 'KD'], true)) {
            $response->assertNotFound();
        } else {
            $response->assertUnprocessable()->assertJsonPath('message', $mutation === 'delete'
                ? 'Hanya akun karyawan yang dapat dihapus. Akun Admin tidak dapat dihapus di sini.'
                : 'Hanya akun karyawan yang dapat dikelola. Akun Admin bukan merupakan target pengelolaan.');
        }
        $this->assertSame($before, $this->snapshot());
    }

    #[TestWith(['department'])]
    #[TestWith(['deactivate'])]
    #[TestWith(['activate'])]
    #[TestWith(['delete'])]
    public function test_tc10_19_storage_failure_rolls_back_every_partial_change(string $mutation): void
    {
        $this->fixture();
        $this->actingAs($this->user('H1'));
        $before = $this->snapshot();
        $dispatcher = EmployeeRegistry::getEventDispatcher();
        EmployeeRegistry::setEventDispatcher(clone $dispatcher);
        EmployeeRegistry::updating(static function (EmployeeRegistry $registry): void {
            throw new RuntimeException('PBI9 simulated storage failure');
        });

        try {
            $response = $this->mutate($mutation, $this->user($mutation === 'activate' ? 'K2' : 'K1')->id);

            $response->assertInternalServerError()->assertJsonStructure(['message']);
            $this->assertStringContainsString('Terjadi kesalahan sistem', $response->json('message'));
            $this->assertSame($before, $this->snapshot());
        } finally {
            EmployeeRegistry::setEventDispatcher($dispatcher);
        }
    }
}
