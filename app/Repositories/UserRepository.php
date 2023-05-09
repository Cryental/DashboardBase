<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    public function Update($user_id, array $inputs): ?object
    {
        $user = $this->Find($user_id);

        if (!$user) {
            return null;
        }

        if (array_key_exists('name', $inputs)) {
            $user->name = $inputs['name'];
        }

        if (array_key_exists('email', $inputs)) {
            $user->email = $inputs['email'];
        }

        if (array_key_exists('role', $inputs)) {
            if ($user_id != 1) {
                $user->detachAllRoles();
                $user->attachRole($inputs['role']);
            }
        }

        if (array_key_exists('bio', $inputs)) {
            $user->bio = $inputs['bio'];
        }

        if (array_key_exists('website_url', $inputs)) {
            $user->website_url = $inputs['website_url'];
        }

        if ($user_id != 1) {
            if (!$user->email_verified_at && $inputs['email-verification'] === 'Verified') {
                $user->email_verified_at = now();
            } elseif ($user->email_verified_at && $inputs['email-verification'] === 'Unverified') {
                $user->email_verified_at = null;
            }
        }

        if (!empty($inputs['password'])) {
            $user->password = Hash::make($inputs['password']);
        }

        $user->save();

        return $user;
    }

    public function Find($user_id): Model|\Illuminate\Database\Eloquent\Collection|Builder|array|null
    {
        return User::query()->find($user_id);
    }

    public function FindAll($search, $page, $limit): LengthAwarePaginator|null
    {
        return User::query()
            ->where('name', 'LIKE', "%$search%")
            ->orWhere('email', 'LIKE', "%$search%")
            ->paginate($limit, ['*'], 'p', $page);
    }
}
