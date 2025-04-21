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
        Schema::create('task_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');

            $table->string('title');
            $table->text('description');
            $table->dateTime('due_date');
            $table->time('time');
            $table->unsignedSmallInteger('priority')->default(0); //0=low, 1=star
            $table->unsignedSmallInteger('status')->default(1); //0 = completed, 1 = pending, 2 = deleting
            $table->unsignedBigInteger('parent_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_details');
    }
};
