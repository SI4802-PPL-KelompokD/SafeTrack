<?php

namespace Tests\Browser;

use App\Models\User;
use Database\Seeders\Pbi9EmployeeFixtureSeeder;
use Facebook\WebDriver\WebDriverKeys;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\DuskTestCase;

class Pbi9EmployeeBrowserTest extends DuskTestCase
{
    use DatabaseTruncation;

    private function fixture(): void
    {
        $this->seed(Pbi9EmployeeFixtureSeeder::class);
    }

    private function fixtureUser(string $alias): User
    {
        return User::withTrashed()->where('username', strtolower($alias))->firstOrFail();
    }

    private function login(Browser $browser, string $alias = 'H1'): void
    {
        $browser->visit('/login')->type('login', strtolower($alias))
            ->type('password', Pbi9EmployeeFixtureSeeder::PASSWORD)
            ->click('button[type="submit"]')->waitForLocation('/dashboard');
    }

    private function management(Browser $browser): void
    {
        $this->login($browser);
        $browser->visit('/admin/pengguna')->waitFor('#userTableBody');
    }

    private function row(string $alias): string
    {
        return '#user-row-'.$this->fixtureUser($alias)->id;
    }

    private function manage(Browser $browser, string $alias = 'K1'): void
    {
        $browser->click($this->row($alias).' .btn-action-kelola')->waitFor('#modalManagePengguna.open');
    }

    private function save(Browser $browser): void
    {
        $browser->click('#btnSubmitManageUser')->waitUntilMissing('#modalManagePengguna.open');
    }

    /** @return array{users: array, registry: array} */
    private function snapshot(): array
    {
        return [
            'users' => DB::table('users')->orderBy('id')->get()->map(fn (object $row): array => (array) $row)->all(),
            'registry' => DB::table('nik')->orderBy('id')->get()->map(fn (object $row): array => (array) $row)->all(),
        ];
    }

    private function search(Browser $browser, string $keyword): void
    {
        $browser->waitForReload(function (Browser $page) use ($keyword): void {
            $page->type('#inputSearch', $keyword)->keys('#inputSearch', WebDriverKeys::ENTER);
        });
    }

    private function filter(Browser $browser, string $selector, string $value): void
    {
        $browser->waitForReload(function (Browser $page) use ($selector, $value): void {
            $page->select($selector, $value);
        });
    }

    public function test_tc10_01_lists_only_employees_including_active_and_inactive(): void
    {
        $this->fixture();
        $this->browse(function (Browser $browser): void {
            $this->management($browser);
            $browser->assertSeeIn('#userTableBody', 'Aditya Pratama')->assertSeeIn('#userTableBody', 'Nabila Anindya')
                ->assertMissing($this->row('H1'))->assertMissing($this->row('H2'))->assertMissing($this->row('KD'));
            $this->evidence($browser);
        });
    }

    #[TestWith(['Aditya'])]
    #[TestWith(['EMP-001'])]
    #[TestWith(['aditya@example.test'])]
    public function test_tc10_02_searches_name_nik_and_email_separately(string $keyword): void
    {
        $this->fixture();
        $this->browse(function (Browser $browser) use ($keyword): void {
            $this->management($browser);
            $this->search($browser, $keyword);
            $browser->assertPresent($this->row('K1'))->assertMissing($this->row('K2'))->assertMissing($this->row('K3'));
            $this->evidence($browser);
        });
    }

    public function test_tc10_03_displays_no_matching_data_without_error(): void
    {
        $this->fixture();
        $this->browse(function (Browser $browser): void {
            $this->management($browser);
            $this->search($browser, 'TidakAdaKaryawanXYZ');
            $browser->assertSeeIn('#userTableBody', 'Data tidak ditemukan')->assertMissing($this->row('K1'));
            $this->evidence($browser);
        });
    }

    #[TestWith(['department'])]
    #[TestWith(['status'])]
    public function test_tc10_04_filters_department_and_status_separately(string $criterion): void
    {
        $this->fixture();
        $this->browse(function (Browser $browser) use ($criterion): void {
            $this->management($browser);
            if ($criterion === 'department') {
                $this->filter($browser, '#selectDepartment', (string) DB::table('departments')->where('code', 'OPS')->value('id'));
                $browser->assertPresent($this->row('K1'))->assertPresent($this->row('K3'))->assertMissing($this->row('K2'));
            } else {
                $this->filter($browser, '#selectStatus', '0');
                $browser->assertPresent($this->row('K2'))->assertMissing($this->row('K1'))->assertMissing($this->row('K3'));
            }
            $this->evidence($browser);
        });
    }

