<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function show(Request $request)
    {
        return view('admin.dashboard', [
            'userCount' => $this->userRepository->FindUsersCount(''),
        ]);
    }

    public function getUsersChartData()
    {
        $users = $this->userRepository->FindUsersCountCreatedAfterDate(7);

        return response()->json($users);
    }
}
