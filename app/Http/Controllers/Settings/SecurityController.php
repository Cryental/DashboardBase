<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use DeviceDetector\DeviceDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SecurityController extends Controller
{
    public function show(Request $request)
    {
        $devices = DB::table('sessions')
            ->where('user_id', Auth::user()->getAuthIdentifier())
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

            $ipLocation = geoip($device->ip_address);

            $device->ipLocation = "{$ipLocation->city}, {$ipLocation->country}";

            $deviceArrays[] = $device;
        }

        return view('settings.security', ['devices' => $deviceArrays, 'sessionID' => $currentSessionID]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (empty($user->password) && $user->provider_id !== null) {
            $request->validate([
                'new_password'         => ['required', 'string', \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers()->mixedCase()],
                'confirm_new_password' => 'required|same:new_password',
            ]);
        } else {
            $request->validate([
                'password'             => 'required_without:password|string|min:8|current_password',
                'new_password'         => ['required', 'string', \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers()->mixedCase()],
                'confirm_new_password' => 'required|same:new_password',
            ]);
        }

        $user->forceFill([
            'password'       => Hash::make($request->new_password),
            'remember_token' => Str::random(60),
        ])->save();

        return redirect()->route('settings.security')->with('status', 'Your password is changed successfully.');
    }

    public function logoutDevice(Request $request, $device_id)
    {
        DB::table('sessions')
            ->where('id', $device_id)
            ->where('user_id', Auth::user()->getAuthIdentifier())->delete();

        return redirect()->route('settings.security');
    }
}
