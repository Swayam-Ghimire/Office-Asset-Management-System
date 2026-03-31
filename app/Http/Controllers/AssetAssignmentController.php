<?php

namespace App\Http\Controllers;

use App\Models\AssetAssignment;
use App\Models\AssetLog;
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

        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assignments = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('AssetAssignments/Index', [
            'assignments' => $assignments,
            'filters'     => $request->only(['status']),
            'isAdmin'     => $user->hasRole('admin'),
        ]);
    }

    /**
     * Employee/Admin: return an assigned asset.
     */
    public function return(AssetAssignment $assetAssignment)
    {
        $user = Auth::user();

        // Employees can only return their own assets
        if (!$user->hasRole('admin') && $assetAssignment->user_id !== $user->id) {
            abort(403);
        }

        if ($assetAssignment->status !== 'assigned') {
            return back()->with('error', 'This asset has already been returned.');
        }

        $assetAssignment->update([
            'status'      => 'returned',
            'return_date' => now(),
        ]);

        // Make asset available again
        $assetAssignment->asset->update(['status' => 'available']);

        AssetLog::create([
            'asset_id' => $assetAssignment->asset_id,
            'user_id'  => Auth::id(),
            'action'   => 'returned',
            'remarks'  => 'Asset returned by user #' . $assetAssignment->user_id,
        ]);

        return back()->with('success', 'Asset returned successfully.');
    }
}