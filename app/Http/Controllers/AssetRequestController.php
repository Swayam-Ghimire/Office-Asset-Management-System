<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetLog;
use App\Models\AssetRequest;
use App\Notifications\Employee\RequestApprovedNotification;
use App\Notifications\Employee\RequestRejectedNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AssetRequestController extends Controller
{
    /**
     * List requests — Admin sees all, Employee sees own.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = AssetRequest::with(['asset.category', 'user']);

        if (! $user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('AssetRequests/Index', [
            'requests' => $requests,
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Employee: show form to request an asset.
     */
    public function create(Request $request)
    {
        $assets = Asset::where('status', 'available')
            ->with('category')
            ->get();

        $selected = $request->filled('asset_id') ? Asset::with('category')->find($request->asset_id) : null;


        return Inertia::render('AssetRequests/Create', [
            'assets' => $assets,
            'selected' => $selected,
        ]);
    }

    /**
     * Employee: submit an asset request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'reason' => 'nullable|string|max:255',
        ]);
        // Prevent duplicate pending request for same asset
        $duplicate = AssetRequest::where('asset_id', $validated['asset_id'])
            ->where('status', 'pending')
            ->exists();
        if ($duplicate) {
            flash_error("This asset is already requested by someone. Please choose another or wait for it to become available.");
            return back();
        }
        try{
            return DB::transaction(function () use ($validated) {
                $updated = Asset::where('id', $validated['asset_id'])->where('status', 'available')->update(['status' => 'not_available']);

                if (!$updated) {
                    throw new Exception('This asset was just taken by someone else.');
                }
            // create a new request record
                AssetRequest::create([
                    'user_id' => Auth::id(),
                    'asset_id' => $validated['asset_id'],
                    'reason' => $validated['reason'],
                    'status' => 'pending',
                    'requested_at' => now(),
                ]);
                flash_success('Request submitted successfully.');
                return redirect()->route('asset-requests.index');
            });
        }
        catch(Exception $e) {
            flash_error($e->getMessage());
            return back();
        }
    }

    /**
     * Admin: approve a request → create assignment, update statuses.
     */
    public function approve(AssetRequest $assetRequest)
    {
        if ($assetRequest->status !== 'pending') {
            flash_error("Only pending requests can be approved.");
            return redirect()->route('asset-requests.index');
        }

        $asset = $assetRequest->asset;

        if (!in_array($asset->status, ['available', 'not_available'], true)) {
            flash_error("Asset is no longer assignable.");
            return redirect()->route('asset-requests.index');
        }

        // Update request
        $assetRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        
        // Create assignment
        AssetAssignment::create([
            'asset_id' => $asset->id,
            'user_id' => $assetRequest->user_id,
            'assigned_date' => now(),
            'status' => 'assigned',
            ]);
            
            //sends notification
            $assetRequest->user->notify(new RequestApprovedNotification($assetRequest));
        // Log
        AssetLog::create([
            'asset_id' => $asset->id,
            'user_id' => Auth::id(),
            'action' => 'approved',
            'remarks' => 'Request approved and asset assigned to user '.$assetRequest->user->name,
        ]);
        flash_success("Request approved and asset assigned.");
        return back();
    }

    /**
     * Admin: reject a request.
     */
    public function reject(AssetRequest $assetRequest)
    {
        if ($assetRequest->status !== 'pending') {
            flash_error("Only pending requests can be rejected.");
            return back();
        }

        $asset = $assetRequest->asset;

        $assetRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        if ($asset && $asset->status === 'not_available') {
            $asset->update(['status' => 'available']);
        }

        // Rejection notification
        $assetRequest->user->notify(new RequestRejectedNotification($assetRequest));

        AssetLog::create([
            'asset_id' => $assetRequest->asset_id,
            'user_id' => Auth::id(),
            'action' => 'rejected',
            'remarks' => 'Request rejected by ' . Auth::user()->name,
        ]);
        flash_success("Request rejected.");
        return back();
    }
}
