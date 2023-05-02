<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CaptchaController extends Controller
{
    public function reloadCaptcha(): JsonResponse
    {
        return response()->json(['captcha' => captcha_src()]);
    }
}
