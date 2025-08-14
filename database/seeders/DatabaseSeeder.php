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

        // Create a service provider
        $serviceProvider = ServiceProvider::create([
            'id' => Str::ulid(),
            'name' => 'Test Company',
            'category_id' => $category->id,
            'email' => 'test@company.com',
            'password_hash' => bcrypt('password123'),
            'contact_phone' => '+1234567890',
            'image_url' => 'https://example.com/company.jpg',
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

        // Create employees
        $employee1 = Employee::create([
            'id' => Str::ulid(),
            'unique_id' => Str::ulid(),
            'is_active' => true,
            'service_provider_id' => $serviceProvider->id,
        ]);

        $employee2 = Employee::create([
            'id' => Str::ulid(),
            'unique_id' => Str::ulid(),
            'is_active' => false,
            'service_provider_id' => $serviceProvider->id,
        ]);

        // Create employee data
        EmployeeData::create([
            'id' => Str::ulid(),
            'employee_id' => $employee1->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@company.com',
            'password_hash' => bcrypt('password123'),
            'image_url' => 'https://example.com/john.jpg',
            'sub_account_id' => 'acc_001',
        ]);

        EmployeeData::create([
            'id' => Str::ulid(),
            'employee_id' => $employee2->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@company.com',
            'password_hash' => bcrypt('password123'),
            'image_url' => 'https://example.com/jane.jpg',
            'sub_account_id' => 'acc_002',
        ]);

        $this->command->info('Sample data created successfully!');
        $this->command->info('Category ID: ' . $category->id);
        $this->command->info('Service Provider ID: ' . $serviceProvider->id);
        $this->command->info('Employee 1 ID: ' . $employee1->id);
        $this->command->info('Employee 2 ID: ' . $employee2->id);
    }
} 