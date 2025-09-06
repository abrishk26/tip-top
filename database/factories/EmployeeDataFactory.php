<?php

namespace Database\Factories;

use App\Models\EmployeeData;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EmployeeDataFactory extends Factory
{
    protected $model = EmployeeData::class;

    public function definition()
    {
        return [
            'id' => Str::ulid(),
            'employee_id' => Employee::factory(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password_hash' => bcrypt('password123'),
            'image_url' => $this->faker->imageUrl(),
        ];
    }
}
