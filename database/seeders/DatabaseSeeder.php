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
        // Seed roles first
        $this->call(RoleSeeder::class);

        // Seed complete dashboard test data (users, projects, tasks, etc.)
        $this->call(DashboardTestSeeder::class);

        $this->command->info('');
        $this->command->info('🎉 Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('📝 Login credentials:');
        $this->command->info('   Admin:    admin@example.com / password');
        $this->command->info('   Developer: john@example.com / password');
        $this->command->info('   Designer:  jane@example.com / password');
        $this->command->info('   Tester:    bob@example.com / password');
        $this->command->info('');
    }
}
