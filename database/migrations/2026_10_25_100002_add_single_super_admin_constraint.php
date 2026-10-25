<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up(): void {
  DB::statement("ALTER TABLE users ADD super_admin_slot TINYINT GENERATED ALWAYS AS (CASE WHEN role = 'super_admin' THEN 1 ELSE NULL END) STORED, ADD UNIQUE KEY users_one_super_admin (super_admin_slot)");
 }
 public function down(): void { DB::statement('ALTER TABLE users DROP INDEX users_one_super_admin, DROP COLUMN super_admin_slot'); }
};
