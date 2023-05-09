<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Nav;
use App\Models\User;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use jeremykenedy\LaravelRoles\Traits\RolesAndPermissionsHelpersTrait;

class RoleController extends Controller
{
    use RolesAndPermissionsHelpersTrait;
    private RoleRepository $roleRepository;
    private PermissionRepository $permissionRepository;

    public function __construct(RoleRepository $roleRepository, PermissionRepository $permissionRepository)
    {
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
    }

    public function show(Request $request)
    {
        if (!Auth::user()->hasPermission('view.roles')) {
            abort(403);
        }

        $roles = $this->roleRepository->FindAll('', 1, 50);

        $permissionsArray = [];

        // Iterate through the roles and get their permissions
        foreach ($roles as $role) {
            $permissions = $role->permissions()->get()->toArray();
            $permissionsArray[$role->id] = $permissions;
        }

        $showingText = "Showing {$roles->firstItem()} to {$roles->lastItem()} of {$roles->total()} entries";

        $currentPage = $roles->currentPage();

        return view('admin.roles', [
            'roles'            => $roles,
            'permissions'      => $permissionsArray,
            'permissions_list' => config('roles.models.permission')::all(),
            'bottomText'       => $showingText,
            'links'            => Nav::getNavLinks($currentPage, $roles->lastPage()),
            'page'             => $currentPage,
            'search'           => '',
        ]);
    }

    public function search(Request $request)
    {
        if (!Auth::user()->hasPermission('view.roles')) {
            abort(403);
        }

        $roles = $this->roleRepository->FindAll($request->search, $request->p, 50);

        $permissionsArray = [];

        // Iterate through the roles and get their permissions
        foreach ($roles as $role) {
            $permissions = $role->permissions()->get()->toArray();
            $permissionsArray[$role->id] = $permissions;
        }

        $showingText = "Showing {$roles->firstItem()} to {$roles->lastItem()} of {$roles->total()} entries";

        $currentPage = $roles->currentPage();

        return view('admin.roles-list', [
            'roles'       => $roles,
            'permissions' => $permissionsArray,
            'bottomText'  => $showingText,
            'links'       => Nav::getNavLinks($currentPage, $roles->lastPage()),
            'page'        => $currentPage,
            'search'      => $request->search,
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission('create.roles')) {
            abort(403);
        }

        $request->validate([
            'name'          => 'required|string|max:255',
            'slug'          => 'required|string|max:255|unique:roles',
            'description'   => 'required|string|max:255',
            'level'         => 'required|integer|max:11',
            'permissions'   => 'required|array',
            'permissions.*' => [
                'integer',
                Rule::exists('permissions', 'id'),
            ],
        ]);

        $this->roleRepository->Create([
            'name'        => $request->input('name'),
            'slug'        => $request->input('slug'),
            'description' => $request->input('description'),
            'level'       => (int) $request->input('level'),
        ]);

        $role = $this->roleRepository->FindBySlug($request->input('slug'));

        foreach ($request->input('permissions') as $permission) {
            $role->attachPermission($permission);
        }

        return redirect("/admin/roles/$role->id");
    }

    public function editSave(Request $request, $id)
    {
        // no permissions or trying to edit admin or user roles.
        if (!Auth::user()->hasPermission('edit.roles') || $id == 1 || $id == 2) {
            abort(403);
        }

        $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'required|string|max:255',
            'level'         => 'required|integer|max:11',
            'permissions'   => 'required|array',
            'permissions.*' => [
                'integer',
                Rule::exists('permissions', 'id'),
            ],
        ]);

        $role = $this->roleRepository->Update($id, [
            'name'        => $request->input('name'),
            'description' => $request->input('description'),
            'level'       => (int) $request->input('level'),
            'permissions' => $request->input('permissions'),
        ]);

        if (!$role) {
            abort(403);
        }

        return redirect("/admin/roles/$id");
    }

    public function edit(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('edit.roles') || $id == 1 || $id == 2) {
            abort(403);
        }

        $role = $this->roleRepository->FindById($id);

        if (!$role) {
            abort(404);
        }

        $permissionsArray = [];

        $fullPermissions = $this->permissionRepository->FindAll('', 1, 5000);

        $permissions = $role->permissions()->pluck('slug')->toArray();

        foreach ($fullPermissions as $permission) {
            $permissionsArray[] = [
                'id'      => $permission->id,
                'name'    => $permission->name,
                'slug'    => $permission->slug,
                'checked' => in_array($permission->slug, $permissions),
            ];
        }

        return response()->view('admin.role_edit', [
            'role'        => $role,
            'permissions' => $permissionsArray,
        ]);
    }

    public function delete(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('delete.roles') || $id == 1 || $id == 2) {
            abort(403);
        }

        $role = $this->roleRepository->FindById($id);

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
