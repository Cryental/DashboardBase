<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Nav;
use App\Models\User;
use App\Repositories\UserRepository;
use DeviceDetector\DeviceDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function show(Request $request)
    {
        if ($request->user()->cannot('viewAny', User::class)) {
            abort(403);
        }

        $users = $this->userRepository->FindAll($request->search, 1, 50);

        $showingText = "Showing {$users->firstItem()} to {$users->lastItem()} of {$users->total()} entries";

        $currentPage = $users->currentPage();

        return view('admin.users', [
            'users'      => $users,
            'bottomText' => $showingText,
            'links'      => Nav::getNavLinks($currentPage, $users->lastPage()),
            'page'       => $currentPage,
            'search'     => $request->search,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('update', User::class)) {
            abort(403);
        }

        $user = User::query()->find($id);

        if (! $user) {
            abort(404);
        }

        $devices = DB::table('sessions')
            ->where('user_id', $user->id)
            ->get()->reverse()->toArray();

        $currentSessionID = $request->session()->getId();

        $deviceArrays = [];

        foreach ($devices as $device) {
            $dd = new DeviceDetector($device->user_agent);
            $dd->parse();

            $device->agentPlatform = $dd->getOs('name').' '.$dd->getOs('version');
            $device->agentBrowser = $dd->getClient('name').' '.$dd->getClient('version');

            $device->deviceDetector = [
                'mobile' => $dd->isSmartphone() || $dd->isFeaturePhone() || $dd->isMobileApp(),
                'tablet' => $dd->isTablet() || $dd->isPhablet(),
                'pc'     => $dd->isDesktop(),
                'tv'     => $dd->isTV() || $dd->isSmartDisplay(),
                'camera' => $dd->isCamera(),
                'bot'    => $dd->isBot(),
            ];

            $deviceArrays[] = $device;
        }

        return response()->view('admin.user_edit', [
            'user'      => $user->toArray(),
            'devices'   => $deviceArrays,
            'sessionID' => $currentSessionID,
        ]);
    }

    public function logoutDevice(Request $request, $id, $device_id)
    {
        DB::table('sessions')
            ->where('id', $device_id)
            ->where('user_id', $id)->delete();

        return back();
    }

    public function store(Request $request)
    {
        if ($request->action == 'search') {
            $users = User::query();

            if ($request->search) {
                $users->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            }

            $users = $users->paginate(50, ['*'], 'p');

            if (count($users) == 0) {
                $showingText = 'Showing 0 to 0 of 0 entries';
            } else {
                $showingText = 'Showing '.$users->firstItem().' to '.$users->lastItem().' of '.$users->total().' entries';
            }

            return view('admin.users', [
                'users'      => $users,
                'bottomText' => $showingText,
                'links'      => Nav::getNavLinks($users->currentPage(), $users->lastPage()),
                'page'       => $users->currentPage(),
                'search'     => $request->search,
            ]);
        } elseif ($request->action == 'create') {
            if ($request->user()->cannot('create', User::class)) {
                abort(403);
            }

            $request->validate([
                'name'               => 'required|string|max:255',
                'email'              => 'required|string|email|max:255|unique:users',
                'role'               => 'required|string|in:Member,Admin',
                'bio'                => 'sometimes|string|max:1000|nullable',
                'website_url'        => 'sometimes|url|max:500|nullable',
                'email-verification' => 'required|string|in:Unverified,Verified',
                'password'           => 'required|string|min:8',
            ]);

            $user = User::query()->create([
                'name'              => $request->input('name'),
                'email'             => $request->input('email'),
                'role'              => $request->input('role'),
                'bio'               => $request->input('bio'),
                'website_url'       => $request->input('website_url'),
                'email_verified_at' => $request->input('email-verification') === 'Verified' ? now() : null,
                'password'          => Hash::make($request->input('password')),
                'remember_token'    => Str::random(60),
            ]);

            return redirect('/admin/users/'.$user->id);
        } else {
            return redirect('/admin/users');
        }
    }

    public function editSave(Request $request, $id)
    {
        if ($request->user()->cannot('update', User::class)) {
            abort(403);
        }

        $user = User::query()->find($id);

        if (! $user) {
            abort(404);
        }

        $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'role'               => 'required|string|in:Member,Admin',
            'bio'                => 'sometimes|string|max:1000|nullable',
            'website_url'        => 'sometimes|url|max:500|nullable',
            'email-verification' => 'required|string|in:Unverified,Verified',
            'password'           => 'sometimes|string|nullable',
        ]);

        if ($user->name != $request->name) {
            $user->name = $request->name;
        }

        if ($user->email != $request->email) {
            $user->email = $request->email;
        }

        if ($user->role != $request->role) {
            $user->role = $request->role;
        }

        if ($user->bio != $request->bio) {
            $user->bio = $request->bio;
        }

        if ($user->website_url != $request->website_url) {
            $user->website_url = $request->website_url;
        }

        if ($user->email_verified_at == null && $request->input('email-verification') === 'Verified') {
            $user->email_verified_at = now();
        } elseif ($user->email_verified_at != null && $request->input('email-verification') === 'Unverified') {
            $user->email_verified_at = null;
        }

        if (! empty($request->password)) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        return redirect('/admin/users/'.$user->id);
    }

    public function delete(Request $request, $id)
    {
        if ($request->user()->cannot('delete', User::class)) {
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
