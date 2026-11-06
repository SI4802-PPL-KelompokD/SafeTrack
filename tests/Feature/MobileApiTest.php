<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Reward;
use App\Models\SapMonthlySummary;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DemoSeeder::class);
        Carbon::setTestNow('2026-10-26 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_login_successful_with_valid_credentials(): void
    {
        $department = Department::where('code', 'OPS')->first();

        $user = User::factory()->create([
            'username' => 'mobileuser_' . uniqid(),
            'email' => 'mobile_' . uniqid() . '@safetrack.local',
            'password' => Hash::make('Secret123!'),
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'login' => $user->username,
            'password' => 'Secret123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'user' => [
                        'id',
                        'name',
                        'username',
                        'email',
                        'role',
                        'department' => ['id', 'name', 'is_hse'],
                    ],
                ],
            ]);

        $this->assertEquals('Bearer', $response->json('data.token_type'));
        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'login' => 'nonexistent_user',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Username/email atau password salah.',
            ]);
    }

    public function test_login_fails_if_user_is_inactive(): void
    {
        $user = User::factory()->create([
            'username' => 'inactive_' . uniqid(),
            'password' => Hash::make('Secret123!'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'login' => $user->username,
            'password' => 'Secret123!',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Akun Anda berstatus nonaktif. Silakan hubungi administrator.',
            ]);
    }

    public function test_me_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/me');
        $response->assertStatus(401);
    }

    public function test_me_endpoint_returns_user_profile(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_sap_summary_endpoint_for_non_hse_reward(): void
    {
        $dept = Department::where('code', 'OPS')->first();

        $user = User::factory()->create([
            'department_id' => $dept->id,
            'is_active' => true,
        ]);

        $reward = Reward::create([
            'name' => 'Sepeda Gunung Mobile Test',
            'required_points' => 10,
            'description' => 'Hadiah capaian 10 poin SAP',
            'is_active' => true,
        ]);

        SapMonthlySummary::create([
            'user_id' => $user->id,
            'department_id' => $dept->id,
            'period' => '2026-10-01',
            'is_hse_snapshot' => false,
            'hazard_count' => 5,
            'inspection_count' => 3,
            'safety_talk_count' => 2,
            'resolution_count' => 0,
            'total_points' => 10,
            'reward_eligible' => true,
            'eligible_reward_id' => $reward->id,
            'calculated_at' => now(),
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/sap/summary?year=2026&month=10');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'period' => '2026-10-01',
                    'is_hse' => false,
                    'stats' => [
                        'total_points' => 10,
                        'hazard_count' => 5,
                    ],
                    'conversion' => [
                        'type' => 'reward',
                        'target_met' => null,
                        'reward_eligible' => true,
                        'reward' => [
                            'id' => $reward->id,
                            'name' => 'Sepeda Gunung Mobile Test',
                            'required_points' => 10,
                        ],
                    ],
                ],
            ]);
    }

    public function test_logout_revokes_token(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $token = $user->createToken('test-token')->plainTextToken;

        // Logout
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Logout berhasil. Token telah dicabut.',
            ]);

        // Request selanjutnya dengan token yang sudah dicabut harus 401
        app('auth')->forgetGuards();
        $subsequentResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/me');

        $subsequentResponse->assertStatus(401);
    }
}
