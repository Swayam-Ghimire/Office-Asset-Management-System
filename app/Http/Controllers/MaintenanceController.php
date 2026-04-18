<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetLog;
use App\Models\AssetMaintenance;
use App\Notifications\Employee\ReturnRequestedNotification;
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
        $query = AssetMaintenance::with(['asset.category', 'reporter.assignments']);

        if (! $user->hasRole('admin')) {
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
     * Employee reports an issue on an asset they hold.
     * Fix: set asset status to under_maintenance (not not_available).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'description' => 'required|string|max:1000',
        ]);

        $asset = Asset::findOrFail($validated['asset_id']);

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

        // Fix: correct status — under_maintenance means it is being serviced,
        // not_available means pending a request. These are different concepts.
        $asset->update(['status' => 'under_maintenance']);

        AssetLog::create([
            'asset_id' => $asset->id,
            'user_id' => Auth::id(),
            'action' => 'maintenance_reported',
            'remarks' => 'Issue reported by '.Auth::user()->name.': '.$validated['description'],
        ]);

        // TODO: notify admins here (phase 3)

        flash_success('Issue reported. The asset has been flagged for maintenance.');

        return back();
    }

    public function markInProgress(Request $request, $id)
    {
        $maintenance = AssetMaintenance::with('asset')->findOrFail($id);

        if ($maintenance->status !== 'reported') {
            flash_error('Only reported issues can be moved to in progress.');

            return back();
        }

        // Guard: asset must NOT still be assigned — employee must have returned it first.
        $stillAssigned = AssetAssignment::where('asset_id', $maintenance->asset_id)
            ->where('status', 'assigned')
            ->exists();

        if ($stillAssigned) {
            flash_error('The asset is still assigned to an employee. Ask them to return it before starting maintenance.');

            return back();
        }

        $maintenance->update(['status' => 'in_progress']);

        $maintenance->asset()->update([
            'status' => 'under_maintenance',
            'condition' => 'damaged',
        ]);

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
     * Status: resolved → asset: available (condition updated).
     */
    public function resolve(Request $request, $id)
    {
        $maintenance = AssetMaintenance::with('asset')->findOrFail($id);

        if ($maintenance->status === 'resolved') {
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

        $maintenance->asset?->update([
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

        // // notify reporter

        flash_success('Maintenance resolved. Asset is now available.');

        return back();
    }

    /**
     * Admin asks the assigned employee to return the asset for servicing.
     * Sends a DB notification + queued email via ReturnRequestedNotification.
     */
    public function requestReturn(AssetMaintenance $maintenance, Request $request)
    {
        if ($maintenance->status === 'resolved') {
            flash_error('This maintenance record is already resolved.');

            return back();
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $maintenance->load(['asset.assignments' => function ($q) {
            $q->where('status', 'assigned')->with('user')->latest();
        }, 'asset.category']);

        $activeAssignment = $maintenance->asset->assignments->first();

        if (! $activeAssignment) {
            flash_error('This asset does not have an active assignment. No one to notify.');

            return back();
        }

        $employee = $activeAssignment->user;

        if (! $employee) {
            flash_error('Could not find the assigned employee.');

            return back();
        }

        $employee->notify(
            new ReturnRequestedNotification($maintenance, $validated['reason'] ?? null)
        );

        AssetLog::create([
            'asset_id' => $maintenance->asset_id,
            'user_id' => Auth::id(),
            'action' => 'return_requested',
            'remarks' => 'Return requested by '.Auth::user()->name
                .' from '.$employee->name
                .($validated['reason'] ? '. Note: '.$validated['reason'] : ''),
        ]);

        flash_success('Return request sent to '.$employee->name.' via notification and email.');

        return back();
    }
}
