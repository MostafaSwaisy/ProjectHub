<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing role enum to include new roles
        // For SQLite, we need to recreate the column with the new values
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::table('project_members', function (Blueprint $table) {
                // SQLite doesn't support modifying column types directly
                // We'll add a new column, migrate data, then drop the old one
                $table->string('role_temp')->after('role')->nullable();
            });

            // Migrate data: editor -> member
            DB::table('project_members')
                ->where('role', 'editor')
                ->update(['role_temp' => 'member']);

            // Keep other roles as-is
            DB::table('project_members')
                ->whereIn('role', ['owner', 'viewer'])
                ->update(['role_temp' => DB::raw('role')]);

            // Drop old column and rename new one
            Schema::table('project_members', function (Blueprint $table) {
                $table->dropColumn('role');
            });

            Schema::table('project_members', function (Blueprint $table) {
                $table->renameColumn('role_temp', 'role');
            });
        } else {
            // For MySQL/PostgreSQL, we can modify the enum directly
            Schema::table('project_members', function (Blueprint $table) {
                $table->enum('role', ['owner', 'lead', 'member', 'viewer'])->change();
            });

            // Migrate existing editor roles to member
            DB::table('project_members')
                ->where('role', 'editor')
                ->update(['role' => 'member']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::table('project_members', function (Blueprint $table) {
                $table->string('role_temp')->after('role')->nullable();
            });

            // Migrate data back: member -> editor
            DB::table('project_members')
                ->where('role', 'member')
                ->where('role', '!=', 'lead') // keep lead as-is for users who have it
                ->update(['role_temp' => 'editor']);

            // Keep other roles as-is
            DB::table('project_members')
                ->whereIn('role', ['owner', 'viewer', 'lead'])
                ->where('role', '!=', 'member')
                ->update(['role_temp' => DB::raw('role')]);

            Schema::table('project_members', function (Blueprint $table) {
                $table->dropColumn('role');
            });

            Schema::table('project_members', function (Blueprint $table) {
                $table->renameColumn('role_temp', 'role');
            });
        } else {
            // Migrate member roles back to editor
            DB::table('project_members')
                ->where('role', 'member')
                ->update(['role' => 'editor']);

            // Revert enum
            Schema::table('project_members', function (Blueprint $table) {
                $table->enum('role', ['owner', 'editor', 'viewer'])->change();
            });
        }
    }
};
