<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the tasks table
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('due_date');
            $table->time('time');
            $table->unsignedSmallInteger('priority')->default(0); //0=low, 1=priority, 2=admin
            $table->unsignedTinyInteger('status')->default(1); //0 = deleting, 1 = pending, 2 = completed
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('group_task_id')->constrained('group_tasks')->onDelete('cascade')->nullable();
            $table->timestamps();
            // Add indexes for better performance
            $table->index(['user_id', 'group_task_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
