<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceProvider;
use App\Models\Employee;
use App\Models\EmployeeData;
use App\Models\Category;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create categories first (required for service provider)
        $category = Category::create([
            'id' => Str::ulid(),
            'name' => 'Technology',
        ]);

        // Create more categories
        Category::create([
            'id' => Str::ulid(),
            'name' => 'Healthcare',
        ]);

        Category::create([
            'id' => Str::ulid(),
            'name' => 'Education',
        ]);

        // Always seed an admin user (will prompt for credentials if env not set)
        $this->call(AdminSeeder::class);

        $this->command->info('Sample data created successfully!');
        $this->command->info('Category ID: ' . $category->id);
    }
}

