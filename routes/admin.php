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

Route::prefix('admin')->group(function () {
    Route::middleware(['auth', 'verified', 'level:2'])->group(function () {
        Route::get('/users-stats', [\App\Http\Controllers\Admin\DashboardController::class, 'getUserStats']);
        Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'show'])
            ->name('admin.dashboard');
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'show'])
            ->name('admin.users');
        Route::post('/users/search', [\App\Http\Controllers\Admin\UserController::class, 'search'])
            ->name('admin.users.search');
        Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store']);
        Route::post('/users/{id}/remove', [\App\Http\Controllers\Admin\UserController::class, 'delete']);
        Route::get('/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'edit']);
        Route::post('/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'editSave']);
        Route::get('/users/{id}/remove_device/{device_id}', [\App\Http\Controllers\Admin\UserController::class, 'logoutDevice']);

        Route::get('/roles', [\App\Http\Controllers\Admin\RoleController::class, 'show'])
            ->name('admin.roles');
        Route::post('/roles', [\App\Http\Controllers\Admin\RoleController::class, 'store']);
        Route::post('/roles/search', [\App\Http\Controllers\Admin\RoleController::class, 'search'])
            ->name('admin.roles.search');
        Route::get('/roles/{id}', [\App\Http\Controllers\Admin\RoleController::class, 'edit']);
        Route::post('/roles/{id}/remove', [\App\Http\Controllers\Admin\RoleController::class, 'delete']);
        Route::post('/roles/{id}', [\App\Http\Controllers\Admin\RoleController::class, 'editSave']);

        Route::get('/permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'show'])
            ->name('admin.permissions');
        Route::post('/permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'store']);
        Route::post('/permissions/search', [\App\Http\Controllers\Admin\PermissionController::class, 'search'])
            ->name('admin.permissions.search');
        Route::get('/permissions/{id}', [\App\Http\Controllers\Admin\PermissionController::class, 'edit']);
        Route::post('/permissions/{id}/remove', [\App\Http\Controllers\Admin\PermissionController::class, 'delete']);
        Route::post('/permissions/{id}', [\App\Http\Controllers\Admin\PermissionController::class, 'editSave']);
    });
});
