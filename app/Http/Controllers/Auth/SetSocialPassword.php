<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\PasswordValidationRules;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SetSocialPassword extends Controller
{
    use PasswordValidationRules;

    public function show(Request $request)
    {
        return view('auth.social-password');
    }

    public function store(Request $request) {
        $request->validate([
            'password' => $this->passwordRules(),
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        \Auth::login($request->user());

        return redirect('/');
    }
}
