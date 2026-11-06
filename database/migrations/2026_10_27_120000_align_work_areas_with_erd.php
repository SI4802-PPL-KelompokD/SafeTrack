<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_areas', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
        });

        Schema::table('work_areas', function (Blueprint $table) {
            $table->dropUnique('work_areas_department_id_name_unique');
            $table->dropUnique('work_areas_code_unique');
            $table->dropColumn(['department_id', 'code']);
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::table('work_areas', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->unsignedBigInteger('department_id')->nullable()->after('id');
            $table->string('code', 30)->nullable()->after('department_id');
        });

        Schema::table('work_areas', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('departments')->restrictOnDelete();
            $table->unique('code');
            $table->unique(['department_id', 'name']);
        });
    }
};
