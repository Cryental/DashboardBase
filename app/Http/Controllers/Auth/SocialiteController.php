<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(Request $request, $provider)
    {
        $userSocial = Socialite::driver('google')->user();
        $user = User::query()->where(['provider' => 'google', 'provider_id' => $userSocial->getId()])->get()->first();

        if (!$user) {
            $user = User::query()->create([
                'name'              => $userSocial->getName(),
                'email'             => $userSocial->getEmail(),
                'password'          => null,
                'provider'          => 'google',
                'provider_id'       => $userSocial->getId(),
                'email_verified_at' => Carbon::now(),
            ]);

            if (User::count() === 1) {
                $role = config('roles.models.role')::where('name', '=', 'Admin')->first();
            } else {
                $role = config('roles.models.role')::where('name', '=', 'User')->first();
            }

            $user->attachRole($role);

            Auth::login($user);
        } elseif ($user->email_verified_at === null) {
            $user->email_verified_at = now();
            $user->save();
        } else {
            if ($user->two_factor_confirmed) {
                session()->put([
                    'login.id'       => $user->getKey(),
                    'login.remember' => false,
                ]);

                TwoFactorAuthenticationChallenged::dispatch($user);

                return redirect()->route('two-factor.login');
            } else {
                Auth::login($user);
            }
        }

        return redirect('/');
    }
}
