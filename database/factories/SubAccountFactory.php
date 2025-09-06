<?php

namespace Database\Factories;

use App\Models\SubAccount;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SubAccountFactory extends Factory
{
    protected $model = SubAccount::class;

    public function definition()
    {
        return [
            'id' => Str::ulid(),
            'sub_account' => $this->faker->unique()->numerify('SUB####'),
            'employee_id' => Employee::factory(),
        ];
    }
}
