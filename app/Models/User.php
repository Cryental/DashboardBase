<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use jeremykenedy\LaravelRoles\Traits\HasRoleAndPermission;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticationProvider;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoleAndPermission;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'provider',
        'provider_id',
        'bio',
        'website_url',
        'role',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function confirmTwoFactorAuth($code)
    {
        if ($this->two_factor_secret === null) {
            return false;
        }

        $codeIsValid = app(TwoFactorAuthenticationProvider::class)
            ->verify(decrypt($this->two_factor_secret), $code);

        if ($codeIsValid) {
            $this->two_factor_confirmed = true;
            $this->two_factor_confirmed_at = now();
            $this->save();

            return true;
        }

        return false;
    }
}
