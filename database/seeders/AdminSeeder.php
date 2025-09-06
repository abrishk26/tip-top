<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Support non-interactive seeding via environment variables
        $name = env('ADMIN_NAME');
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        // Fallback to interactive prompts if any value is missing
        if (!$name || !$email || !$password) {
            $this->command->info('Create an admin user manually: please provide Name, Email, and Password.');
        }
        if (!$name) {
            $name = $this->command->ask('Admin name');
        }
        if (!$email) {
            $email = $this->command->ask('Admin email');
        }
        if (!$password) {
            $password = $this->command->secret('Admin password');
        }

        // Delete existing admin by email if exists
        Admin::where('email', $email)->delete();

        Admin::create([
            'name' => $name,
            'email' => $email,
            'password_hash' => Hash::make($password),
            'is_active' => true,
        ]);

        $this->command->info("Admin '{$email}' created/updated successfully.");
    }
}
