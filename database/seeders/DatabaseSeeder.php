<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // Create test users with different roles
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        $instructorRole = \App\Models\Role::where('name', 'instructor')->first();
        $studentRole = \App\Models\Role::where('name', 'student')->first();

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role_id' => $adminRole?->id,
        ]);

        User::factory()->create([
            'name' => 'Instructor User',
            'email' => 'instructor@example.com',
            'role_id' => $instructorRole?->id,
        ]);

        User::factory()->create([
            'name' => 'Student User',
            'email' => 'student@example.com',
            'role_id' => $studentRole?->id,
        ]);
    }
}
