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

class UserController extends Controller
{
    private UserRepository $userRepository;
    private DevicesRepository $devicesRepository;

    public function __construct(UserRepository $userRepository, DevicesRepository $devicesRepository)
    {
        $this->userRepository = $userRepository;
        $this->devicesRepository = $devicesRepository;
    }

    public function show(Request $request)
    {
        if (! Auth::user()->hasPermission('view.users')) {
            abort(403);
        }

        $users = $this->userRepository->FindAll('', 1, 5);

        $showingText = "Showing {$users->firstItem()} to {$users->lastItem()} of {$users->total()} entries";

        $currentPage = $users->currentPage();

        $roles = config('roles.models.role')::all();

        return view('admin.users', [
            'users' => $users,
            'bottomText' => $showingText,
            'links' => Nav::getNavLinks($currentPage, $users->lastPage()),
            'page' => $currentPage,
            'search' => $request->search,
            'roles' => $roles,
        ]);
    }

    public function search(Request $request)
    {
        if (! Auth::user()->hasPermission('view.users')) {
            abort(403);
        }

        $users = $this->userRepository->FindAll($request->search, $request->p, 5);
ray($request->all());
        $showingText = "Showing {$users->firstItem()} to {$users->lastItem()} of {$users->total()} entries";

        $currentPage = $users->currentPage();

        return view('admin.users-list', [
            'users' => $users,
            'bottomText' => $showingText,
            'links' => Nav::getNavLinks($currentPage, $users->lastPage()),
            'page' => $currentPage,
            'search' => $request->search,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if (! Auth::user()->hasPermission('edit.users')) {
            abort(403);
        }

        $user = $this->userRepository->Find($id);

        if (! $user) {
            abort(404);
        }

        $devices = $this->devicesRepository->FindAllUserDevices($user->id)->reverse()->toArray();

        $currentSessionID = $request->session()->getId();

        $deviceArrays = [];

        foreach ($devices as $device) {
            $dd = new DeviceDetector($device->user_agent);
            $dd->parse();

            $device->agentPlatform = $dd->getOs('name').' '.$dd->getOs('version');
            $device->agentBrowser = $dd->getClient('name').' '.$dd->getClient('version');
            $device->deviceDetector = DeviceDTO::fromModel($dd)->GetDTO();

            $deviceArrays[] = $device;
        }

        $roles = config('roles.models.role')::all();

        return response()->view('admin.user_edit', [
            'user' => $user,
            'devices' => $deviceArrays,
            'sessionID' => $currentSessionID,
            'roles' => $roles,
        ]);
    }

    public function logoutDevice(Request $request, $id, $device_id)
    {
        if (! Auth::user()->hasPermission('edit.users')) {
            abort(403);
        }

        $this->devicesRepository->LogoutUserDevice($id, $device_id);

        return back();
    }

    public function store(Request $request)
    {
        if (! Auth::user()->hasPermission('create.users')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string|in:Member,Admin',
            'bio' => 'sometimes|string|max:1000|nullable',
            'website_url' => 'sometimes|url|max:500|nullable',
            'email-verification' => 'required|string|in:Unverified,Verified',
            'password' => 'required|string|min:8',
        ]);

        $user = User::query()->create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'role' => $request->input('role'),
            'bio' => $request->input('bio'),
            'website_url' => $request->input('website_url'),
            'email_verified_at' => $request->input('email-verification') === 'Verified' ? now() : null,
            'password' => Hash::make($request->input('password')),
            'remember_token' => Str::random(60),
        ]);

        return redirect('/admin/users/'.$user->id);
    }

    public function editSave(Request $request, $id)
    {
        if (! Auth::user()->hasPermission('edit.users')) {
            abort(403);
        }

        $user = $this->userRepository->Find($id);

        if (! $user) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'role' => 'required|int',
            'bio' => 'sometimes|string|max:1000|nullable',
            'website_url' => 'sometimes|url|max:500|nullable',
            'email-verification' => 'required|string|in:Unverified,Verified',
            'password' => 'sometimes|string|nullable',
        ]);

        $this->userRepository->Update($user->id, $request->all());

        return redirect('/admin/users/'.$user->id);
    }

    public function delete(Request $request, $id)
    {
        if (! Auth::user()->hasPermission('delete.users')) {
            abort(403);
        }

        if ($id == 1) {
            abort(403);
        }

        $user = User::query()->find($id);

        if (! $user) {
            return redirect()->route('admin.users');
        }

        $user->delete();

        return redirect()->route('admin.users');
    }
}
