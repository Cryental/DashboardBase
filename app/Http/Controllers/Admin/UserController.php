<?php

namespace App\Http\Controllers\Admin;

use App\DataTransferObjects\DeviceDTO;
use App\Facades\Nav;
use App\Models\User;
use App\Repositories\DeviceRepository;
use App\Repositories\UserRepository;
use DeviceDetector\DeviceDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    private UserRepository $userRepository;

    private DeviceRepository $devicesRepository;

    public function __construct(UserRepository $userRepository, DeviceRepository $devicesRepository)
    {
        $this->userRepository = $userRepository;
        $this->devicesRepository = $devicesRepository;
    }

    public function show(Request $request)
    {
        if (! Auth::user()->hasPermission('view.users')) {
            abort(403);
        }

        $users = $this->userRepository->FindAll('', 1, 50);

        $showingText = "Showing {$users->firstItem()} to {$users->lastItem()} of {$users->total()} entries";

        $currentPage = $users->currentPage();

        $roles = config('roles.models.role')::all();

        return view('admin.users.index', [
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

        $users = $this->userRepository->FindAll($request->search, $request->p, 50);

        $showingText = "Showing {$users->firstItem()} to {$users->lastItem()} of {$users->total()} entries";

        $currentPage = $users->currentPage();

        return view('admin.users.list', [
            'users' => $users,
            'bottomText' => $showingText,
            'links' => Nav::getNavLinks($currentPage, $users->lastPage()),
            'page' => $currentPage,
            'search' => $request->search,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $user = $this->userRepository->Find($id);

        if (! $user) {
            abort(404);
        }

        $devices = $this->devicesRepository->FindAllUserDevices($user->id)->reverse()->toArray();

        $currentSessionID = $request->session()->getId();

        $roles = config('roles.models.role')::all();

        return response()->view('admin.users.edit_public_profile', [
            'user' => $user,
            'sessionID' => $currentSessionID,
            'roles' => $roles,
        ]);
    }

    public function showSecurity(Request $request, $id)
    {
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

        return response()->view('admin.users.edit_security', [
            'user' => $user,
            'devices' => $deviceArrays,
            'sessionID' => $currentSessionID,
        ]);
    }

    public function updateSecurity(Request $request, $id)
    {
        if (! Auth::user()->hasPermission('edit.users')) {
            abort(403);
        }

        $user = $this->userRepository->Find($id);

        if (! $user) {
            abort(404);
        }

        $validationRules = [
            'password' => 'required|string|min:8|max:20|regex:/^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z\d]*$/',
        ];

        $request->validate($validationRules);

        $updateData['password'] = $request->password;

        $this->userRepository->Update($user->id, $updateData);

        session()->flash('status', 'Password has been successfully updated.');

        return back();
    }

    public function logoutFromDevice(Request $request, $id, $device_id)
    {
        if (! Auth::user()->hasPermission('edit.users')) {
            abort(403);
        }

        $this->devicesRepository->LogoutUserDevice($id, $device_id);

        return back();
    }

    public function destroyTwoFactorAuth(Request $request, $userId)
    {
        if (!Auth::user()->hasPermission('edit.users')) {
            abort(403);
        }

        $user = User::find($userId);

        if (!$user) {
            abort(404, 'User not found');
        }

        // Assuming the 2FA details are stored in a method like `disableTwoFactorAuth()`
        // on the User model. You would need to implement this logic based on your 2FA setup.
        $user->two_factor_confirmed = null;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_recovery_codes = null;

        $user->update();

        session()->flash('status', 'Two-factor authentication disabled for ' . $user->name);

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
            'role' => 'required|integer|min:0',
            'bio' => 'sometimes|string|max:1000|nullable',
            'website_url' => 'sometimes|url|max:500|nullable',
            'email-verification' => 'required|string|in:Unverified,Verified',
            'password' => 'required|string|min:8|max:20|regex:/^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z\d]*$/',
        ]);

        $user = User::query()->create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'bio' => $request->input('bio'),
            'website_url' => $request->input('website_url'),
            'email_verified_at' => $request->input('email-verification') === 'Verified' ? now() : null,
            'password' => Hash::make($request->input('password')),
            'remember_token' => Str::random(60),
        ]);

        $user->attachRole((int) $request->input('role'));

        return redirect('/admin/users/'.$user->id);
    }

    public function update(Request $request, $id)
    {
        if (! Auth::user()->hasPermission('edit.users')) {
            abort(403);
        }

        $user = $this->userRepository->find($id);

        if (! $user) {
            abort(404);
        }

        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'bio' => 'sometimes|string|max:1000|nullable',
            'website_url' => 'sometimes|url|max:500|nullable'
        ];

        // Add role and bio validation rules if not the default user
        if ($id != 1) {
            $validationRules['role'] = 'required|int';
            $validationRules['email-verification'] = 'required|string|in:Unverified,Verified';
        }

        $request->validate($validationRules);

        $updateData = $request->except(['password']);

        // Update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = $request->password;
        }

        // Exclude role and bio from update if it's the default user
        if ($id == 1) {
            unset($updateData['role'], $updateData['email-verification']);
        }

        $this->userRepository->Update($user->id, $updateData);

        session()->flash('status', 'Public profile has been successfully updated.');

        return back();
    }

    public function destroy(Request $request, $id)
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
