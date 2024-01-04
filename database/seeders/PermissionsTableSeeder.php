<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
         * Permission Types
         *
         */
        $Permissionitems = [
            [
                'name' => 'Can View Users',
                'slug' => 'view.users',
                'description' => 'Can view users',
                'model' => 'Permission',
            ],
            [
                'name' => 'Can Create Users',
                'slug' => 'create.users',
                'description' => 'Can create new users',
                'model' => 'Permission',
            ],
            [
                'name' => 'Can Edit Users',
                'slug' => 'edit.users',
                'description' => 'Can edit users',
                'model' => 'Permission',
            ],
            [
                'name' => 'Can Delete Users',
                'slug' => 'delete.users',
                'description' => 'Can delete users',
                'model' => 'Permission',
            ],
            [
                'name' => 'Can View Roles',
                'slug' => 'view.roles',
                'description' => 'Can view roles',
                'model' => 'Permission',
            ],
            [
                'name' => 'Can Create Roles',
                'slug' => 'create.roles',
                'description' => 'Can create roles',
                'model' => 'Permission',
            ],
            [
                'name' => 'Can Edit Roles',
                'slug' => 'edit.roles',
                'description' => 'Can edit roles',
                'model' => 'Permission',
            ],
            [
                'name' => 'Can Delete Roles',
                'slug' => 'delete.roles',
                'description' => 'Can delete roles',
                'model' => 'Permission',
            ],
            [
                'name' => 'Can View Permissions',
                'slug' => 'view.permissions',
                'description' => 'Can view permissions',
                'model' => 'Permission',
            ],
            [
                'name' => 'Can Create Permissions',
                'slug' => 'create.permissions',
                'description' => 'Can create permissions',
                'model' => 'Permission',
            ],
            [
                'name' => 'Can Edit Permissions',
                'slug' => 'edit.permissions',
                'description' => 'Can edit permissions',
                'model' => 'Permission',
            ],
            [
                'name' => 'Can Delete Permissions',
                'slug' => 'delete.permissions',
                'description' => 'Can delete permissions',
                'model' => 'Permission',
            ],
        ];

        /*
         * Add Permission Items
         *
         */
        foreach ($Permissionitems as $Permissionitem) {
            $newPermissionitem = config('roles.models.permission')::where('slug', '=', $Permissionitem['slug'])->first();
            if ($newPermissionitem === null) {
                $newPermissionitem = config('roles.models.permission')::create([
                    'name' => $Permissionitem['name'],
                    'slug' => $Permissionitem['slug'],
                    'description' => $Permissionitem['description'],
                    'model' => $Permissionitem['model'],
                ]);
            }
        }
    }
}
