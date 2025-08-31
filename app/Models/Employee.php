<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Exceptions\EmployeeNotFoundException;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Authenticatable
{
    use HasUlids, HasApiTokens, HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    // Relationship to ServiceProvider
    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    // Relationship to EmployeeData
    public function data()
    {
        return $this->hasOne(EmployeeData::class, 'employee_id', 'id');
    }

    // Relationship to SubAccount
    public function subAccount()
    {
        return $this->hasOne(SubAccount::class, 'employee_id', 'id');
    }

    public function login(array $data)
    {
        $employee_data = EmployeeData::where('email', $data['email'])->first();

        if (!$employee_data) {
            Log::error("user does not exist");
            throw new \App\Exceptions\InvalidCredentialsException("invalid credential");
        }


        if (!Hash::check($data['password'], $employee_data->password_hash)) {
            Log::error("invalid password");
            throw new \App\Exceptions\InvalidCredentialsException("invalid credential");
        }
        $employee = $employee_data->employee;

        $token = $employee->createToken('api-token')->plainTextToken;

        return $token;
    }
    public function updateProfile(array $attributes)
    {
        if ($this->data) {
            $this->data->update($attributes);
            return $this->data;
        }

        return $this->data()->create($attributes);
    }

    public function changePassword(string $password)
    {
        $this->data()->update([
            'password_hash' => Hash::make($password)
        ]);
    }

    public function getProfile()
    {
        return $this->data;
    }

    public function create(array $personalData): void
    {
        DB::transaction(function () use ($personalData) {
            if ($this->data) {
                $this->data->update($personalData);
            } else {
                $this->data()->create($personalData);
            }
        });
    }
}
