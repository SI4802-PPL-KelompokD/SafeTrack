<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('safety_talks', function (Blueprint $t) {
            $t->id();
            $t->uuid('client_request_id')->unique();
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->foreignId('department_id')->nullable()->constrained('departments')->restrictOnDelete();
            $t->string('topic',200);
            $t->longText('content');
            $t->dateTime('held_at')->nullable();
            $t->enum('status',["draft", "published", "archived"])->default('draft');
            $t->dateTime('published_at')->nullable();
            $t->foreignId('verified_by')->nullable()->constrained('users')->restrictOnDelete();
            $t->dateTime('verified_at')->nullable();
            $t->index(['status','published_at']);
            $t->timestamps();
        });
        Schema::create('safety_talk_reads', function (Blueprint $t) {
            $t->id();
            $t->foreignId('safety_talk_id')->constrained('safety_talks')->restrictOnDelete();
            $t->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $t->dateTime('read_at');
            $t->unique(['safety_talk_id','user_id']);
            $t->timestamps();
        });
        Schema::create('safety_talk_attendees', function (Blueprint $t) {
            $t->id();
            $t->foreignId('safety_talk_id')->constrained('safety_talks')->restrictOnDelete();
            $t->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $t->unique(['safety_talk_id','user_id']);
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('safety_talk_attendees');
        Schema::dropIfExists('safety_talk_reads');
        Schema::dropIfExists('safety_talks');
    }
};
