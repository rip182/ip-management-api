<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\InternetProtocolAddress;
use Carbon\Carbon;

class DashboardController extends Controller
{

    public function getStats()
    {
        $totalUsers = User::count();
        $totalIp = InternetProtocolAddress::count();
        $ipsAddedThisMonth = InternetProtocolAddress::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        return response()->json([
            'activeUsers' => $totalUsers,
            'ipsAddedThisMonth' => $ipsAddedThisMonth,
            'totalIp' => $totalIp
        ]);
    }
}
