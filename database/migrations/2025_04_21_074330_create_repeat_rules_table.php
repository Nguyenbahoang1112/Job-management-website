<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\RepeatRule;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('repeat_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->unsignedSmallInteger('repeat_type');
            $table->unsignedSmallInteger('repeat_interval')->nullable();
            $table->date('repeat_due_date')->nullable();
            $table->unsignedSmallInteger('status_repeat_task')->default((RepeatRule::STATUS_IN_PROGRESS));
            $table->unsignedSmallInteger('priority_repeat_task')->default(RepeatRule::PRIORITY_LOW);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repeat_rules');
    }
};
