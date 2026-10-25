<?php
namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class RegistrationService {
 // Caller validates username/email/password; never accepts a public role or department.
 public function register(array $data): User {
  return DB::transaction(function()use($data){
   $employee=DB::table('employee_registry')->where('nik',$data['nik'])->lockForUpdate()->first();
   if(!$employee || !$employee->is_active || DB::table('users')->where('employee_registry_id',$employee->id)->exists()){
    throw ValidationException::withMessages(['nik'=>'NIK tidak tersedia untuk registrasi.']);
   }
   $user=new User();
   $user->fill(collect($data)->only(['username','email','password'])->all());
   $user->name=$employee->full_name;
   $user->employee_registry_id=$employee->id;
   $user->department_id=$employee->department_id;
   $user->role='employee';$user->is_active=true;$user->save();
   return $user;
  },3);
 }
}
