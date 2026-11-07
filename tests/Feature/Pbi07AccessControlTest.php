<?php

namespace Tests\Feature;

use App\Models\HazardReport;
use App\Models\InspectionQuestion;
use App\Models\User;
use App\Models\WorkArea;
use App\Services\PenggunaService;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class Pbi07AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public static function personas(): array
    {
        return ['non-HSE' => ['employee'], 'HSE' => ['hseEmployee']];
    }

    public static function adminPages(): array
    {
        return array_map(fn (string $name): array => [$name], [
            'admin.pengguna.index', 'shifts.index', 'admin.data-master',
            'admin.work-areas.index', 'admin.work-areas.show', 'sap.index',
        ]);
    }

    public static function employeeAdminPages(): array
    {
        $cases = [];
        foreach (self::personas() as $persona => [$state]) {
            foreach (self::adminPages() as [$name]) {
                $cases[$persona.' '.$name] = [$state, $name];
            }
        }

        return $cases;
    }

    #[DataProvider('personas')]
    public function test_t_c07_01_employees_login_without_administration(string $state): void
    {
        $user = User::factory()->{$state}()->create();
        $this->post(route('login.submit'), ['login' => $user->username, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))->assertOk()
            ->assertSee($state === 'hseEmployee' ? 'Karyawan HSE' : 'Karyawan')
            ->assertDontSee('href="'.route('admin.pengguna.index').'"', false)
            ->assertDontSee('href="'.route('shifts.index').'"', false)
            ->assertDontSee('>Administrasi<', false);
    }

    public function test_t_c07_02_admin_dashboard_renders_administration_and_read_only_badge(): void
    {
        $user = User::factory()->admin()->create();
        $this->post(route('login.submit'), ['login' => $user->username, 'password' => 'password'])
            ->assertRedirect(route('shifts.index'));

        $this->get(route('dashboard'))->assertOk()->assertSee('Administrasi')
            ->assertSee('href="'.route('admin.pengguna.index').'"', false)->assertSee('Admin/HSE')
            ->assertDontSee('Ganti Akun');
    }

    #[DataProvider('adminPages')]
    public function test_t_c07_03_admin_can_open_each_administrative_page(string $name): void
    {
        $user = User::factory()->admin()->create();
        $parameters = $name === 'admin.work-areas.show' ? [WorkArea::factory()->create()] : [];

        $this->actingAs($user)->get(route($name, $parameters))->assertOk();
    }

    #[DataProvider('personas')]
    public function test_t_c07_04_both_employee_personas_can_open_shared_pages(string $state): void
    {
        $user = User::factory()->{$state}()->create();
        $report = HazardReport::factory()->create(['reporter_id' => $user->id]);
        $area = WorkArea::factory()->create();
        $this->actingAs($user);

        foreach (['dashboard', 'hazards.index', 'hazards.mine', 'daily-inspections.index', 'daily-inspections.create'] as $name) {
            $this->get(route($name))->assertOk();
        }
        $this->get(route('hazards.show', $report))->assertOk();
        $this->getJson(route('work-areas.active-questions', $area))->assertOk();
    }

    #[DataProvider('employeeAdminPages')]
    public function test_t_c07_05_employee_admin_urls_render_safe_403(string $state, string $name): void
    {
        $user = User::factory()->{$state}()->create();
        $parameters = $name === 'admin.work-areas.show' ? [WorkArea::factory()->create()] : [];

        $this->actingAs($user)->get(route($name, $parameters))->assertForbidden()
            ->assertViewIs('errors.403')->assertSee('Akses ditolak')->assertSee('Lihat Laporan Saya')
            ->assertSee($state === 'hseEmployee' ? 'Karyawan HSE' : 'Karyawan')
            ->assertDontSee('href="'.route('admin.pengguna.index').'"', false);
    }

    #[DataProvider('personas')]
    public function test_t_c07_06_07_admin_writes_return_exact_403_json_and_preserve_data(string $state): void
    {
        $user = User::factory()->{$state}()->create();
        $target = User::factory()->employee()->create();
        $area = WorkArea::factory()->create();
        $question = InspectionQuestion::factory()->for($area)->create();
        $shift = DB::table('shift')->insertGetId(['code' => 'PAGI', 'name' => 'Pagi', 'starts_at' => '07:00', 'ends_at' => '15:00']);
        $parameters = ['pengguna' => $target->id, 'workArea' => $area->id, 'work_area' => $area->id, 'question' => $question->id, 'shift' => $shift];
        $tables = ['users', 'nik', 'area_kerja', 'inspection_questions', 'shift', 'audit_logs', 'history_sap', 'pencapaian'];
        $before = [];
        foreach ($tables as $table) {
            $before[$table] = DB::table($table)->orderBy('id')->get()->toJson();
        }
        $this->actingAs($user);

        foreach (Route::getRoutes() as $route) {
            if (! in_array('role:admin,super_admin', $route->gatherMiddleware(), true)) {
                continue;
            }
            foreach (array_intersect(['POST', 'PUT', 'PATCH', 'DELETE'], $route->methods()) as $method) {
                $this->json($method, route($route->getName(), array_intersect_key($parameters, array_flip($route->parameterNames()))), [
                    'role' => 'admin', 'name' => 'Unauthorized change', 'is_active' => false,
                ])->assertForbidden()->assertExactJson(['message' => 'Akses ditolak.']);
            }
        }
        foreach ($before as $table => $snapshot) {
            $this->assertSame($snapshot, DB::table($table)->orderBy('id')->get()->toJson(), $table);
        }
        $this->assertDatabaseHas('users', ['id' => $target->id, 'role' => 'employee', 'is_active' => true]);
        $this->assertDatabaseMissing('shift', ['name' => 'Unauthorized change']);
    }

    public function test_t_c07_08_09_guests_are_redirected_or_receive_401_and_public_pages_work(): void
    {
        foreach (Route::getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true) || ! $this->isProtected($route->gatherMiddleware())) {
                continue;
            }
            $parameters = array_fill_keys($route->parameterNames(), 999999);
            $url = route($route->getName(), $parameters);
            if (str_starts_with($route->uri(), 'api/')) {
                $this->getJson($url)->assertUnauthorized();
            } else {
                $this->get($url)->assertRedirect(route('login'));
                $this->getJson($url)->assertUnauthorized();
            }
        }
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
    }

    public function test_t_c07_10_11_role_mass_assignment_and_forged_request_values_cannot_escalate(): void
    {
        $user = User::factory()->employee()->create();
        $user->update(['role' => 'admin']);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'employee']);
        $this->actingAs($user)->withHeaders(['X-Role' => 'admin', 'Role' => 'super_admin'])
            ->getJson(route('admin.pengguna.index', ['role' => 'admin']))
            ->assertForbidden()->assertExactJson(['message' => 'Akses ditolak.']);
    }

    public function test_t_c07_12_employee_report_detail_does_not_render_admin_controls(): void
    {
        $user = User::factory()->employee()->create();
        $report = HazardReport::factory()->create(['reporter_id' => $user->id]);

        $this->actingAs($user)->get(route('hazards.show', $report))->assertOk()
            ->assertDontSee('>Verifikasi<', false)->assertDontSee('>Ubah Status<', false)
            ->assertDontSee('>Hapus<', false);
    }

    public static function accountEndings(): array
    {
        return ['TC07-13 nonaktif' => ['inactive'], 'TC07-14 dihapus' => ['deleted']];
    }

    #[DataProvider('accountEndings')]
    public function test_t_c07_13_14_account_changes_end_existing_session_with_same_banner(string $change): void
    {
        $this->freezeTime();
        $user = User::factory()->employee()->create();
        $this->post(route('login.submit'), ['login' => $user->username, 'password' => 'password'])->assertRedirect();
        DB::table('user_sessions')->insert([
            'id' => 'other-device', 'user_id' => $user->id, 'payload' => '', 'last_activity' => now()->timestamp,
        ]);
        if ($change === 'inactive') {
            $user->update(['is_active' => false]);
        } else {
            $user->delete();
        }
        Auth::forgetGuards();

        $this->get(route('dashboard'))->assertRedirect(route('login'))
            ->assertSessionHas('status', 'session_ended');
        $this->assertGuest();
        $this->assertDatabaseHas('user_sessions', ['id' => 'other-device', 'is_active' => false, 'logout_at' => now()]);
        $this->get(route('login'))->assertOk()->assertSee('Sesi Anda telah berakhir.')
            ->assertSee('Akun Anda sudah tidak aktif. Silakan hubungi Admin/HSE untuk informasi lebih lanjut.');
    }

    public function test_t_c07_15_role_demotion_takes_effect_without_logging_in_again(): void
    {
        $user = User::factory()->admin()->create();
        $this->post(route('login.submit'), ['login' => $user->username, 'password' => 'password'])->assertRedirect();
        $user->forceFill(['role' => 'employee'])->save();
        Auth::forgetGuards();

        $this->get(route('admin.pengguna.index'))->assertForbidden()->assertViewIs('errors.403');
        $this->assertAuthenticated();
    }

    public function test_t_c07_16_debug_403_never_discloses_exception_details_and_handles_guests(): void
    {
        config(['app.debug' => true]);
        Route::get('/test-denied', fn () => abort(403, 'SECRET exception detail'));

        $this->get('/test-denied')->assertForbidden()->assertViewIs('errors.403')
            ->assertSee('Masuk ke SafeTrack')->assertDontSee('SECRET')->assertDontSee('Stack trace')
            ->assertDontSee(base_path())->assertDontSee('class="app-sidebar"', false);
        $user = User::factory()->hseEmployee()->create();
        $this->actingAs($user)->get(route('admin.pengguna.index'))->assertForbidden()
            ->assertSee('Kelola Karyawan')->assertDontSee('Stack trace')->assertDontSee(base_path());
    }

    public function test_t_c07_17_routes_are_protected_by_auth_and_admin_role(): void
    {
        $public = ['login', 'login.submit', 'register', 'register.store', 'demo.login', 'api.login'];
        foreach (Route::getRoutes() as $route) {
            if (in_array($route->getName(), $public, true) || in_array($route->uri(), ['/', 'up', 'sanctum/csrf-cookie', 'storage/{path}'], true)) {
                continue;
            }
            $middleware = $route->gatherMiddleware();
            $this->assertTrue($this->isProtected($middleware), $route->uri());
            $this->assertContains('active', $middleware, $route->uri());
            if (str_starts_with($route->uri(), 'admin/') || in_array($route->getName(), ['admin.data-master', 'sap.index', 'sap.recalculate'], true) || str_starts_with($route->getName() ?? '', 'shifts.')) {
                $this->assertContains('role:admin,super_admin', $middleware, $route->uri());
            }
        }
    }

    public function test_demo_login_flag_hides_buttons_and_disables_endpoint(): void
    {
        config(['app.demo_login' => false]);
        $this->get(route('login'))->assertOk()->assertDontSee('atau jelajahi dengan akun demo')
            ->assertDontSee('DemoOnly!2026');
        $this->post(route('demo.login', 'admin'), ['role' => 'admin'])->assertNotFound();
        $this->assertGuest();
        $user = User::factory()->employee()->create();
        $this->actingAs($user)->post(route('demo.login', 'admin'))->assertNotFound();
    }

    public function test_demo_login_uses_server_account_and_regenerates_session(): void
    {
        $this->seed(DemoSeeder::class);
        config(['app.demo_login' => true]);
        $this->get(route('login'))->assertOk()->assertSee('atau jelajahi dengan akun demo');
        $oldSession = session()->getId();

        $this->post(route('demo.login', 'karyawan'), ['role' => 'admin'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs(User::where('email', 'karyawan@safetrack.test')->firstOrFail());
        $this->assertSame('employee', Auth::user()->role);
        $this->assertNotSame($oldSession, session()->getId());
        $this->get(route('admin.pengguna.index'))->assertForbidden();
    }

    public function test_authenticated_users_visiting_guest_pages_go_to_dashboard(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user)->get(route('login'))->assertRedirect(route('dashboard'));
        $this->get(route('register'))->assertRedirect(route('dashboard'));
    }

    public function test_inactive_and_deleted_factory_states_cannot_access_shared_pages(): void
    {
        foreach (['inactive', 'softDeleted'] as $state) {
            $user = User::factory()->{$state}()->create();
            $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('login'))
                ->assertSessionHas('status', 'session_ended');
            $this->assertGuest();
        }
    }

    public function test_revoked_account_cannot_use_mobile_token(): void
    {
        $user = User::factory()->employee()->create();
        $token = $user->createToken('mobile')->plainTextToken;
        $user->update(['is_active' => false]);

        $this->withToken($token)->getJson(route('api.me'))->assertUnauthorized();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_registration_cannot_assign_admin_role_from_client(): void
    {
        $this->seed(DemoSeeder::class);
        $this->post(route('register.store'), [
            'nik' => 'DEMO004', 'username' => 'newemployee', 'email' => 'newemployee@example.test',
            'password' => 'Password123!', 'role' => 'admin',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', ['username' => 'newemployee', 'role' => 'employee']);
    }

    public function test_inspection_ownership_uses_server_identity_and_cannot_be_overridden(): void
    {
        $user = User::factory()->employee()->create();
        $other = User::factory()->hseEmployee()->create();
        $area = WorkArea::factory()->create();
        $question = InspectionQuestion::factory()->for($area)->create();
        $shift = DB::table('shift')->insertGetId(['code' => 'PAGI', 'name' => 'Pagi', 'starts_at' => '07:00', 'ends_at' => '15:00']);
        $this->actingAs($user)->post(route('daily-inspections.store'), [
            'inspector_id' => $other->id, 'department_id' => $other->department_id,
            'work_area_id' => $area->id, 'shift_id' => $shift, 'inspection_date' => now()->toDateString(),
            'answers' => [['question_id' => $question->id, 'answer' => 'yes']],
        ])->assertRedirect(route('daily-inspections.index'));

        $this->assertDatabaseHas('daily_inspection', ['inspector_id' => $user->id, 'department_id' => $user->department_id]);
        $this->assertDatabaseMissing('daily_inspection', ['inspector_id' => $other->id]);
        $this->actingAs($other)->get(route('daily-inspections.index', ['inspector_id' => $user->id]))
            ->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('DailyInspections/Index', false)->where('inspections.total', 0));
    }

    public function test_revoked_session_stays_invalid_after_account_reactivation(): void
    {
        $user = User::factory()->employee()->create();
        $user->createToken('mobile');
        $this->post(route('login.submit'), ['login' => $user->username, 'password' => 'password'])->assertRedirect();
        DB::table('user_sessions')->insert([
            'id' => session()->getId(), 'user_id' => $user->id, 'payload' => '', 'last_activity' => now()->timestamp,
        ]);
        $sessionId = session()->getId();
        $user->update(['is_active' => false]);
        app(PenggunaService::class)->revokeUserSessions($user);
        $user->update(['is_active' => true]);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseHas('user_sessions', ['id' => $sessionId, 'is_active' => false]);

        $this->withCookie(config('session.cookie'), $sessionId)->get(route('dashboard'))->assertRedirect(route('login'))
            ->assertSessionHas('status', 'session_ended');
        $this->assertGuest();
    }

    private function isProtected(array $middleware): bool
    {
        return in_array('auth', $middleware, true) || in_array('auth:sanctum', $middleware, true);
    }
}
