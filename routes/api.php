<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ServiceProviderController;

Route::prefix('service-providers')->group(function () {
    Route::post('register', [ServiceProviderController::class, 'register']);
    Route::post('login', [ServiceProviderController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [ServiceProviderController::class, 'profile']);
        Route::get('employees', [ServiceProviderController::class, 'getEmployees']);
        Route::post('employees/register', [ServiceProviderController::class, 'registerEmployees']);
        Route::patch('employees/{id}/activate', [ServiceProviderController::class, 'activateEmployee']);
        Route::patch('employees/{id}/deactivate', [ServiceProviderController::class, 'deactivateEmployee']);
        Route::patch('employees/status', [ServiceProviderController::class, 'setEmployeesStatus']);
        Route::get('employee-summary', [ServiceProviderController::class, 'employeeSummary']);
    });
});

Route::get('/categories', [CategoryController::class, 'index']);
