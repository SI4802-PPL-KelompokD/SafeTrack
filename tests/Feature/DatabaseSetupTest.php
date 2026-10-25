<?php
namespace Tests\Feature;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class DatabaseSetupTest extends TestCase {
 use RefreshDatabase;
 public function test_default_seeder_is_repeatable_and_does_not_create_test_accounts(): void {
  $this->seed(DatabaseSeeder::class);$this->seed(DatabaseSeeder::class);
  $this->assertDatabaseCount('hazard_categories',2);
  $this->assertDatabaseCount('employment_statuses',2);
  $this->assertDatabaseCount('users',0);
 }
 public function test_demo_seeder_is_repeatable_and_passwords_are_hashed(): void {
  $this->seed(DemoSeeder::class);$this->seed(DemoSeeder::class);
  $this->assertDatabaseCount('users',3);
  $this->assertDatabaseCount('companies',1);
  $this->assertTrue(Hash::check('DemoOnly!2026',User::where('username','karyawan')->firstOrFail()->password));
 }
 public function test_database_rejects_a_second_super_admin(): void {
  $this->seed(DemoSeeder::class);
  $this->expectException(QueryException::class);
  $this->expectExceptionCode('23000');
  DB::table('users')->where('username','hse')->update(['role'=>'super_admin']);
 }
 public function test_database_rejects_an_unknown_department_company(): void {
  $this->expectException(QueryException::class);
  $this->expectExceptionCode('23000');
  DB::table('departments')->insert(['company_id'=>999999,'code'=>'INVALID','name'=>'Invalid']);
 }
 public function test_user_factory_can_create_users_with_required_identity_data(): void {
  $user=User::factory()->create();
  $this->assertDatabaseHas('employee_registry',['id'=>$user->employee_registry_id]);
  $this->assertDatabaseHas('departments',['id'=>$user->department_id]);
 }
}
