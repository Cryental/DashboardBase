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

Route::middleware(['auth', 'verified', 'role:admin', 'level:2'])->group(function () {
    Route::get('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'show'])
        ->name('admin.users');
    Route::post('/admin/users/search', [\App\Http\Controllers\Admin\UserController::class, 'search'])
        ->name('admin.users.search');
    Route::post('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'store']);
    Route::post('/admin/users/{id}/remove', [\App\Http\Controllers\Admin\UserController::class, 'delete']);
    Route::get('/admin/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'edit']);
    Route::post('/admin/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'editSave']);
    Route::get('/admin/users/{id}/remove_device/{device_id}', [\App\Http\Controllers\Admin\UserController::class, 'logoutDevice']);
});
