<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sap_log_perhitungan', function (Blueprint $table) {
            $table->id();
            $table->string('trigger_type', 40);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('period');
            $table->string('status', 20);
            $table->unsignedInteger('processed_count')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['trigger_type', 'status']);
            $table->index(['period', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sap_log_perhitungan');
    }
};
