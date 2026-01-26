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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('type'); // task_created, task_updated, task_moved, comment_added, etc.
            $table->string('subject_type')->nullable(); // Task, Comment, Project, etc. (for polymorphic relationships)
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('data')->nullable(); // Store additional context
            $table->timestamps();

            $table->index('user_id');
            $table->index('project_id');
            $table->index('type');
            $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
