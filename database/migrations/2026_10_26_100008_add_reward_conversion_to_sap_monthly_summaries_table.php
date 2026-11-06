<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sap_monthly_summaries', function (Blueprint $table) {
            $table->foreignId('eligible_reward_id')->nullable()->after('target_met')->constrained('rewards')->nullOnDelete();
            $table->boolean('reward_eligible')->default(false)->after('eligible_reward_id');
        });
    }

    public function down(): void
    {
        Schema::table('sap_monthly_summaries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('eligible_reward_id');
            $table->dropColumn('reward_eligible');
        });
    }
};
