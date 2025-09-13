<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

use Laravel\Sanctum\HasApiTokens;

use App\Mail\VerificationEmail;
use App\Exceptions\DuplicateEmailException;
use App\Exceptions\DuplicateEmployeeException;
use App\Exceptions\EmployeeNotFoundException;
use App\Exceptions\UnverifiedUserException;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceProvider extends Model
{
    use HasUlids, HasApiTokens, HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    // relationship methods
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function address()
    {
        return $this->hasOne(Address::class, 'provider_id', 'id');
    }


    // reigister service provider with the given data from the controller
    public static function register($data)
    {
        // check if provider with the given email already exists
        $user = ServiceProvider::where('email', $data['email'])->first();
        if ($user) {
            throw new DuplicateEmailException;
        }

        // restructure the data for db insertion
        $data['password_hash'] = bcrypt($data['password']);
        unset($data['password']);

        $street = $data['address']['street_address'];
        $city = $data['address']['city'];
        $region = $data['address']['region'];

        unset($data['address']);

        $provider = ServiceProvider::create($data);
        $provider->address()->create([
            'street_address' => $street,
            'city' => $city,
            'region' => $region,
        ]);

        // generate verification token
        $token = Str::random(64);
        VerificationToken::create([
            'token' => $token,
            'tokenable_type' => 'provider',
            'tokenable_id' => $provider->id,
            'expires_at' => now()->addHours(24),
        ]);

        // send email for the provider in the background
        $verificationLink = config('app.frontend_url', 'http://localhost:8080') . '/api/service-provider/verify-email/?token=' . $token;

        Mail::to($data['email'])->queue(new VerificationEmail($verificationLink));

        // Log the verification link instead
        \Log::info('Verification link generated for ' . $data['email'] . ': ' . $verificationLink);

        return $provider;
    }

    // generate session token during login
    public static function login(array $data)
    {
        $user = ServiceProvider::where('email', $data['email'])->first();

        if (!$user) {
            throw new \App\Exceptions\InvalidCredentialsException("invalid credential");
        }

        if (!Hash::check($data['password'], $user->password_hash)) {
            throw new \App\Exceptions\InvalidCredentialsException("invalid credential");
        }

        if (!$user->is_verified) {
            throw new UnverifiedUserException("Email not verified", false);
        }

        if ($user->registration_status != 'accepted') {
            throw new UnverifiedUserException("license not verified", true, $user->registration_status);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return $token;
    }

    // get employees summary
    public function employeeSummary(): array
    {
        return [
            'total' => $this->employees()->count(),
            'active' => $this->employees()->where('is_active', true)->count(),
            'inactive' => $this->employees()->where('is_active', false)->count(),
        ];
    }

    // get service provider employee's data
    public function getEmployees()
    {
        return $this->employees()
            ->with('data')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'is_active' => $employee->is_active,
                    'first_name' => $employee->data?->first_name,
                    'last_name' => $employee->data?->last_name,
                    'email' => $employee->data?->email,
                    'image_url' => $employee->data?->image_url,
                ];
            });
    }

    // register service provider employee's
    public function registerEmployees($count)
    {
        $results = [];
        DB::beginTransaction();

        try {
            for ($i = 0; $i < $count; $i++) {
                $employee = $this->employees()->create([
                    'id' => Str::ulid(),
                    'tip_code' => Str::ulid()->toString(),
                    'service_provider_id' => $this->id,
                ]);

                $results[] = ['employee_code' => $employee['id']];
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

    public function activateEmployee(String $employeeID): void
    {
        $employee = $this->employees()->where('id', $employeeID)->first();
        if (!$employee) {
            throw new EmployeeNotFoundException;
        }

        $employee->update(['is_active' => true]);
    }

    public function deactivateEmployee(String $employeeID): void
    {
        $employee = $this->employees()->where('id', $employeeID)->first();
        if (!$employee) {
            throw new EmployeeNotFoundException;
        }

        $employee->update(['is_active' => false]);
    }
}
