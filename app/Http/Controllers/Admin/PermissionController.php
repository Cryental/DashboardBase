<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Nav;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use jeremykenedy\LaravelRoles\Traits\RolesAndPermissionsHelpersTrait;

class PermissionController extends Controller
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
        if (!Auth::user()->hasPermission('view.permissions')) {
            abort(403);
        }

        $permissions = $this->permissionRepository->FindAll('', 1, 50);

        $showingText = "Showing {$permissions->firstItem()} to {$permissions->lastItem()} of {$permissions->total()} entries";

        $currentPage = $permissions->currentPage();

        return view('admin.permissions', [
            'permissions' => $permissions,
            'bottomText'  => $showingText,
            'links'       => Nav::getNavLinks($currentPage, $permissions->lastPage()),
            'page'        => $currentPage,
            'search'      => '',
        ]);
    }

    public function search(Request $request)
    {
        if (!Auth::user()->hasPermission('view.permissions')) {
            abort(403);
        }

        $permissions = $this->permissionRepository->FindAll($request->search, $request->p, 50);

        $showingText = "Showing {$permissions->firstItem()} to {$permissions->lastItem()} of {$permissions->total()} entries";

        $currentPage = $permissions->currentPage();

        return view('admin.permissions-list', [
            'permissions' => $permissions,
            'bottomText'  => $showingText,
            'links'       => Nav::getNavLinks($currentPage, $permissions->lastPage()),
            'page'        => $currentPage,
            'search'      => $request->search,
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission('create.permissions')) {
            abort(403);
        }

        $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:permissions',
            'description' => 'sometimes|string|max:255',
        ]);

        $this->permissionRepository->Create([
            'name'        => $request->input('name'),
            'slug'        => $request->input('slug'),
            'description' => $request->input('description'),
        ]);

        $permission = $this->permissionRepository->FindBySlug($request->input('slug'));

        return redirect("/admin/permissions/$permission->id");
    }

    public function editSave(Request $request, $id)
    {
        // no permissions or trying to edit admin or user roles.
        if (!Auth::user()->hasPermission('edit.permissions')) {
            abort(403);
        }

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        $permission = $this->permissionRepository->Update($id, [
            'name'        => $request->input('name'),
            'description' => $request->input('description'),
        ]);

        if (!$permission) {
            abort(403);
        }

        return redirect('/admin/permissions');
    }

    public function edit(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('edit.permissions')) {
            abort(403);
        }

        $permission = $this->permissionRepository->FindById($id);

        if (!$permission) {
            abort(404);
        }

        return response()->view('admin.permission_edit', [
            'permission' => $permission,
        ]);
    }

    public function delete(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('delete.permissions')) {
            abort(403);
        }

        $permission = $this->permissionRepository->FindById($id);

        if (!$permission) {
            return redirect()->route('admin.permissions');
        }

        $this->removeUsersAndRolesFromPermissions($permission);
        $permission->destroy($id);
        $this->destroyPermission($id);

        return redirect()->route('admin.permissions');
    }
}
