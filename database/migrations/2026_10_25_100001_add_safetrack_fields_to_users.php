<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  // This addon is for a new project. Do not fabricate NIK for existing users.
  if(!DB::pretending() && DB::table('users')->exists()){
   throw new RuntimeException('Tabel users sudah berisi data. Lakukan pemetaan NIK/backfill sebelum migration ini; jangan hapus data pengguna.');
  }
  Schema::table('users',function(Blueprint $t){
   $t->foreignId('employee_registry_id')->unique()->constrained('employee_registry')->restrictOnDelete();
   $t->foreignId('department_id')->constrained('departments')->restrictOnDelete();
   $t->string('username',60)->unique();
   $t->enum('role',['super_admin','admin','employee'])->default('employee');
   $t->boolean('is_active')->default(true);
   $t->string('profile_photo_path',500)->nullable();
   $t->foreignId('deleted_by')->nullable()->constrained('users')->restrictOnDelete();
   $t->text('deletion_reason')->nullable();
   $t->softDeletes();
  });
 }
 public function down(): void {
  Schema::table('users',function(Blueprint $t){
   $t->dropConstrainedForeignId('deleted_by');
   $t->dropConstrainedForeignId('employee_registry_id');
   $t->dropConstrainedForeignId('department_id');
   $t->dropColumn(['username','role','is_active','profile_photo_path','deletion_reason','deleted_at']);
  });
 }
};
