<?php

namespace App\Http\Controllers\Admin;

use App\Repositories\UserRepository;
use Illuminate\Http\Request;

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

    public function getUserStats(Request $request)
    {
        $stats = $this->userRepository->GetUsersStatistics($request->input('period', 7));

        return response()->json($stats);
    }
}
