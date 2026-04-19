<?php

namespace App\Http\Controllers;

use App\Models\AssetAssignment;
use App\Models\AssetLog;
use App\Models\AssetMaintenance;
use App\Models\User;
use App\Notifications\Admin\ReturnedAssetNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AssetAssignmentController extends Controller
{
    /**
     * Admin: list all assignments. Employee: list own.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = AssetAssignment::with(['asset.category', 'user']);

        if (! $user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assignments = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('AssetAssignments/Index', [
            'assignments' => $assignments,
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Employee/Admin: return an assigned asset.
     */
    public function return(AssetAssignment $assetAssignment)
    {
        $user = Auth::user();
        // Employees can only return their own assets
        if (! $user->hasRole('admin') && $assetAssignment->user_id !== $user->id) {
            abort(403);
        }

        if ($assetAssignment->status !== 'assigned') {
            flash_error('This asset has already been returned.');

            return back();
        }

        $assetAssignment->update([
            'status' => 'returned',
            'return_date' => now(),
        ]);

        // Notify return asset to returning
        $assetAssignment->load(['reporter', 'asset']);
        User::role('admin')->each(
            fn ($admin) => $admin->notify(new ReturnedAssetNotification($assetAssignment))
        );

        // If there is one, keep it as under_maintenance so admin can service it.
        $hasOpenMaintenance = AssetMaintenance::where('asset_id', $assetAssignment->asset_id)
            ->whereIn('status', ['reported', 'in_progress'])
            ->exists();

        if ($hasOpenMaintenance) {
            // Asset stays under_maintenance — admin will resolve it and set available
            $assetAssignment->asset->update(['status' => 'under_maintenance']);
        } else {
            $assetAssignment->asset->update(['status' => 'available']);
        }

        AssetLog::create([
            'asset_id' => $assetAssignment->asset_id,
            'user_id' => Auth::id(),
            'action' => 'returned',
            'remarks' => 'Asset returned by user '.$user->name.($hasOpenMaintenance ? ' (open maintenance report - kept under maintenance)' : ''),
        ]);

        flash_success('Asset returned successfully.');

        return back();
    }
}
