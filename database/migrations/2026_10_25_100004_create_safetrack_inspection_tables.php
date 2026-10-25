<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('daily_inspections', function (Blueprint $t) {
            $t->id();
            $t->uuid('client_request_id')->unique();
            $t->foreignId('inspector_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $t->foreignId('work_area_id')->constrained('work_areas')->restrictOnDelete();
            $t->foreignId('shift_id')->constrained('shifts')->restrictOnDelete();
            $t->date('inspection_date');
            $t->enum('status',["submitted", "reviewed", "closed"])->default('submitted');
            $t->text('notes')->nullable();
            $t->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $t->dateTime('reviewed_at')->nullable();
            $t->text('review_notes')->nullable();
            $t->index(['inspector_id','inspection_date']);
            $t->index(['work_area_id','inspection_date','shift_id']);
            $t->timestamps();
        });
        Schema::create('inspection_answers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('daily_inspection_id')->constrained('daily_inspections')->restrictOnDelete();
            $t->foreignId('inspection_question_id')->constrained('inspection_questions')->restrictOnDelete();
            $t->text('question_snapshot');
            $t->enum('answer',["yes", "no", "na"]);
            $t->text('notes')->nullable();
            $t->unique(['daily_inspection_id','inspection_question_id'],'inspection_answer_question_unique');
            $t->timestamps();
        });
        Schema::create('inspection_findings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('inspection_answer_id')->constrained('inspection_answers')->restrictOnDelete();
            $t->foreignId('pic_id')->nullable()->constrained('users')->restrictOnDelete();
            $t->text('description');
            $t->enum('status',["open", "in_progress", "closed"])->default('open');
            $t->date('due_date')->nullable();
            $t->text('corrective_action')->nullable();
            $t->foreignId('closed_by')->nullable()->constrained('users')->restrictOnDelete();
            $t->dateTime('closed_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('inspection_findings');
        Schema::dropIfExists('inspection_answers');
        Schema::dropIfExists('daily_inspections');
    }
};
