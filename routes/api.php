<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDataController;
use App\Http\Controllers\ServiceProviderController;
use App\Http\Controllers\TipController;
use App\Http\Middleware\EnsureTokenIsFor;

Route::post('verify-payment', [TipController::class, 'verifyTipPayment']);

// Service provider routes
Route::prefix('service-providers')->group(function () {
    Route::post('register', [ServiceProviderController::class, 'register']);
    Route::post('login', [ServiceProviderController::class, 'login']);
    Route::post('verify-email', [ServiceProviderController::class, 'verifyEmail']);


    Route::middleware(['auth:sanctum', EnsureTokenIsFor::class.':App\Models\ServiceProvider'])->group(function () {
        Route::get('profile', [ServiceProviderController::class, 'profile']);
        Route::post('logout', [ServiceProviderController::class, 'logout']);
        Route::get('employees', [ServiceProviderController::class, 'getEmployees']);
        Route::post('employees/register', [ServiceProviderController::class, 'registerEmployees']);
        Route::patch('employees/activate/{id}', [ServiceProviderController::class, 'activateEmployee']);
        Route::patch('employees/deactivate/{id}', [ServiceProviderController::class, 'deactivateEmployee']);
        Route::get('employees/summary', [ServiceProviderController::class, 'employeesSummary']);
    });
});

// Category routes
Route::get('/categories', [CategoryController::class, 'index']);

Route::get('/tip/{id}', [TipController::class, 'processTip']);
Route::prefix('employees')->group(function () {
    Route::post('register', [EmployeeController::class, 'completeRegistration']);
    Route::post('login', [EmployeeController::class, 'login']);
    Route::post('verify-email', [EmployeeController::class, 'verifyEmail']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/set-bank-info', [EmployeeController::class, 'completeBankInfo']);
    });
});

// EmployeeData routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/employees-data', [EmployeeDataController::class, 'index']);
    Route::get('/employees-data/{id}', [EmployeeDataController::class, 'show']);
    Route::post('/employees-data', [EmployeeDataController::class, 'store']);
    Route::put('/employees-data/{id}', [EmployeeDataController::class, 'update']);
    Route::put('/employees-data/{id}/password', [EmployeeDataController::class, 'updatePassword']);
    Route::delete('/employees-data/{id}', [EmployeeDataController::class, 'destroy']);
    Route::get('/employees-data/employee/{employeeId}', [EmployeeDataController::class, 'getByEmployeeId']);
     // Profile Management Endpoints
     Route::get('/profile', [EmployeeDataController::class, 'getProfile']);
     Route::put('/profile', [EmployeeDataController::class, 'updateProfile']);
     Route::put('/change-password', [EmployeeDataController::class, 'changePassword']);
     Route::delete('/account', [EmployeeDataController::class, 'deactivateAccount']);
});

// Test route for debugging
Route::get('test', function () {
    return response()->json(['message' => 'API is working', 'time' => now()]);
});
