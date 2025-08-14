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

    public function completeRegistration(array $personalData): void
    {
        if (!$this->exists) {
            throw new EmployeeNotFoundException("Employee ID does not exist.");
        }

        DB::beginTransaction();

        try {
            // Create or update personal data
            if ($this->data) {
                $this->data->update($personalData);
            } else {
                $this->data()->create($personalData);
            }

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            throw new \Exception("Failed to complete registration: " . $e->getMessage(), 0, $e);
        }
    }
}
