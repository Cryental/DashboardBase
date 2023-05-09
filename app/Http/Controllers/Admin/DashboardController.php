<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
    }

    public function show(Request $request)
    {
        return view('admin.dashboard', [
            'userCount' => User::count(),
        ]);
    }

    public function getUsersChartData()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateString();

        $users = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereDate('created_at', '>=', $sevenDaysAgo)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($users);
    }
}
