<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDataController;
use App\Http\Controllers\ServiceProviderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TipController;

Route::post('verify-payment', [TipController::class, 'verifyTipPayment']);

// Service provider routes
Route::prefix('service-providers')->group(function () {
    Route::post('register', [ServiceProviderController::class, 'register']);
    Route::post('login', [ServiceProviderController::class, 'login']);
    Route::post('verify-email', [ServiceProviderController::class, 'verifyEmail']);


    Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [ServiceProviderController::class, 'profile']);
    Route::post('logout', [ServiceProviderController::class, 'logout']);
    Route::get('employees', [ServiceProviderController::class, 'getEmployees']);
    Route::post('employees/register', [ServiceProviderController::class, 'registerEmployees']);
    Route::patch('employees/{id}/activate', [ServiceProviderController::class, 'activateEmployee']);
    Route::patch('employees/{id}/deactivate', [ServiceProviderController::class, 'deactivateEmployee']);
    Route::patch('employees/status', [ServiceProviderController::class, 'setEmployeesStatus']);
    Route::get('employee-summary', [ServiceProviderController::class, 'employeeSummary']);
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
});

// Test route for debugging
Route::get('test', function () {
    return response()->json(['message' => 'API is working', 'time' => now()]);
});

// User authentication routes
Route::prefix('users')->group(function () {
    Route::post('register', [UserController::class, 'register']);
    Route::match(['GET', 'POST'], 'verify-email', [UserController::class, 'verifyEmail']);
    Route::post('get-token', [UserController::class, 'getVerificationToken']); // Testing helper
    Route::post('login', [UserController::class, 'login']);
    Route::post('resend-verification', [UserController::class, 'resendVerification']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [UserController::class, 'profile']);
        Route::put('profile', [UserController::class, 'updateProfile']);
        Route::put('password', [UserController::class, 'changePassword']);
        Route::post('logout', [UserController::class, 'logout']);
    });
});
