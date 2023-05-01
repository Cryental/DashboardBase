<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $userSocial = Socialite::driver('google')->user();
        $user = User::query()->where(['provider' => 'google', 'provider_id' => $userSocial->getId()])->get()->first();

        if (! $user) {
            $user = User::query()->create([
                'name' => $userSocial->getName(),
                'email' => $userSocial->getEmail(),
                'password' => Hash::make(Str::random(24)),
                'provider' => 'google',
                'provider_id' => $userSocial->getId(),
                'email_verified_at' => Carbon::now(),
            ]);

            Auth::login($user);
        } elseif ($user->email_verified_at === null) {
            $user->email_verified_at = now();
            $user->save();
        } else {
            Auth::login($user);
        }

        return redirect('/');
    }
}
