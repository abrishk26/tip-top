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
        $name = $this->command->ask('Admin name');
        $email = $this->command->ask('Admin email');
        $password = $this->command->secret('Admin password');

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