    public function test_tc10_05_combines_then_clears_all_search_and_filter_criteria(): void
    {
        $this->fixture();
        $this->browse(function (Browser $browser): void {
            $this->management($browser);
            $this->search($browser, 'Nabila');
            $this->filter($browser, '#selectDepartment', (string) DB::table('departments')->where('code', 'HCGS')->value('id'));
            $this->filter($browser, '#selectStatus', '0');
            $browser->assertPresent($this->row('K2'))->assertMissing($this->row('K1'));
            $this->evidence($browser, 'combined');
            $this->search($browser, '');
            $this->filter($browser, '#selectDepartment', '');
            $this->filter($browser, '#selectStatus', '');
            $browser->assertPresent($this->row('K1'))->assertPresent($this->row('K2'))->assertPresent($this->row('K3'));
            $this->evidence($browser, 'reset');
        });
    }

    public function test_tc10_06_changes_department_persists_after_reload_and_preserves_other_fields(): void
    {
        $this->fixture();
        $employee = $this->fixtureUser('K1');
        $before = $employee->getRawOriginal();
        $registryBefore = $employee->employeeRegistry->getRawOriginal();
        $engineering = DB::table('departments')->where('code', 'ENG')->value('id');
        $this->browse(function (Browser $browser) use ($engineering): void {
            $this->management($browser);
            $this->manage($browser);
            $browser->select('#modalDeptSelect', (string) $engineering);
            $this->evidence($browser, 'editing');
            $this->save($browser);
            $browser->refresh()->waitFor('#userTableBody')->assertSeeIn($this->row('K1').' .row-dept', 'Engineering');
            $this->evidence($browser);
        });
        $after = $employee->fresh()->getRawOriginal();
        $registryAfter = $employee->employeeRegistry->fresh()->getRawOriginal();
        $this->assertSame($engineering, $after['department_id']);
        $this->assertSame($engineering, $registryAfter['department_id']);
        foreach (['department_id', 'updated_at'] as $field) {
            unset($before[$field], $after[$field]);
        }
        foreach (['department_id', 'updated_at'] as $field) {
            unset($registryBefore[$field], $registryAfter[$field]);
        }
        $this->assertSame($before, $after);
        $this->assertSame($registryBefore, $registryAfter);
    }

    #[TestWith(['K1', '0', 'Nonaktif'])]
    #[TestWith(['K2', '1', 'Aktif'])]
    public function test_tc10_08_tc10_09_changes_account_status_and_persists_after_reload(string $alias, string $status, string $label): void
    {
        $this->fixture();
        $this->browse(function (Browser $browser) use ($alias, $status, $label): void {
            $this->management($browser);
            $this->manage($browser, $alias);
            $browser->select('#modalStatusSelect', $status);
            $this->save($browser);
            $browser->refresh()->waitFor('#userTableBody')->assertSeeIn($this->row($alias).' .row-status-container', $label);
            $this->evidence($browser);
        });
        $this->assertSame($status === '1', $this->fixtureUser($alias)->is_active);
        $this->assertSame($status === '1', $this->fixtureUser($alias)->employeeRegistry->is_active);
    }

    #[TestWith(['K2'])]
    #[TestWith(['K1'])]
    public function test_tc10_10_tc10_21_inactive_and_soft_deleted_users_cannot_login(string $alias): void
    {
        $this->fixture();
        if ($alias === 'K1') {
            $this->fixtureUser('K1')->delete();
        }
        $this->browse(function (Browser $browser) use ($alias): void {
            $browser->visit('/login')->type('login', strtolower($alias))->type('password', Pbi9EmployeeFixtureSeeder::PASSWORD)
                ->click('button[type="submit"]')->waitForText($alias === 'K2' ? 'Akun Anda berstatus nonaktif' : 'Username/email atau password salah.')
                ->assertPathIs('/login')->assertGuest();
            $this->evidence($browser, 'login-rejected');
            $browser->visit('/dashboard')->assertPathIs('/login');
            $this->evidence($browser);
        });
        $this->assertSame(0, DB::table(config('session.table'))->where('user_id', $this->fixtureUser($alias)->id)->count());
    }

    #[TestWith(['manage'])]
    #[TestWith(['delete'])]
    public function test_tc10_11_cancel_closes_dialog_without_data_or_metadata_changes(string $dialog): void
    {
        $this->fixture();
        $before = $this->snapshot();
        $this->browse(function (Browser $browser) use ($dialog): void {
            $this->management($browser);
            if ($dialog === 'manage') {
                $this->manage($browser);
                $browser->select('#modalStatusSelect', '0')->select('#modalDeptSelect', (string) DB::table('departments')->where('code', 'ENG')->value('id'));
                $this->evidence($browser, 'temporary');
                $browser->click('#modalManagePengguna .btn-modal-cancel')->waitUntilMissing('#modalManagePengguna.open');
            } else {
                $browser->click($this->row('K1').' .btn-action-hapus')->waitFor('#modalDeletePengguna.open')->type('#inputDeletionReason', 'Alasan sementara.');
                $this->evidence($browser, 'temporary');
                $browser->click('#modalDeletePengguna .btn-modal-cancel')->waitUntilMissing('#modalDeletePengguna.open');
            }
            $browser->refresh()->waitFor('#userTableBody')->assertPresent($this->row('K1'));
            $this->evidence($browser);
        });
        $this->assertSame($before, $this->snapshot());
    }

