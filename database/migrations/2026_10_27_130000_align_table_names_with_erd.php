<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, string> */
    private array $tableRenames = [
        'companies' => 'company',
        'employment_statuses' => 'status_karyawan',
        'employee_registry' => 'nik',
        'hazard_categories' => 'kategori_temuan',
        'shifts' => 'shift',
        'work_areas' => 'area_kerja',
        'hazard_resolutions' => 'penyelesaian',
        'hazard_status_histories' => 'hazard_history_status',
        'daily_inspections' => 'daily_inspection',
        'inspection_answers' => 'inspection_answer',
        'inspection_findings' => 'detail_daily_inspection',
        'safety_talks' => 'safety_talk',
        'sap_entries' => 'pencapaian',
        'sap_monthly_summaries' => 'history_sap',
        'sap_targets' => 'target_safety_talks',
        'sessions' => 'user_sessions',
        'password_reset_tokens' => 'password_reset_token',
        'failed_jobs' => 'failed_background_tasks',
    ];

    public function up(): void
    {
        foreach ($this->tableRenames as $currentName => $erdName) {
            Schema::rename($currentName, $erdName);
        }

        Schema::create('status_company', function (Blueprint $table) {
            $table->id();
            $table->string('nama_status')->unique();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nama_role')->unique();
            $table->timestamps();
        });

        Schema::create('status', function (Blueprint $table) {
            $table->id();
            $table->string('nama_status')->unique();
            $table->timestamps();
        });

        Schema::create('version_app', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique();
            $table->timestamps();
        });

        Schema::create('outbox_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->json('payload');
            $table->string('status', 40)->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('inspection', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_inspection_id')->constrained('daily_inspection')->restrictOnDelete();
            $table->foreignId('inspection_question_id')->constrained('inspection_questions')->restrictOnDelete();
            $table->foreignId('inspection_answer_id')->nullable()->constrained('inspection_answer')->nullOnDelete();
            $table->boolean('answer');
            $table->text('catatan')->nullable();
            $table->string('gambar', 500)->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('department_id')->constrained('roles')->restrictOnDelete();
            $table->foreignId('status_karyawan_id')->nullable()->after('role_id')->constrained('status_karyawan')->restrictOnDelete();
        });

        DB::table('roles')->insert([
            ['nama_role' => 'Admin Utama', 'created_at' => now(), 'updated_at' => now()],
            ['nama_role' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
            ['nama_role' => 'Karyawan', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('status_karyawan_id');
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('inspection');
        Schema::dropIfExists('outbox_events');
        Schema::dropIfExists('version_app');
        Schema::dropIfExists('status');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('status_company');

        foreach (array_reverse($this->tableRenames, true) as $currentName => $erdName) {
            Schema::rename($erdName, $currentName);
        }
    }
};
