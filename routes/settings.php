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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/settings/account', [\App\Http\Controllers\Settings\AccountController::class, 'show'])
        ->name('settings.account');

    Route::post('/settings/account', [\App\Http\Controllers\Settings\AccountController::class, 'store']);

    Route::get('/settings/security', [\App\Http\Controllers\Settings\SecurityController::class, 'show'])
        ->name('settings.security');

    Route::post('/settings/security', [\App\Http\Controllers\Settings\SecurityController::class, 'store']);

    Route::get('/settings/security/remove_device/{device_id}', [\App\Http\Controllers\Settings\SecurityController::class, 'logoutDevice']);
});
