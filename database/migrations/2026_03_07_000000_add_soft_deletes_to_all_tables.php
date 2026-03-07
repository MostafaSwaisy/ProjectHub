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
        $tables = ['users', 'projects', 'boards', 'columns', 'tasks', 'subtasks', 'comments', 'labels', 'activities', 'project_members'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->softDeletes();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
                $table->index('deleted_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['users', 'projects', 'boards', 'columns', 'tasks', 'subtasks', 'comments', 'labels', 'activities', 'project_members'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeignKeyIfExists(['deleted_by']);
                $table->dropColumn(['deleted_at', 'deleted_by']);
            });
        }
    }
};
