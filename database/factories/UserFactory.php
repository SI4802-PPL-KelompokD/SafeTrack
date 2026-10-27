<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class UserFactory extends Factory {
 public function definition(): array {
  $company=DB::table('company')->insertGetId(['code'=>Str::random(20),'name'=>'Test Company']);
  $dept=DB::table('departments')->insertGetId(['company_id'=>$company,'code'=>'TEST','name'=>'Test Department']);
  $status=DB::table('status_karyawan')->value('id') ?: DB::table('status_karyawan')->insertGetId(['code'=>'test','name'=>'Test']);
  $reg=DB::table('nik')->insertGetId(['nik'=>Str::uuid(),'full_name'=>'Test User','department_id'=>$dept,'employment_status_id'=>$status]);
  return ['employee_registry_id'=>$reg,'department_id'=>$dept,'name'=>fake()->name(),'username'=>fake()->unique()->userName(),'email'=>fake()->unique()->safeEmail(),'password'=>'password','email_verified_at'=>now(),'remember_token'=>Str::random(10)];
 }
 public function unverified(): static {return $this->state(fn()=>['email_verified_at'=>null]);}
}
