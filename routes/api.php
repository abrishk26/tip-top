<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDataController;

// Category routes
Route::get('/categories', [CategoryController::class, 'index']);

// Employee routes
Route::get('/employees', [EmployeeController::class, 'index']);
Route::get('/employees/{id}', [EmployeeController::class, 'show']);
Route::put('/employees/{id}/profile', [EmployeeController::class, 'updateProfile']);
Route::put('/employees/{id}/password', [EmployeeController::class, 'changePassword']);
Route::post('/employees/{id}/complete-registration', [EmployeeController::class, 'completeRegistration']);

// EmployeeData routes
Route::get('/employees-data', [EmployeeDataController::class, 'index']);
Route::get('/employees-data/{id}', [EmployeeDataController::class, 'show']);
Route::post('/employees-data', [EmployeeDataController::class, 'store']);
Route::put('/employees-data/{id}', [EmployeeDataController::class, 'update']);
Route::put('/employees-data/{id}/password', [EmployeeDataController::class, 'updatePassword']);
Route::delete('/employees-data/{id}', [EmployeeDataController::class, 'destroy']);
Route::get('/employees-data/employee/{employeeId}', [EmployeeDataController::class, 'getByEmployeeId']);