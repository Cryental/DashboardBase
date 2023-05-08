<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PermissionRepository
{
    public function Create(array $inputs): void
    {
        config('roles.models.permission')::create([
            'name' => $inputs('name'),
            'slug' => $inputs('slug'),
            'description' => $inputs('description'),
        ]);
    }

    public function Update($permission_id, array $inputs)
    {
        $permission = $this->FindById($permission_id);

        if (! $permission) {
            return null;
        }

        $permission->update([
            'name' => $inputs('name'),
            'description' => $inputs('description'),
        ]);

        return $permission;
    }

    public function FindById($id)
    {
        return config('roles.models.permission')::find($id);
    }

    public function FindBySlug($slug)
    {
        return config('roles.models.permission')::where('slug', '=', $slug)->first();
    }


    public function FindAll($search, $page, $limit): LengthAwarePaginator|null
    {
        return config('roles.models.permission')::where('name', 'LIKE', "%$search%")
            ->orWhere('slug', 'LIKE', "%$search%")
            ->orWhere('description', 'LIKE', "%$search%")
            ->paginate($limit, ['*'], 'p', $page);
    }
}
