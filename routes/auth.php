<?php

use App\Http\Controllers\SocialController;
use Illuminate\Support\Facades\Route;

Route::get('login/{provider}', [SocialController::class, 'redirect']);
Route::get('login/{provider}/callback', [SocialController::class, 'callback']);
Route::get('/reload-captcha', [\App\Http\Controllers\Auth\CaptchaController::class, 'reloadCaptcha']);
