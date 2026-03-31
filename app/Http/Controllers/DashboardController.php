<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetMaintenance;
use App\Models\AssetRequest;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function admin(Request $request)
    {
        return Inertia::render('Dashboard', ['stats' => [
            'total_assets' => Asset::count(),
            'available' => Asset::where('status', 'available')->count(),
            'assigned' => Asset::where('status', 'assigned')->count(),
            'maintenance' => AssetMaintenance::where('status', 'under_maintenance')->count(),
            'pending_requests' => AssetRequest::where('status', 'pending')->count(),
            'total_users' => User::count(),
        ],
            // Get 5 most recent pending requests with relationships
            'pendingRequests' => AssetRequest::with(['user', 'asset'])
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get(),
            // Get 10 most recent assignments
            'recentAssignments' => AssetAssignment::with(['user', 'asset'])
                ->latest()
                ->take(10)
                ->get(),
            // Count assets per category for the chart: ['Laptop' => 5, 'Monitor' => 3]
            'assetsByCategory' => Category::withCount('assets')
                ->pluck('assets_count', 'name'),
        ]);
    }

    public function employee(Request $request)
    {
        $user = $request->user();
        return Inertia::render('Employee/EDashboard', [
            'stats' => [
                'my_assets' => AssetAssignment::where('user_id', $user->id)->where('status', 'assigned')->count(),
                'pending_requests' => AssetRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
                'approved_requests' => AssetRequest::where('user_id', $user->id)->where('status', 'approved')->count(),
                'returned_assets' => AssetAssignment::where('user_id', $user->id)->where('status', 'returned')->count(),
            ],
            // The specific assets currently held by the user
            'myAssignments' => AssetAssignment::with(['asset.category'])
                ->where('user_id', $user->id)
                ->where('status', 'assigned')
                ->get(),
            // The user's recent request history
            'myRequests' => AssetRequest::with('asset')
                ->where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}
