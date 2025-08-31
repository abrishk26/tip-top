<?php

namespace Database\Factories;

use App\Models\ServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceProviderFactory extends Factory
{
    protected $model = ServiceProvider::class;

    public function definition()
    {
        return [
            'id' => Str::ulid(),
            'name' => $this->faker->company(),
            'category_id' => \App\Models\Category::factory(),
            'description' => $this->faker->sentence(),
            'tax_id' => $this->faker->numerify('TAX####'),
            'email' => $this->faker->unique()->safeEmail(),
            'password_hash' => bcrypt('password123'),
            'contact_phone' => $this->faker->unique()->numerify('+1##########'),
            'license' => $this->faker->numerify('LIC####'),
            'registration_status' => 'pending',
            'is_verified' => true,
        ];
    }
}
