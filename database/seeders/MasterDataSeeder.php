<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class MasterDataSeeder extends Seeder {
 public function run(): void {
  DB::transaction(function(){
   foreach(['unsafe_condition'=>'Kondisi Tidak Aman','unsafe_act'=>'Tindakan Tidak Aman'] as $code=>$name){
    DB::table('kategori_temuan')->upsert([['code'=>$code,'name'=>$name,'created_at'=>now(),'updated_at'=>now()]],['code'],['name','updated_at']);
   }
   foreach(['permanent'=>'Karyawan Tetap','contract'=>'Kontrak'] as $code=>$name){
    DB::table('status_karyawan')->upsert([['code'=>$code,'name'=>$name,'created_at'=>now(),'updated_at'=>now()]],['code'],['name','updated_at']);
   }
  });
 }
}
