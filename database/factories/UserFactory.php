<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class UserFactory extends Factory {
 public function definition(): array {
  $dept = DB::table('departments')->value('id') ?: DB::table('departments')->insertGetId(['code' => 'TEST' . Str::upper(Str::random(5)), 'name' => 'Test Department']);
  $status=DB::table('status_karyawan')->value('id') ?: DB::table('status_karyawan')->insertGetId(['code'=>'test','name'=>'Test']);
  $reg=DB::table('nik')->insertGetId(['nik'=>Str::uuid(),'full_name'=>'Test User','department_id'=>$dept,'employment_status_id'=>$status]);
  return ['employee_registry_id'=>$reg,'department_id'=>$dept,'name'=>fake()->name(),'username'=>fake()->unique()->userName(),'email'=>fake()->unique()->safeEmail(),'password'=>'password','is_active'=>true,'email_verified_at'=>now(),'remember_token'=>Str::random(10)];
 }
 public function unverified(): static {return $this->state(fn()=>['email_verified_at'=>null]);}
}
