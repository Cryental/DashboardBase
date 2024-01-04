<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function show()
    {
        return view('settings.account');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.Auth::id(),
            'bio' => 'sometimes|string|max:1000|nullable',
            'website_url' => 'sometimes|url|max:500|nullable',
        ]);

        $user = Auth::user();

        $currentEmail = $user->email;

        $user->name = $request->name;

        if ($user->email != $request->email) {
            $user->email = $request->email;
            $user->email_verified_at = null;
        }

        $user->bio = $request->bio;
        $user->website_url = $request->website_url;
        $user->save();

        if ($currentEmail != $request->email) {
            $request->user()->sendEmailVerificationNotification();

            return redirect()->route(RouteServiceProvider::HOME);
        }

        return redirect()->route('settings.account')->with('status', 'Your account is updated successfully.');
    }
}
