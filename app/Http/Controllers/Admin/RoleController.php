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

class RoleController extends Controller
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

        $users = $this->userRepository->FindAll('', 1, 50);

        $showingText = "Showing {$users->firstItem()} to {$users->lastItem()} of {$users->total()} entries";

        $currentPage = $users->currentPage();

        return view('admin.users', [
            'users' => $users,
            'bottomText' => $showingText,
            'links' => Nav::getNavLinks($currentPage, $users->lastPage()),
            'page' => $currentPage,
            'search' => $request->search,
        ]);
    }
}
