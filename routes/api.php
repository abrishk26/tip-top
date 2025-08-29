<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureTokenIsFor;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDataController;
use App\Http\Controllers\ServiceProviderController;
use App\Http\Controllers\TipController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\ServiceProviderAdminController;
use App\Http\Controllers\Admin\AdminEmployeeController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminConfigController;

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
    Route::delete('/employees-data/{id}', [EmployeeDataController::class, 'destroy']);
    Route::get('/employees-data/employee/{employeeId}', [EmployeeDataController::class, 'getByEmployeeId']);
     // Profile Management Endpoints
     Route::get('/profile', [EmployeeDataController::class, 'getProfile']);
     Route::put('/profile', [EmployeeDataController::class, 'updateProfile']);
     Route::put('/password', [EmployeeDataController::class, 'updatePassword']);
     Route::delete('/account', [EmployeeDataController::class, 'deactivateAccount']);
});

// Test route for debugging
Route::get('test', function () {
    return response()->json(['message' => 'API is working', 'time' => now()]);
});

// Admin auth routes
Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
        Route::get('profile', [AdminAuthController::class, 'profile']);
        Route::post('logout', [AdminAuthController::class, 'logout']);

        // Admin -> Service Providers management
        Route::prefix('service-providers')->group(function () {
            Route::get('/', [ServiceProviderAdminController::class, 'index']);
            Route::get('{id}', [ServiceProviderAdminController::class, 'show']);
            Route::post('{id}/accept', [ServiceProviderAdminController::class, 'accept']);
            Route::post('{id}/reject', [ServiceProviderAdminController::class, 'reject']);
            Route::post('{id}/suspend', [ServiceProviderAdminController::class, 'suspend']);
            Route::post('{id}/unsuspend', [ServiceProviderAdminController::class, 'unsuspend']);
            Route::get('{id}/employees', [ServiceProviderAdminController::class, 'employees']);
        });

        // Admin -> Employees management
        Route::prefix('employees')->group(function () {
            Route::get('/', [AdminEmployeeController::class, 'index']);
            Route::get('{id}', [AdminEmployeeController::class, 'show']);
            Route::post('{id}/activate', [AdminEmployeeController::class, 'activate']);
            Route::post('{id}/deactivate', [AdminEmployeeController::class, 'deactivate']);
            Route::post('{id}/suspend', [AdminEmployeeController::class, 'suspend']);
            Route::post('{id}/unsuspend', [AdminEmployeeController::class, 'unsuspend']);
        });

        // Admin -> Reporting & Analytics
        Route::prefix('reports')->group(function () {
            Route::get('overview', [AdminReportController::class, 'overview']);
            Route::get('tips', [AdminReportController::class, 'tips']);
            Route::get('payments', [AdminReportController::class, 'payments']);
        });

        // Admin -> Categories config
        Route::prefix('categories')->group(function () {
            Route::get('/', [AdminConfigController::class, 'listCategories']);
            Route::post('/', [AdminConfigController::class, 'createCategory']);
            Route::put('{id}', [AdminConfigController::class, 'updateCategory']);
            Route::delete('{id}', [AdminConfigController::class, 'deleteCategory']);
        });
    });
});
