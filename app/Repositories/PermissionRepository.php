<?php

namespace App\Repositories;

class PermissionRepository
{
    public function FindAll()
    {
        return config('roles.models.permission')::all();
    }
}
