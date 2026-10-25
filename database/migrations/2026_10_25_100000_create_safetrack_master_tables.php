<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('companies', function (Blueprint $t) {
            $t->id();
            $t->string('code',30)->unique();
            $t->string('name',255);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('departments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $t->string('code',30);
            $t->string('name',255);
            $t->boolean('is_hse')->default(false);
            $t->boolean('is_active')->default(true);
            $t->unique(['company_id','code']);
            $t->timestamps();
        });
        Schema::create('employment_statuses', function (Blueprint $t) {
            $t->id();
            $t->string('code',30)->unique();
            $t->string('name',255);
            $t->timestamps();
        });
        Schema::create('employee_registry', function (Blueprint $t) {
            $t->id();
            $t->string('nik',40)->unique();
            $t->string('full_name',255);
            $t->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $t->foreignId('employment_status_id')->constrained('employment_statuses')->restrictOnDelete();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('hazard_categories', function (Blueprint $t) {
            $t->id();
            $t->string('code',30)->unique();
            $t->string('name',255);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('shifts', function (Blueprint $t) {
            $t->id();
            $t->string('code',30)->unique();
            $t->string('name',255);
            $t->time('starts_at');
            $t->time('ends_at');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('work_areas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $t->string('name',255);
            $t->boolean('is_active')->default(true);
            $t->unique(['department_id','name']);
            $t->timestamps();
        });
        Schema::create('inspection_questions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('work_area_id')->constrained('work_areas')->restrictOnDelete();
            $t->text('question');
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('inspection_questions');
        Schema::dropIfExists('work_areas');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('hazard_categories');
        Schema::dropIfExists('employee_registry');
        Schema::dropIfExists('employment_statuses');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('companies');
    }
};
