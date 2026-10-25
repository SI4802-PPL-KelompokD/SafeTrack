<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class DemoSeeder extends Seeder {
 public function run(): void {
  if(!app()->environment('local','testing')){throw new \RuntimeException('Demo hanya local/testing.');}
  DB::transaction(function(){
   $this->call(MasterDataSeeder::class);
   $insert=function($table,$key,$values){
    $id=DB::table($table)->where($key)->value('id');
    return $id ?: DB::table($table)->insertGetId($key+$values+['created_at'=>now(),'updated_at'=>now()]);
   };
   $co=$insert('companies',['code'=>'DEMO'],['name'=>'PT Demo SafeTrack']);
   $hse=$insert('departments',['company_id'=>$co,'code'=>'HSE'],['name'=>'HSE','is_hse'=>true]);
   $ops=$insert('departments',['company_id'=>$co,'code'=>'OPS'],['name'=>'Operasional','is_hse'=>false]);
   $employment=DB::table('employment_statuses')->where('code','permanent')->value('id');
   foreach([['DEMO001','Admin Utama','super_admin',$hse,'admin'],['DEMO002','Petugas HSE','admin',$hse,'hse'],['DEMO003','Karyawan Demo','employee',$ops,'karyawan']] as [$nik,$name,$role,$dept,$username]){
    $reg=$insert('employee_registry',['nik'=>$nik],['full_name'=>$name,'department_id'=>$dept,'employment_status_id'=>$employment]);
    $insert('users',['employee_registry_id'=>$reg],['department_id'=>$dept,'name'=>$name,'username'=>$username,'email'=>$username.'@safetrack.test','password'=>Hash::make('DemoOnly!2026'),'role'=>$role,'is_active'=>true]);
   }
   $area=$insert('work_areas',['department_id'=>$ops,'name'=>'Area Produksi Demo'],[]);
   $insert('shifts',['code'=>'PAGI'],['name'=>'Pagi','starts_at'=>'07:00','ends_at'=>'15:00']);
   foreach(['Apakah jalur evakuasi bebas hambatan?','Apakah APD digunakan dengan benar?'] as $i=>$q){
    $insert('inspection_questions',['work_area_id'=>$area,'question'=>$q],['sort_order'=>$i+1]);
   }
   $insert('sap_point_rules',['activity'=>'hazard_verified','effective_from'=>'2026-10-01'],['points'=>1]);
  });
 }
}

