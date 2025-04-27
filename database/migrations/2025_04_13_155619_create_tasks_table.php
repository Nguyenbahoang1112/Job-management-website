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
            $table->unsignedSmallInteger('is_admin_created')->default(0); //0 = user created, 1 = admin created
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('task_group_id')->nullable()->constrained('task_groups')->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('cascade');
            $table->timestamps();
            // Add indexes for better performance
            $table->index(['user_id', 'task_group_id']);
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
