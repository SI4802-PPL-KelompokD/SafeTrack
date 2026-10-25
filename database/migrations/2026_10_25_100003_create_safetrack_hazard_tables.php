<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('hazard_reports', function (Blueprint $t) {
            $t->id();
            $t->uuid('client_request_id')->unique();
            $t->foreignId('reporter_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('reporter_department_id')->constrained('departments')->restrictOnDelete();
            $t->foreignId('repair_department_id')->nullable()->constrained('departments')->restrictOnDelete();
            $t->foreignId('category_id')->constrained('hazard_categories')->restrictOnDelete();
            $t->foreignId('work_area_id')->nullable()->constrained('work_areas')->restrictOnDelete();
            $t->string('title',200);
            $t->string('location',255);
            $t->text('description');
            $t->text('immediate_action')->nullable();
            $t->enum('risk_level',["low", "medium", "high"]);
            $t->enum('status',["pending_verification", "rejected", "open", "in_progress", "closed"])->default('pending_verification');
            $t->foreignId('assigned_admin_id')->nullable()->constrained('users')->restrictOnDelete();
            $t->foreignId('repair_pic_id')->nullable()->constrained('users')->restrictOnDelete();
            $t->foreignId('verified_by')->nullable()->constrained('users')->restrictOnDelete();
            $t->dateTime('verified_at')->nullable();
            $t->text('verification_notes')->nullable();
            $t->dateTime('reported_at');
            $t->dateTime('closed_at')->nullable();
            $t->foreignId('deleted_by')->nullable()->constrained('users')->restrictOnDelete();
            $t->text('deletion_reason')->nullable();
            $t->softDeletes();
            $t->index(['status','reported_at']);
            $t->index(['reporter_department_id','reported_at']);
            $t->index(['reporter_id','reported_at']);
            $t->timestamps();
        });
        Schema::create('hazard_resolutions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('hazard_report_id')->constrained('hazard_reports')->restrictOnDelete();
            $t->foreignId('submitted_by')->constrained('users')->restrictOnDelete();
            $t->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $t->uuid('client_request_id')->unique();
            $t->text('description');
            $t->enum('status',["pending", "accepted", "rejected"])->default('pending');
            $t->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $t->dateTime('reviewed_at')->nullable();
            $t->text('review_notes')->nullable();
            $t->dateTime('submitted_at');
            $t->timestamps();
        });
        Schema::create('hazard_status_histories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('hazard_report_id')->constrained('hazard_reports')->restrictOnDelete();
            $t->string('from_status',40)->nullable();
            $t->string('to_status',40);
            $t->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $t->text('reason')->nullable();
            $t->index(['hazard_report_id','created_at']);
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('hazard_status_histories');
        Schema::dropIfExists('hazard_resolutions');
        Schema::dropIfExists('hazard_reports');
    }
};
