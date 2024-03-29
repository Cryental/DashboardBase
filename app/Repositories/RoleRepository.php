<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoleRepository
{
    public function Create(array $inputs): void
    {
        config('roles.models.role')::create([
            'name' => $inputs['name'],
            'slug' => $inputs['slug'],
            'description' => $inputs['description'],
            'level' => $inputs['level'],
        ]);
    }

    public function Update($role_id, array $inputs)
    {
        $role = $this->FindById($role_id);

        if (! $role) {
            return null;
        }

        $role->update([
            'name' => $inputs['name'],
            'description' => $inputs['description'],
            'level' => $inputs['level'],
        ]);

        if (! empty($inputs['permissions'])) {
            $role->detachAllPermissions();

            foreach ($inputs['permissions'] as $permission) {
                $role->attachPermission($permission);
            }
        }

        return $role;
    }

    public function FindBySlug($slug)
    {
        return config('roles.models.role')::where('slug', '=', $slug)->first();
    }

    public function FindById($id)
    {
        return config('roles.models.role')::find($id);
    }

    public function FindAll($search, $page, $limit): ?LengthAwarePaginator
    {
        return config('roles.models.role')::where('name', 'LIKE', "%$search%")
            ->orWhere('slug', 'LIKE', "%$search%")
            ->orWhere('description', 'LIKE', "%$search%")
            ->orWhere('level', 'LIKE', "%$search%")
            ->paginate($limit, ['*'], 'p', $page);
    }
}
