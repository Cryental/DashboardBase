<?php

use App\Http\Controllers\Auth\SetSocialPassword;
use App\Http\Controllers\Auth\TwoFactorAuthController;
use Illuminate\Support\Facades\Route;

Route::get('login/{provider}', [\App\Http\Controllers\Auth\SocialiteController::class, 'redirect']);
Route::get('login/{provider}/callback', [\App\Http\Controllers\Auth\SocialiteController::class, 'handleProviderCallback']);

Route::get('/set-social-password', [SetSocialPassword::class, 'show'])->middleware('auth')->name('set-social-password');
Route::post('/set-social-password', [SetSocialPassword::class, 'store'])->middleware('auth')->name('set-social-password');

Route::get('/2fa-setup', [TwoFactorAuthController::class, 'show'])->middleware(['auth', 'password.confirm'])->name('two-factor.setup');
Route::post('/2fa-setup', [TwoFactorAuthController::class, 'confirm'])->middleware('auth');
Route::post('/2fa-cancel', [TwoFactorAuthController::class, 'cancelTwoFactorAuth'])->middleware('auth')->name('two-factor.destroy');
Route::post('/2fa-confirm', [TwoFactorAuthController::class, 'confirm'])->name('two-factor.confirm');

Route::get('/reload-captcha', [\App\Http\Controllers\Auth\CaptchaController::class, 'reloadCaptcha']);
