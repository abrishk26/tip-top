<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\ServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition()
    {
        return [
            'id' => Str::ulid(),
            'service_provider_id' => ServiceProvider::factory(),
            'tip_code' => $this->faker->unique()->numerify('TIP####'),
            'is_active' => true,
            'is_verified' => true,
        ];
    }
}
