<?php

namespace App\Http\Controllers\Admin;

use App\DataTransferObjects\DeviceDTO;
use App\Facades\Nav;
use App\Models\User;
use App\Repositories\DevicesRepository;
use App\Repositories\UserRepository;
use DeviceDetector\DeviceDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use jeremykenedy\LaravelRoles\Traits\RolesAndPermissionsHelpersTrait;

class RoleController extends Controller
{
    use RolesAndPermissionsHelpersTrait;

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

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission('create.roles')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles',
            'description' => 'required|string|max:255',
            'level' => 'required|integer|max:11',
            'permissions' => 'required|array',
            'permissions.*' => [
                'integer',
                Rule::exists('permissions', 'id')
            ],
        ]);

        config('roles.models.role')::create([
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'description' => $request->input('description'),
            'level' => (int) $request->input('level'),
        ]);

        $role = config('roles.models.role')::where('slug', '=', $request->input('slug'))->first();

        foreach ($request->input('permissions') as $permission) {
            $role->attachPermission($permission);
        }

        return redirect('/admin/roles/' . $role->id);
    }

    public function editSave(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('edit.roles')) {
            abort(403);
        }

        if ($id == 1 || $id == 2) {
            abort(403);
        }

        $role = config('roles.models.role')::find($id);

        if (!$role) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'level' => 'required|integer|max:11',
            'permissions' => 'required|array',
            'permissions.*' => [
                'integer',
                Rule::exists('permissions', 'id')
            ],
        ]);

        $role->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'level' => (int) $request->input('level'),
        ]);

        $role->detachAllPermissions();

        foreach ($request->input('permissions') as $permission) {
            $role->attachPermission($permission);
        }

        return redirect('/admin/roles/' . $role->id);
    }

    public function edit(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('edit.roles')) {
            abort(403);
        }

        if ($id == 1 || $id == 2) {
            abort(403);
        }

        $role = config('roles.models.role')::query()->find($id);

        if (!$role) {
            abort(404);
        }

        $permissionsArray = [];

        $fullPermissions = config('roles.models.permission')::all();
        $permissions = $role->permissions()->pluck('slug')->toArray();

        foreach ($fullPermissions as $permission) {
            if (in_array($permission->slug, $permissions)) {
                $permissionsArray[] = [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'slug' => $permission->slug,
                    'checked' => true,
                ];
            } else {
                $permissionsArray[] = [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'slug' => $permission->slug,
                    'checked' => false,
                ];
            }
        }

        return response()->view('admin.role_edit', [
            'role' => $role,
            'permissions' => $permissionsArray,
        ]);
    }

    public function delete(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('delete.roles')) {
            abort(403);
        }

        if ($id == 1 || $id == 2) {
            abort(403);
        }

        $role = config('roles.models.role')::find($id);

        if (!$role) {
            return redirect()->route('admin.roles');
        }

        $users = User::cursor();

        foreach ($users as $user) {
            if ($user->hasRole($role->slug)) {
                $user->detachAllRoles();
                $user->attachRole(2);
            }
        }

        $role->destroy($id);

        $this->destroyRole($id);

        return redirect()->route('admin.roles');
    }
}
