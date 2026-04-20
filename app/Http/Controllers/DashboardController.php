<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetMaintenance;
use App\Models\AssetRequest;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function admin(Request $request)
    {
        //  Stats tiles
        $totalAssets = Asset::count();

        $stats = [
            'total_assets' => $totalAssets,
            'available' => Asset::where('status', 'available')->count(),
            'not_available' => Asset::where('status', 'not_available')->count(),
            'assigned' => AssetAssignment::where('status', 'assigned')->count(),
            'under_maintenance' => AssetMaintenance::whereIn('status', ['reported', 'in_progress'])->count(),
            'pending_requests' => AssetRequest::where('status', 'pending')->count(),
            'total_users' => User::count(),
        ];

        // Assets by category — bar chart
        $categoryRows = Category::withCount('assets')->get();
        $assetsByCategory = [
            'labels' => $categoryRows->pluck('name')->values()->all(),
            'datasets' => [[
                'label' => 'Assets',
                'data' => $categoryRows->pluck('assets_count')->values()->all(),
                'backgroundColor' => '#EF4444',
                'borderRadius' => 6,
                'borderSkipped' => false,
            ]],
        ];

        // Asset status breakdown — doughnut chart
        $assetStatusBreakdown = [
            'labels' => ['Available', 'Not Available', 'Under Maintenance'],
            'datasets' => [[
                'data' => [
                    $stats['available'],
                    $stats['not_available'],
                    $stats['under_maintenance'],
                ],
                'backgroundColor' => ['#10B981', '#3B82F6', '#F59E0B'],
                'borderWidth' => 0,
                'hoverOffset' => 6,
            ]],
        ];

        // Request trend — last 6 months (line chart)
        // Counts approved + rejected + pending per month
        $trendRows = AssetRequest::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw('COUNT(*) as total')
        )
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Fill in any missing months so the chart always has 6 points
        $months = [];
        $trendCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $months[] = now()->subMonths($i)->format('M Y');
            $trendCounts[] = $trendRows->firstWhere('month', $key)?->total ?? 0;
        }

        $requestTrend = [
            'labels' => $months,
            'datasets' => [[
                'label' => 'Requests',
                'data' => $trendCounts,
                'borderColor' => '#EF4444',
                'backgroundColor' => 'rgba(239,68,68,0.08)',
                'borderWidth' => 2,
                'pointRadius' => 4,
                'pointBackgroundColor' => '#EF4444',
                'fill' => true,
                'tension' => 0.4,
            ]],
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'pendingRequests' => AssetRequest::with(['user', 'asset'])
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get(),
            'recentAssignments' => AssetAssignment::with(['user', 'asset'])
                ->latest()
                ->take(8)
                ->get(),
            'assetsByCategory' => $assetsByCategory,
            'assetStatusBreakdown' => $assetStatusBreakdown,
            'requestTrend' => $requestTrend,
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
            'myAssignments' => AssetAssignment::with(['asset.category'])
                ->where('user_id', $user->id)
                ->where('status', 'assigned')
                ->get(),
            'myRequests' => AssetRequest::with('asset.category')
                ->where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}
