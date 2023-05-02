<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class CaptchaController extends Controller
{
    public function reloadCaptcha(): JsonResponse
    {
        return response()->json(['captcha' => captcha_src()]);
    }
}
