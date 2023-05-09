<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DeviceRepository
{
    public function Find($user_id): Model|\Illuminate\Database\Eloquent\Collection|Builder|array|null
    {
        return User::query()->find($user_id);
    }

    public function FindAllUserDevices($user_id): \Illuminate\Support\Collection
    {
        return DB::table('sessions')
            ->where('user_id', $user_id)
            ->get();
    }

    public function LogoutUserDevice($user_id, $device_id): void
    {
        DB::table('sessions')
            ->where('id', $device_id)
            ->where('user_id', $user_id)->delete();
    }
}
