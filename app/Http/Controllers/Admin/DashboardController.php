<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;

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
        $users = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($users);
    }
}
