<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class CreateInitialAdmin extends Command {
 protected $signature='safetrack:admin';
 protected $description='Buat satu Admin Utama awal tanpa password di source code';
 public function handle(): int {
  if(DB::table('users')->where('role','super_admin')->exists()){$this->error('Admin Utama sudah ada.');return 1;}
  $data=['company_code'=>$this->ask('Kode perusahaan'),'company_name'=>$this->ask('Nama perusahaan'),'nik'=>$this->ask('NIK Admin Utama'),'name'=>$this->ask('Nama lengkap'),'username'=>$this->ask('Username'),'email'=>$this->ask('Email'),'password'=>$this->secret('Password (minimal 12 karakter)')];
  $validator=Validator::make($data,['company_code'=>'required|string|max:30','company_name'=>'required|string|max:255','nik'=>'required|string|max:40|unique:nik,nik','name'=>'required|string|max:255','username'=>'required|alpha_dash|max:60|unique:users,username','email'=>'required|email|unique:users,email','password'=>'required|string|min:12']);
  if($validator->fails()){foreach($validator->errors()->all() as $e){$this->error($e);}return 1;}
  DB::transaction(function()use($data){
   $now=now();
   $company=DB::table('company')->where('code',$data['company_code'])->value('id') ?: DB::table('company')->insertGetId(['code'=>$data['company_code'],'name'=>$data['company_name'],'created_at'=>$now,'updated_at'=>$now]);
   $dept=DB::table('departments')->where('company_id',$company)->where('code','HSE')->value('id') ?: DB::table('departments')->insertGetId(['company_id'=>$company,'code'=>'HSE','name'=>'HSE','is_hse'=>true,'created_at'=>$now,'updated_at'=>$now]);
   $status=DB::table('status_karyawan')->where('code','permanent')->value('id');
   if(!$status){throw new \RuntimeException('Jalankan php artisan db:seed dahulu.');}
   $reg=DB::table('nik')->insertGetId(['nik'=>$data['nik'],'full_name'=>$data['name'],'department_id'=>$dept,'employment_status_id'=>$status,'created_at'=>$now,'updated_at'=>$now]);
   DB::table('users')->insert(['employee_registry_id'=>$reg,'department_id'=>$dept,'name'=>$data['name'],'username'=>$data['username'],'email'=>$data['email'],'password'=>Hash::make($data['password']),'role'=>'super_admin','is_active'=>true,'created_at'=>$now,'updated_at'=>$now]);
  });
  $this->info('Admin Utama dibuat. Endpoint login belum termasuk starter database.');return 0;
 }
}
