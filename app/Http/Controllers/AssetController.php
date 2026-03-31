<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Category;
use App\Models\AssetLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with('category');

        // Filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('asset_code', 'like', '%' . $request->search . '%')
                  ->orWhere('brand', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        $assets = $query->latest()->paginate(10)->withQueryString();

        $categories = Category::all();

        $stats = [
            'total'       => Asset::count(),
            'available'   => Asset::where('status', 'available')->count(),
            'assigned'    => Asset::where('status', 'assigned')->count(),
            'assigned_to_you' => $request->user()->assignments()->with('asset')->get()->pluck('asset'), 
            'maintenance' => Asset::where('status', 'under_maintenance')->count(),
        ];

        return Inertia::render('Home', [
            'assets'     => $assets,
            'categories' => $categories,
            'stats'      => $stats,
            'filters'    => $request->only(['search', 'category', 'status', 'condition']),
        ]);
    }

    public function create()
    {
        $categories = Category::all();

        return Inertia::render('Assets/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'model_name'          => 'required|string|unique:assets,model_name|max:255',
            'category_id'   => 'required|exists:categories,id',
            'brand'         => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'condition'     => 'required|in:new,good,damaged',
            'status'        => 'required|in:available,assigned,under_maintenance',
            'img_path'      => 'nullable|image|max:10000',
        ]);

        if ($request->hasFile('img_path')) {
            $validated['img_path'] = $request->file('img_path')->store('assets', 'public');
        }

        $asset = Asset::create($validated);

        // Log creation
        AssetLog::create([
            'asset_id' => $asset->id,
            'user_id'  => Auth::id(),
            'action'   => 'created',
            'remarks'  => 'Asset created by admin',
        ]);

        return redirect()->route('assets.index')->with('success', 'Asset created successfully.');
    }

    public function show(Asset $asset)
    {
        $asset->load([
            'category',
            'assignments.user',
            'requests.user',
            'maintenance',
        ]);

        return Inertia::render('Assets/Show', [
            'asset'   => $asset,
            'isAdmin' => Auth::user()->hasRole('admin'),
        ]);
    }

    public function edit(Asset $asset)
    {
        $categories = Category::all();

        return Inertia::render('Assets/Edit', [
            'asset'      => $asset->load('category'),
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'asset_code'    => 'required|string|unique:assets,asset_code,' . $asset->id . '|max:100',
            'category_id'   => 'required|exists:categories,id',
            'brand'         => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'condition'     => 'required|in:new,good,damaged',
            'status'        => 'required|in:available,assigned,under_maintenance',
            'img_path'      => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('img_path')) {
            $validated['img_path'] = $request->file('img_path')->store('assets', 'public');
        }

        $asset->update($validated);

        AssetLog::create([
            'asset_id' => $asset->id,
            'user_id'  => Auth::id(),
            'action'   => 'updated',
            'remarks'  => 'Asset updated by admin',
        ]);

        return redirect()->route('assets.index')->with('success', 'Asset updated successfully.');
    }

    public function destroy(Asset $asset)
    {
        // Prevent deletion if currently assigned
        if ($asset->status === 'assigned') {
            return back()->with('error', 'Cannot delete an asset that is currently assigned.');
        }

        AssetLog::create([
            'asset_id' => $asset->id,
            'user_id'  => Auth::id(),
            'action'   => 'deleted',
            'remarks'  => 'Asset deleted by admin',
        ]);

        $asset->delete();

        return redirect()->route('assets.index')->with('success', 'Asset deleted successfully.');
    }
}