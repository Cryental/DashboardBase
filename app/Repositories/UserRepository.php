<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
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

    public function FindUsersCount($search): int
    {
        return User::query()
            ->where('name', 'LIKE', "%$search%")
            ->orWhere('email', 'LIKE', "%$search%")
            ->count();
    }

    public function GetUsersStatistics(int $days)
    {

        // Calculate the dates for the current period and the previous period
        $currentPeriodStart = Carbon::now()->subDays($days)->toDateTimeString(); //what is it for?
        $previousPeriodStart = Carbon::now()->subDays($days * 2)->toDateTimeString();

        // Get the number of users registered in the current period and the previous period
        $currentPeriodRegistrations = User::where('created_at', '>=', $currentPeriodStart)->count();
        $previousPeriodRegistrations = User::whereBetween('created_at', [$previousPeriodStart, $currentPeriodStart])->count();

        // Calculate the percentage growth
        $percentageGrowth = 0;
        if ($previousPeriodRegistrations > 0) {
            $percentageGrowth = (($currentPeriodRegistrations - $previousPeriodRegistrations) / $previousPeriodRegistrations) * 100;
        }

        $distribution = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereDate('created_at', '>=', $currentPeriodStart)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'distribution' => $distribution,
            'current' => $currentPeriodRegistrations,
            'previous' => $previousPeriodRegistrations,
            'percentage_growth' => round($percentageGrowth, 2)
        ];
    }
}
