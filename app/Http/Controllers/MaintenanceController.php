<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\AssetMaintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MaintenanceController extends Controller
{
    /**
     * Admin  → all records (paginated, filterable by status).
     * Employee → only their own reports.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = AssetMaintenance::with(['asset.category', 'reporter']);

        if (! $user->hasRole('admin')) {
            // Employee sees only records they reported
            $query->where('reported_by', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $maintenances = $query->latest('reported_at')->paginate(10)->withQueryString();

        return Inertia::render('Maintenance/Index', [
            'maintenances' => $maintenances,
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Single maintenance record detail.
     * Admin can always view. Employee can only view their own.
     */
    // public function show(Request $request, AssetMaintenance $maintenance)
    // {
    //     $user = $request->user();

    //     if (! $user->hasRole('admin') && $maintenance->reported_by !== $user->id) {
    //         abort(403);
    //     }

    //     $maintenance->load(['asset.category', 'reporter']);

    //     return Inertia::render('Maintenance/Show', [
    //         'maintenance' => $maintenance,
    //         'isAdmin'     => $user->hasRole('admin'),
    //     ]);
    // }

    /**
     * Employee reports an issue on an asset they hold.
     * Status: reported → asset: under_maintenance.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'description' => 'required|string|max:1000',
        ]);

        $asset = Asset::findOrFail($validated['asset_id']);

        // Prevent duplicate open reports for the same asset
        $alreadyOpen = AssetMaintenance::where('asset_id', $asset->id)
            ->whereIn('status', ['reported', 'in_progress'])
            ->exists();

        if ($alreadyOpen) {
            flash_error('This asset already has an open maintenance report.');

            return back();
        }

        AssetMaintenance::create([
            'asset_id' => $asset->id,
            'reported_by' => Auth::id(),
            'description' => $validated['description'],
            'status' => 'reported',
            'reported_at' => now(),
        ]);

        // Asset is no longer available while under maintenance
        $asset->update(['status' => 'under_maintenance']);

        AssetLog::create([
            'asset_id' => $asset->id,
            'user_id' => Auth::id(),
            'action' => 'maintenance_reported',
            'remarks' => 'Issue reported by '.Auth::user()->name.': '.$validated['description'],
        ]);

        // Notify admin

        flash_success('Issue reported. The asset has been flagged for maintenance.');

        return back();
    }

    /**
     * Admin acknowledges the report — moves to in_progress.
     * Triggered when admin is actively working on the asset.
     * Asset stays under_maintenance.
     */
    public function markInProgress(Request $request, $id)
    {
        $maintenance = AssetMaintenance::findOrFail($id);

        if ($maintenance->status !== 'reported') {
            flash_error('Only reported issues can be moved to in progress.');

            return back();
        }

        $maintenance->update(['status' => 'in_progress']);

        AssetLog::create([
            'asset_id' => $maintenance->asset_id,
            'user_id' => Auth::id(),
            'action' => 'maintenance_in_progress',
            'remarks' => 'Marked as in progress by '.Auth::user()->name,
        ]);

        flash_success('Maintenance marked as in progress.');

        return back();
    }

    /**
     * Admin resolves the maintenance.
     * Status: completed → asset: available (condition updated).
     */
    public function resolve(Request $request, $id)
    {
        $maintenance = AssetMaintenance::with('asset')->findOrFail($id);

        if ($maintenance->status === 'completed') {
            flash_error('This maintenance record is already resolved.');

            return back();
        }

        $validated = $request->validate([
            'resolution_note' => 'nullable|string|max:500',
            'new_condition' => 'required|in:new,good,damaged',
        ]);

        $maintenance->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolution_note' => $validated['resolution_note'] ?? null,
        ]);

        // Asset returns to available inventory with updated condition
        $maintenance->asset->update([
            'status' => 'available',
            'condition' => $validated['new_condition'],
        ]);

        $note = $validated['resolution_note']
            ? '. Note: '.$validated['resolution_note']
            : '';

        AssetLog::create([
            'asset_id' => $maintenance->asset_id,
            'user_id' => Auth::id(),
            'action' => 'maintenance_resolved',
            'remarks' => 'Resolved by '.Auth::user()->name.$note,
        ]);

        flash_success('Maintenance resolved. Asset is now available.');

        return back();
    }
}
