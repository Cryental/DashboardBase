<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth', 'verified', 'level:2'])->group(function () {
    Route::get('/admin/users-chart-data', [\App\Http\Controllers\Admin\DashboardController::class, 'getUsersChartData']);
    Route::get('/admin', [\App\Http\Controllers\Admin\DashboardController::class, 'show'])
        ->name('admin.dashboard');
    Route::get('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'show'])
        ->name('admin.users');
    Route::post('/admin/users/search', [\App\Http\Controllers\Admin\UserController::class, 'search'])
        ->name('admin.users.search');
    Route::post('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'store']);
    Route::post('/admin/users/{id}/remove', [\App\Http\Controllers\Admin\UserController::class, 'delete']);
    Route::get('/admin/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'edit']);
    Route::post('/admin/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'editSave']);
    Route::get('/admin/users/{id}/remove_device/{device_id}', [\App\Http\Controllers\Admin\UserController::class, 'logoutDevice']);

    Route::get('/admin/roles', [\App\Http\Controllers\Admin\RoleController::class, 'show'])
        ->name('admin.roles');
    Route::post('/admin/roles', [\App\Http\Controllers\Admin\RoleController::class, 'store']);
    Route::post('/admin/roles/search', [\App\Http\Controllers\Admin\RoleController::class, 'search'])
        ->name('admin.roles.search');
    Route::get('/admin/roles/{id}', [\App\Http\Controllers\Admin\RoleController::class, 'edit']);
    Route::post('/admin/roles/{id}/remove', [\App\Http\Controllers\Admin\RoleController::class, 'delete']);
    Route::post('/admin/roles/{id}', [\App\Http\Controllers\Admin\RoleController::class, 'editSave']);

    Route::get('/admin/permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'show'])
        ->name('admin.permissions');
    Route::post('/admin/permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'store']);
    Route::post('/admin/permissions/search', [\App\Http\Controllers\Admin\PermissionController::class, 'search'])
        ->name('admin.permissions.search');
    Route::get('/admin/permissions/{id}', [\App\Http\Controllers\Admin\PermissionController::class, 'edit']);
    Route::post('/admin/permissions/{id}/remove', [\App\Http\Controllers\Admin\PermissionController::class, 'delete']);
    Route::post('/admin/permissions/{id}', [\App\Http\Controllers\Admin\PermissionController::class, 'editSave']);
});