    #[TestWith([''])]
    #[TestWith(['   '])]
    public function test_tc10_12_delete_dialog_rejects_empty_and_whitespace_reasons(string $reason): void
    {
        $this->fixture();
        $before = $this->snapshot();
        $this->browse(function (Browser $browser) use ($reason): void {
            $this->management($browser);
            $browser->click($this->row('K1').' .btn-action-hapus')->waitFor('#modalDeletePengguna.open')
                ->type('#inputDeletionReason', $reason)->click('#btnConfirmDelete');
            if ($reason === '') {
                $browser->assertScript('document.getElementById("inputDeletionReason").validity.valueMissing');
            } else {
                $browser->waitFor('#modalDeleteErrorAlert')->assertSeeIn('#modalDeleteErrorAlert', 'Alasan penghapusan akun wajib diisi.');
            }
            $browser->assertVisible('#modalDeletePengguna.open');
            $this->evidence($browser);
        });
        $this->assertSame($before, $this->snapshot());
    }

    public function test_tc10_13_soft_delete_records_reason_actor_server_time_and_hides_employee(): void
    {
        $this->fixture();
        $started = now()->subSecond();
        $this->browse(function (Browser $browser): void {
            $this->management($browser);
            $browser->click($this->row('K1').' .btn-action-hapus')->waitFor('#modalDeletePengguna.open')
                ->type('#inputDeletionReason', 'Karyawan sudah tidak bekerja.');
            $this->evidence($browser, 'confirmation');
            $browser->click('#btnConfirmDelete')->waitUntilMissing($this->row('K1'));
            $browser->refresh()->waitFor('#userTableBody')->assertMissing($this->row('K1'));
            $this->evidence($browser);
        });
        $employee = $this->fixtureUser('K1');
        $this->assertTrue($employee->trashed());
        $this->assertFalse($employee->is_active);
        $this->assertSame('Karyawan sudah tidak bekerja.', $employee->deletion_reason);
        $this->assertSame($this->fixtureUser('H1')->id, $employee->deleted_by);
        $this->assertGreaterThanOrEqual($started->timestamp, $employee->deleted_at->timestamp);
        $this->assertLessThanOrEqual(now()->timestamp, $employee->deleted_at->timestamp);
    }

    public function test_tc10_19_storage_failure_is_visible_and_preserves_data(): void
    {
        $this->fixture();
        $before = $this->snapshot();
        DB::unprepared("CREATE TRIGGER pbi9_fail_registry BEFORE UPDATE ON nik FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'PBI9 simulated storage failure'");
        try {
            $this->browse(function (Browser $browser): void {
                $this->management($browser);
                $this->manage($browser);
                $browser->select('#modalDeptSelect', (string) DB::table('departments')->where('code', 'ENG')->value('id'))->click('#btnSubmitManageUser')
                    ->waitFor('#modalManageErrorAlert')->assertSeeIn('#modalManageErrorAlert', 'Terjadi kesalahan sistem')->assertVisible('#modalManagePengguna.open');
                $this->evidence($browser);
            });
            $this->assertSame($before, $this->snapshot());
        } finally {
            DB::unprepared('DROP TRIGGER IF EXISTS pbi9_fail_registry');
        }
    }

    public function test_tc10_20_displays_empty_employee_list_without_error(): void
    {
        $this->fixture();
        User::where('role', 'employee')->delete();
        $this->browse(function (Browser $browser): void {
            $this->management($browser);
            $browser->assertSee('Belum ada data karyawan')->assertSeeIn('#statTotalUsers', '0');
            $this->evidence($browser);
        });
    }

    public function test_tc10_22_deactivation_ends_existing_session_on_next_protected_request(): void
    {
        $this->fixture();
        $this->browse(function (Browser $admin, Browser $employee): void {
            $this->login($employee, 'K1');
            $employee->assertAuthenticatedAs($this->fixtureUser('K1'));
            $this->assertGreaterThan(0, DB::table(config('session.table'))->where('user_id', $this->fixtureUser('K1')->id)->count());
            $this->evidence($employee, 'employee-before');
            $this->management($admin);
            $this->manage($admin);
            $admin->select('#modalStatusSelect', '0');
            $this->save($admin);
            $this->evidence($admin, 'admin-deactivated');
            $employee->visit('/dashboard')->waitForLocation('/login')->assertGuest();
            $employee->visit('/dashboard')->assertPathIs('/login');
            $this->evidence($employee, 'employee-rejected');
            $admin->assertAuthenticatedAs($this->fixtureUser('H1'));
        });
        $this->assertSame(0, DB::table(config('session.table'))->where('user_id', $this->fixtureUser('K1')->id)->count());
    }
}
