<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('hazard_photos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('hazard_report_id')->constrained('hazard_reports')->restrictOnDelete();
            $t->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $t->string('disk',40)->default('local');
            $t->string('path',500);
            $t->string('original_name',255);
            $t->string('mime_type',100);
            $t->unsignedBigInteger('size_bytes');
            $t->timestamps();
        });
        Schema::create('resolution_photos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('hazard_resolution_id')->constrained('hazard_resolutions')->restrictOnDelete();
            $t->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $t->string('disk',40)->default('local');
            $t->string('path',500);
            $t->string('original_name',255);
            $t->string('mime_type',100);
            $t->unsignedBigInteger('size_bytes');
            $t->timestamps();
        });
        Schema::create('inspection_photos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('inspection_answer_id')->constrained('inspection_answers')->restrictOnDelete();
            $t->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $t->string('disk',40)->default('local');
            $t->string('path',500);
            $t->string('original_name',255);
            $t->string('mime_type',100);
            $t->unsignedBigInteger('size_bytes');
            $t->timestamps();
        });
        Schema::create('finding_photos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('inspection_finding_id')->constrained('inspection_findings')->restrictOnDelete();
            $t->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $t->string('disk',40)->default('local');
            $t->string('path',500);
            $t->string('original_name',255);
            $t->string('mime_type',100);
            $t->unsignedBigInteger('size_bytes');
            $t->timestamps();
        });
        Schema::create('safety_talk_attachments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('safety_talk_id')->constrained('safety_talks')->restrictOnDelete();
            $t->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $t->string('disk',40)->default('local');
            $t->string('path',500);
            $t->string('original_name',255);
            $t->string('mime_type',100);
            $t->unsignedBigInteger('size_bytes');
            $t->timestamps();
        });
        Schema::create('sap_point_rules', function (Blueprint $t) {
            $t->id();
            $t->string('activity',40);
            $t->unsignedInteger('points');
            $t->date('effective_from');
            $t->date('effective_until')->nullable();
            $t->unique(['activity','effective_from']);
            $t->timestamps();
        });
        Schema::create('sap_targets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $t->date('period');
            $t->string('activity',40);
            $t->unsignedInteger('target_count');
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->unique(['user_id','period','activity']);
            $t->timestamps();
        });
        Schema::create('sap_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('point_rule_id')->constrained('sap_point_rules')->restrictOnDelete();
            $t->string('activity',40);
            $t->unsignedBigInteger('source_id');
            $t->integer('points');
            $t->dateTime('earned_at');
            $t->date('period');
            $t->boolean('is_void')->default(false);
            $t->text('void_reason')->nullable();
            $t->unique(['activity','source_id','user_id']);
            $t->index(['user_id','period','is_void']);
            $t->timestamps();
        });
        Schema::create('sap_monthly_summaries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $t->date('period');
            $t->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $t->boolean('is_hse_snapshot');
            $t->unsignedInteger('hazard_count')->default(0);
            $t->unsignedInteger('inspection_count')->default(0);
            $t->unsignedInteger('resolution_count')->default(0);
            $t->unsignedInteger('safety_talk_count')->default(0);
            $t->integer('total_points')->default(0);
            $t->boolean('target_met')->nullable();
            $t->dateTime('calculated_at');
            $t->unique(['user_id','period']);
            $t->timestamps();
        });
        Schema::create('rewards', function (Blueprint $t) {
            $t->id();
            $t->string('name',255);
            $t->unsignedInteger('required_points');
            $t->text('description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('reward_redemptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('reward_id')->constrained('rewards')->restrictOnDelete();
            $t->unsignedInteger('points_spent');
            $t->enum('status',["requested", "approved", "rejected", "delivered"])->default('requested');
            $t->foreignId('processed_by')->nullable()->constrained('users')->restrictOnDelete();
            $t->dateTime('processed_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('actor_id')->nullable()->constrained('users')->restrictOnDelete();
            $t->string('action',60);
            $t->string('entity_type',100);
            $t->unsignedBigInteger('entity_id');
            $t->json('before_values')->nullable();
            $t->json('after_values')->nullable();
            $t->string('ip_address',45)->nullable();
            $t->text('user_agent')->nullable();
            $t->index(['entity_type','entity_id','created_at']);
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('reward_redemptions');
        Schema::dropIfExists('rewards');
        Schema::dropIfExists('sap_monthly_summaries');
        Schema::dropIfExists('sap_entries');
        Schema::dropIfExists('sap_targets');
        Schema::dropIfExists('sap_point_rules');
        Schema::dropIfExists('safety_talk_attachments');
        Schema::dropIfExists('finding_photos');
        Schema::dropIfExists('inspection_photos');
        Schema::dropIfExists('resolution_photos');
        Schema::dropIfExists('hazard_photos');
    }
};
