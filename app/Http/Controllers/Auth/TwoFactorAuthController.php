<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\RecoveryCode;

class TwoFactorAuthController extends Controller
{
    /**
     * The two factor authentication provider.
     *
     * @var TwoFactorAuthenticationProvider
     */
    protected TwoFactorAuthenticationProvider $provider;

    /**
     * Create a new action instance.
     *
     * @param  TwoFactorAuthenticationProvider  $provider
     * @return void
     */
    public function __construct(TwoFactorAuthenticationProvider $provider)
    {
        $this->provider = $provider;
    }

    public function show(Request $request)
    {
        $user = $request->user();

        if (empty($user->two_factor_secret)) {
            $this->enableTwoFactor(User::find(Auth::user()->id));
            Auth::user()->refresh();
        }

        if (! $user->two_factor_confirmed) {
            return view('auth.2fa-confirm-code', ['twoFactorSecretKey' => decrypt($user->two_factor_secret)]);
        }

        return redirect()->route('settings.security');
    }

    public function enableTwoFactor(User $user)
    {
        $user->forceFill([
            'two_factor_secret' => encrypt($this->provider->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ])->save();
    }

    public function confirm(Request $request)
    {
        $confirmed = $request->user()->confirmTwoFactorAuth($request->code);

        if (! $confirmed) {
            return back()->withErrors('The provided 2FA code is invalid.');
        }

        return redirect()->route('settings.security')->with('2fa_recovery_key', json_decode(decrypt(Auth::user()->two_factor_recovery_codes)));
    }

    public function cancelTwoFactorAuth()
    {
        Auth::user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_confirmed' => false,
        ])->save();

        return redirect()->route('settings.security');
    }
}
