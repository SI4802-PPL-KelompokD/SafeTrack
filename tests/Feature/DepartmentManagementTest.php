<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(DemoSeeder::class);
    }

    public function test_admin_can_open_department_management_page(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('admin.departments.index'));

        $response->assertOk()
            ->assertViewIs('departments.index')
            ->assertViewHas('departments')
            ->assertViewHas('counts');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.departments.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_open_department_management_page(): void
    {
        $nonAdmin = User::where('username', 'karyawan')->firstOrFail();

        $response = $this->actingAs($nonAdmin)->get(route('admin.departments.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_create_valid_department(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.departments.store'), [
            'name' => 'Logistik dan Gudang',
        ]);

        $response->assertRedirect(route('admin.departments.index'))
            ->assertSessionHas('success', 'Data master berhasil disimpan.');

        $dept = Department::where('name', 'Logistik dan Gudang')->firstOrFail();
        $this->assertFalse($dept->is_hse);
        $this->assertTrue($dept->is_active);
        $this->assertNotEmpty($dept->code);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'department.created',
            'entity_type' => Department::class,
            'entity_id' => $dept->id,
        ]);
    }

    public function test_empty_or_duplicate_name_is_rejected(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $existing = Department::firstOrFail();

        // Empty name
        $response1 = $this->actingAs($admin)->post(route('admin.departments.store'), [
            'name' => '',
        ]);
        $response1->assertSessionHasErrors(['name']);

        // Duplicate case-insensitive name
        $response2 = $this->actingAs($admin)->post(route('admin.departments.store'), [
            'name' => Str::lower($existing->name),
        ]);
        $response2->assertSessionHasErrors(['name']);
        $this->assertSame(
            'Nama wajib diisi dan tidak boleh sama dengan data yang ada.',
            session('errors')->first('name')
        );
    }

    public function test_admin_can_update_unreferenced_department_name(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        $dept = Department::create([
            'code' => 'TESTUNREF',
            'name' => 'Departemen Bebas',
            'is_hse' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.departments.update', $dept), [
            'name' => 'Departemen Diubah',
        ]);

        $response->assertRedirect(route('admin.departments.index'))
            ->assertSessionHas('success', 'Data master berhasil disimpan.');

        $this->assertSame('Departemen Diubah', $dept->fresh()->name);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'department.updated',
            'entity_type' => Department::class,
            'entity_id' => $dept->id,
        ]);
    }

    public function test_updating_referenced_department_rejects_name_change(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $ops = Department::where('code', 'OPS')->firstOrFail();
        $this->assertTrue($ops->isReferenced());

        $response = $this->actingAs($admin)->put(route('admin.departments.update', $ops), [
            'name' => 'Operasional Tambang Baru',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertSame(
            'Nama tidak dapat diubah karena sudah digunakan.',
            session('errors')->first('name')
        );
        $this->assertSame('Operasional', $ops->fresh()->name);
    }

    public function test_admin_can_delete_unreferenced_department(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        $dept = Department::create([
            'code' => 'FORDELETE',
            'name' => 'Departemen Sementara',
            'is_hse' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.departments.destroy', $dept));

        $response->assertRedirect(route('admin.departments.index'))
            ->assertSessionHas('success', 'Data master dihapus.');

        $this->assertDatabaseMissing('departments', ['id' => $dept->id]);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'department.deleted',
            'entity_type' => Department::class,
            'entity_id' => $dept->id,
        ]);
    }

    public function test_deleting_referenced_department_is_rejected(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $ops = Department::where('code', 'OPS')->firstOrFail();
        $this->assertTrue($ops->isReferenced());

        $response = $this->actingAs($admin)->delete(route('admin.departments.destroy', $ops));

        $response->assertRedirect(route('admin.departments.index'))
            ->assertSessionHas('error', 'Tidak dapat dihapus: data ini masih dirujuk oleh pengguna atau aktivitas, termasuk arsip.');

        $this->assertDatabaseHas('departments', ['id' => $ops->id]);
    }

    public function test_deleting_hse_department_is_rejected_even_if_not_referenced(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        $hseDept = Department::forceCreate([
            'code' => 'HSETEST',
            'name' => 'Departemen HSE Uji',
            'is_hse' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.departments.destroy', $hseDept));

        $response->assertRedirect(route('admin.departments.index'))
            ->assertSessionHas('error', 'Tidak dapat dihapus: data ini masih dirujuk oleh pengguna atau aktivitas, termasuk arsip.');

        $this->assertDatabaseHas('departments', ['id' => $hseDept->id]);
    }

    public function test_audit_failure_rolls_back_department_creation(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        DB::listen(function ($query) {
            if (str_contains($query->sql, 'audit_logs') && str_contains($query->sql, 'insert')) {
                throw new \Exception('Simulated audit logging failure');
            }
        });

        try {
            $this->actingAs($admin)->post(route('admin.departments.store'), [
                'name' => 'Departemen Rollback Test',
            ]);
        } catch (\Throwable $e) {
            // Expected simulated exception
        }

        $this->assertDatabaseMissing('departments', ['name' => 'Departemen Rollback Test']);
    }
}
