<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Nav;
use App\Repositories\DevicesRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    public function __construct()
    {
    }

    public function show(Request $request)
    {
        if (! Auth::user()->hasPermission('view.roles')) {
            abort(403);
        }

        $roles = config('roles.models.role')::paginate(50, ['*'], 'p', 1);
        $permissionsArray = [];

        // Iterate through the roles and get their permissions
        foreach ($roles as $role) {
            $permissions = $role->permissions()->get()->toArray();
            $permissionsArray[$role->id] = $permissions;
        }

        $showingText = "Showing {$roles->firstItem()} to {$roles->lastItem()} of {$roles->total()} entries";

        $currentPage = $roles->currentPage();

        //exit("<pre>". print_r($permissionsArray, true) ."</pre>");
        return view('admin.roles', [
            'roles' => $roles,
            'permissions' => $permissionsArray,
            'permissions_list' => config('roles.models.permission')::all(),
            'bottomText' => $showingText,
            'links' => Nav::getNavLinks($currentPage, $roles->lastPage()),
            'page' => $currentPage,
            'search' => '',
        ]);
    }

    public function search(Request $request)
    {
        if (! Auth::user()->hasPermission('view.roles')) {
            abort(403);
        }

        $roles = config('roles.models.role')::where('name', 'LIKE', "%$request->search%")
            ->orWhere('slug', 'LIKE', "%$request->search%")
            ->orWhere('description', 'LIKE', "%$request->search%")
            ->orWhere('level', 'LIKE', "%$request->search%")
            ->paginate(50, ['*'], 'p', 1);

        $permissionsArray = [];

        // Iterate through the roles and get their permissions
        foreach ($roles as $role) {
            $permissions = $role->permissions()->get()->toArray();
            $permissionsArray[$role->id] = $permissions;
        }

        $showingText = "Showing {$roles->firstItem()} to {$roles->lastItem()} of {$roles->total()} entries";

        $currentPage = $roles->currentPage();

        return view('admin.roles-list', [
            'roles' => $roles,
            'permissions' => $permissionsArray,
            'bottomText' => $showingText,
            'links' => Nav::getNavLinks($currentPage, $roles->lastPage()),
            'page' => $currentPage,
            'search' => $request->search,
        ]);
    }
}
