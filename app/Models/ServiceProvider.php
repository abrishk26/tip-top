<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

use App\Exceptions\DuplicateEmailException;
use App\Exceptions\DuplicateEmployeeException;
use Laravel\Sanctum\HasApiTokens;

class ServiceProvider extends Model
{
    use HasUlids, HasApiTokens;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected $hidden = ['password_hash'];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public static function register($data)
    {
        try {
            return ServiceProvider::create($data);
        } catch (QueryException $e) {
            if (($e->errorInfo[1] ?? null) === 1062) {
                throw new DuplicateEmailException("Email already exists", 0, $e);
            }

            throw $e;
        }
    }

    public static function login(array $data)
    {
        $user = self::where('email', $data['email'])->first();

        if (!$user) {
            throw new \App\Exceptions\UserNotFoundException("ServiceProvider with this email not found");
        }

        if (!Hash::check($data['password'], $user->password_hash)) {
            throw new \App\Exceptions\InvalidCredentialsException("Invalid password");
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function activateEmployee(Employee $employee): void
    {
        $employee->update(['is_active' => true]);
    }

    public function deactivateEmployee(Employee $employee): void
    {
        $employee->update(['is_active' => false]);
    }

    public function setEmployeesStatus(array $employees, bool $active): void
    {
        foreach ($employees as $employee) {
            $employee->update(['is_active' => $active]);
        }
    }

    public function getEmployees()
    {
        return $this->employees()
            ->with('data')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'unique_id' => $employee->uin,
                    'is_active' => $employee->is_active,
                    'first_name' => $employee->data->first_name ?? null,
                    'last_name' => $employee->data->last_name ?? null,
                    'email' => $employee->data->email ?? null,
                    'image_url' => $employee->data->image_url,
                ];
            });
    }

    public function employeeSummary(): array
    {
        return [
            'total' => $this->employees()->count(),
            'active' => $this->employees()->where('is_active', true)->count(),
            'inactive' => $this->employees()->where('is_active', false)->count(),
        ];
    }

    public function registerEmployees($count)
    {
        $results = [];

        DB::beginTransaction();

        try {
            for ($i = 0; $i < $count; $i++) {
                $results[] = $this->employees()->create([
                    'id' => Str::ulid(),
                    'unique_id' => Str::ulid()->toString(),
                ]);
            }

            DB::commit();

            return $results;
        } catch (QueryException $e) {
            DB::rollBack();

            if (($e->errorInfo[1] ?? null) === 1062) {
                throw new DuplicateEmployeeException("Employee ID or Unique ID alread exists", 0, $e);
            }

            throw $e;
        }
    }
}
