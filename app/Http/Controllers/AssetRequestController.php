<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetRequest;
use App\Models\AssetAssignment;
use App\Models\AssetLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('AssetRequests/Index', [
            'requests' => $requests,
            'filters'  => $request->only(['status']),
            'isAdmin'  => $user->hasRole('admin'),
        ]);
    }

    /**
     * Employee: show form to request an asset.
     */
    public function create(Request $request)
    {
        // Only available assets can be requested
        $assets = Asset::where('status', 'available')->with('category')->get();

        return Inertia::render('AssetRequests/Create', [
            'assets' => $assets,
        ]);
    }

    /**
     * Employee: submit an asset request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
        ]);

        $asset = Asset::findOrFail($validated['asset_id']);

        // Edge case: asset no longer available
        if ($asset->status !== 'available') {
            return back()->with('error', 'This asset is no longer available.');
        }

        // Prevent duplicate pending request for same asset
        $duplicate = AssetRequest::where('user_id', Auth::id())
            ->where('asset_id', $asset->id)
            ->where('status', 'pending')
            ->exists();

        if ($duplicate) {
            return back()->with('error', 'You already have a pending request for this asset.');
        }

        AssetRequest::create([
            'user_id'      => Auth::id(),
            'asset_id'     => $asset->id,
            'status'       => 'pending',
            'requested_at' => now(),
        ]);

        return redirect()->route('asset-requests.index')->with('success', 'Request submitted successfully.');
    }

    /**
     * Admin: approve a request → create assignment, update statuses.
     */
    public function approve(AssetRequest $assetRequest)
    {
        if ($assetRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        $asset = $assetRequest->asset;

        if ($asset->status !== 'available') {
            return back()->with('error', 'Asset is no longer available.');
        }

        // Update request
        $assetRequest->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Create assignment
        AssetAssignment::create([
            'asset_id'      => $asset->id,
            'user_id'       => $assetRequest->user_id,
            'assigned_date' => now(),
            'status'        => 'assigned',
        ]);

        // Update asset status
        $asset->update(['status' => 'assigned']);

        // Log
        AssetLog::create([
            'asset_id' => $asset->id,
            'user_id'  => Auth::id(),
            'action'   => 'approved',
            'remarks'  => 'Request approved and asset assigned to user #' . $assetRequest->user_id,
        ]);

        return back()->with('success', 'Request approved and asset assigned.');
    }

    /**
     * Admin: reject a request.
     */
    public function reject(AssetRequest $assetRequest)
    {
        if ($assetRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be rejected.');
        }

        $assetRequest->update([
            'status'      => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        AssetLog::create([
            'asset_id' => $assetRequest->asset_id,
            'user_id'  => Auth::id(),
            'action'   => 'rejected',
            'remarks'  => 'Request rejected by admin',
        ]);

        return back()->with('success', 'Request rejected.');
    }
}