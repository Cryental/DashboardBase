<?php

namespace App\Repositories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DevicesRepository
{
//    public function Create(array $inputs): Model|Builder
//    {
//        return Subscription::query()->create([
//            'user_id'      => $inputs['user_id'],
//            'plan_id'      => $inputs['plan_id'],
//            'status'       => $inputs['status'],
//            'activated_at' => $inputs['activated_at'] ?? Carbon::now(),
//            'expires_at'   => $inputs['expires_at'],
//            'cancels_at'   => null,
//            'cancelled_at' => null,
//        ]);
//    }
//
//    public function Clone($user_id, $subscription_id, $inputs): Builder|Model|null
//    {
//        $subscription = $this->Find($user_id, $subscription_id);
//
//        if (!$subscription) {
//            return null;
//        }
//
//        return Subscription::query()->create([
//            'user_id'      => $user_id,
//            'plan_id'      => $inputs['plan_id'] ?? $subscription->plan_id,
//            'status'       => $inputs['status'] ?? $subscription->status,
//            'activated_at' => $inputs['activated_at'] ?? $subscription->activated_at,
//            'expires_at'   => $inputs['expires_at'] ?? $subscription->expires_at,
//            'expired_at'   => $inputs['expired_at'] ?? $subscription->expired_at,
//            'cancels_at'   => $inputs['cancels_at'] ?? $subscription->cancels_at,
//            'cancelled_at' => $inputs['cancelled_at'] ?? $subscription->cancelled_at,
//        ]);
//    }
//
//    public function Update($user_id, $subscription_id, array $inputs): ?object
//    {
//        $subscription = $this->Find($user_id, $subscription_id);
//
//        if (!$subscription) {
//            return null;
//        }
//
//        if (array_key_exists('status', $inputs)) {
//            $subscription->status = $inputs['status'];
//        }
//
//        if (array_key_exists('cancels_at', $inputs)) {
//            $subscription->cancels_at = $inputs['cancels_at'];
//        }
//
//        if (array_key_exists('cancelled_at', $inputs)) {
//            $subscription->cancelled_at = $inputs['cancelled_at'];
//        }
//
//        if (array_key_exists('expires_at', $inputs)) {
//            $subscription->expires_at = $inputs['expires_at'];
//        }
//
//        if (array_key_exists('expired_at', $inputs)) {
//            $subscription->expired_at = $inputs['expired_at'];
//        }
//
//        $subscription->save();
//
//        return $subscription;
//    }
//
    public function Find($user_id): Model|\Illuminate\Database\Eloquent\Collection|Builder|array|null
    {
        return User::query()->find($user_id);
    }
//
//    public function FindUserActiveSubscription($user_id): Builder|Model|null
//    {
//        return Subscription::with('plan')
//            ->where('user_id', $user_id)
//            ->where('status', SubscriptionStatus::ACTIVE)
//            ->first();
//    }
//
//    public function FindUserInactiveSubscription($user_id): Builder|Model|null
//    {
//        return Subscription::with('plan')
//            ->where('user_id', $user_id)
//            ->where('status', SubscriptionStatus::INACTIVE)
//            ->orderBy('activated_at', 'ASC')
//            ->first();
//    }
//
//    public function Delete($user_id, $subscription_id): ?bool
//    {
//        $toBeDeletedSub = $this->Find($user_id, $subscription_id);
//
//        if (!$toBeDeletedSub) {
//            return null;
//        }
//
//        $toBeDeletedSub->delete();
//
//        return true;
//    }

    public function FindAllUserDevices($user_id): \Illuminate\Support\Collection
    {
        return DB::table('sessions')
            ->where('user_id',$user_id)
            ->get();
    }

    public function LogoutUserDevice($user_id, $device_id): void
    {
        DB::table('sessions')
            ->where('id', $device_id)
            ->where('user_id', $user_id)->delete();
    }
}