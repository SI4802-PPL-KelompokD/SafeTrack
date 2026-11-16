<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = $this->summaryTableName();

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! Schema::hasColumn($tableName, 'eligible_reward_id')) {
                $table->foreignId('eligible_reward_id')->nullable()->after('target_met')->constrained('rewards')->nullOnDelete();
            }

            if (! Schema::hasColumn($tableName, 'reward_eligible')) {
                $table->boolean('reward_eligible')->default(false)->after('eligible_reward_id');
            }
        });
    }

    public function down(): void
    {
        $tableName = $this->summaryTableName();

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (Schema::hasColumn($tableName, 'eligible_reward_id')) {
                $table->dropConstrainedForeignId('eligible_reward_id');
            }

            if (Schema::hasColumn($tableName, 'reward_eligible')) {
                $table->dropColumn('reward_eligible');
            }
        });
    }

    /**
     * Tabel rekap SAP sudah di-rename menjadi history_sap oleh migration penyesuaian ERD
     * (2026_10_27_130000). Database yang sudah menjalankan rename memakai nama baru,
     * sedangkan database baru yang menjalankan migration berurutan masih memakai nama lama.
     */
    private function summaryTableName(): string
    {
        return Schema::hasTable('history_sap') ? 'history_sap' : 'sap_monthly_summaries';
    }
};
