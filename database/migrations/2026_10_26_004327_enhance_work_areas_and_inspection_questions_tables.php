<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_areas', function (Blueprint $table) {
            $table->string('code', 30)->nullable()->unique()->after('department_id');
            $table->text('description')->nullable()->after('name');
            $table->softDeletes();
            $table->index(['is_active', 'name']);
        });

        Schema::table('inspection_questions', function (Blueprint $table) {
            $table->softDeletes();
            $table->index(['work_area_id', 'is_active', 'sort_order'], 'questions_area_active_order_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspection_questions', function (Blueprint $table) {
            $table->dropIndex('questions_area_active_order_index');
            $table->dropSoftDeletes();
        });

        Schema::table('work_areas', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'name']);
            $table->dropUnique(['code']);
            $table->dropColumn(['code', 'description', 'deleted_at']);
        });
    }
};
