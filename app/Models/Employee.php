<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

use App\Exceptions\EmployeeNotFoundException;

class Employee extends Model
{
    use HasUlids;

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
        return $this->hasOne(EmployeeData::class, 'employee_id', 'id');
    }

    public static function login(array $data)
    {
        $user = Employee::data()->where('email', $data['email'])->first();

        if (!$user) {
            throw new \App\Exceptions\InvalidCredentialsException("invalid credential");
        }

        if (!Hash::check($data['password'], $user->password_hash)) {
            throw new \App\Exceptions\InvalidCredentialsException("invalid credential");
        }

        $token = $user->createToken('api-token')->plainTextToken;

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
