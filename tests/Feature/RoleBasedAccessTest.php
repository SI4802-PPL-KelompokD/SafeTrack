<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RoleBasedAccessTest extends TestCase
{
    public function test_permission_matrix_uses_only_trusted_role_and_active_status(): void
    {
        foreach (['employee', 'admin', 'super_admin', '', null, 'hse', 'unknown'] as $role) {
            $user = new User;
            $user->forceFill(['id' => 1, 'role' => $role, 'is_active' => true]);
            $gate = Gate::forUser($user);
            $this->assertSame(in_array($role, ['employee', 'admin', 'super_admin'], true), $gate->allows('access-application'));
            $this->assertSame(in_array($role, ['admin', 'super_admin'], true), $gate->allows('manage-administration'));
            $this->assertSame($role === 'employee', $gate->allows('submit-inspections'));
            $this->assertFalse($gate->allows('undefined-permission'));
            $user->is_active = false;
            $this->assertFalse($gate->allows('access-application'));
            $this->assertFalse($gate->allows('manage-administration'));
            $this->assertFalse($gate->allows('submit-inspections'));
        }
    }

    public function test_guests_receive_401_for_protected_json_requests(): void
    {
        foreach (['/dashboard', '/daily-inspections', '/daily-inspections/create', '/shifts', '/admin/work-areas'] as $url) {
            $this->getJson($url)->assertUnauthorized();
        }
        foreach (['/daily-inspections', '/shifts', '/admin/work-areas'] as $url) {
            $this->postJson($url, [])->assertUnauthorized();
        }
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_employees_cannot_open_or_mutate_administration_even_with_forged_role(): void
    {
        $user = new User;
        $user->forceFill(['id' => 1, 'role' => 'employee', 'is_active' => true]);
        $this->actingAs($user);
        foreach (['/shifts', '/admin/work-areas'] as $url) {
            $this->get($url)->assertForbidden();
            $this->getJson($url.'?role=super_admin')->assertForbidden();
            $this->postJson($url, ['role' => 'admin', 'userId' => 2])->assertForbidden();
        }
    }

    public function test_invalid_roles_and_inactive_users_are_denied_before_reading_data(): void
    {
        foreach ([null, '', 'unknown', 'hse', 'employee'] as $role) {
            $user = new User;
            $user->forceFill(['id' => 1, 'role' => $role, 'is_active' => $role !== 'employee']);
            $this->actingAs($user);
            foreach (['/dashboard', '/daily-inspections', '/daily-inspections/create', '/shifts', '/admin/work-areas'] as $url) {
                $this->getJson($url)->assertForbidden();
            }
            $this->postJson('/daily-inspections', [])->assertForbidden();
        }
    }

    public function test_permission_decisions_change_when_role_changes(): void
    {
        $user = new User;
        $user->forceFill(['role' => 'admin', 'is_active' => true]);
        $this->assertTrue($user->can('manage-administration'));
        $user->role = 'employee';
        $this->assertFalse($user->can('manage-administration'));
        $this->assertTrue($user->can('submit-inspections'));
    }
}
