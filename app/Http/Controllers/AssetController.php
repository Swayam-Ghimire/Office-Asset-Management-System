<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\AssetLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with('category');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('model_name', 'like', '%' . $request->search . '%')
                  ->orWhere('brand', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) $query->where('category_id', $request->category);
        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('condition'))$query->where('condition', $request->condition);

        $assets     = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::all();

        $stats = [
            'total'           => Asset::count(),
            'available'       => Asset::where('status', 'available')->count(),
            'assigned_to_you' => $request->user()->assignments()->where('status', 'assigned')->count(),
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
        return Inertia::render('Assets/Create', [
            'categories' => Category::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'model_name'    => 'required|string|unique:assets,model_name|max:255',
            'category_id'   => 'required|exists:categories,id',
            'brand'         => 'nullable|string|max:100',
            'purchase_date' => 'required|date',
            'condition'     => 'required|in:new,good,damaged',
            'status'        => 'required|string',
            'img_path'      => 'nullable|image|mimes:png,jpg,jpeg,webp|max:10000',
        ]);

        if ($request->hasFile('img_path')) {
            $validated['img_path'] = $request->file('img_path')->store('assets', 'public');
        }

        $asset = Asset::create($validated);

        AssetLog::create([
            'asset_id' => $asset->id,
            'user_id'  => Auth::id(),
            'action'   => 'created',
            'remarks'  => 'Created by ' . Auth::user()->name,
        ]);

        flash_success('Asset created successfully.');
        return redirect()->route('home');
    }

    public function show(Asset $asset)
    {
        $asset->load([
            'category',
            'assignments' => fn($q) => $q->with('user')->where('status', 'assigned')->withTrashed()->latest(),
            'requests'    => fn($q) => $q->with('user')->withTrashed()->latest(),
            'maintenance' => fn($q) => $q->with('reporter')->latest(),
            'logs'        => fn($q) => $q->with('user')->latest()->take(30),
        ]);

        return Inertia::render('Assets/Show', [
            'asset' => $asset,
        ]);
    }

    public function edit(Asset $asset)
    {
        return Inertia::render('Assets/Edit', [
            'asset'      => $asset->load('category'),
            'categories' => Category::all(),
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'model_name'    => 'sometimes|required|string|max:255|unique:assets,model_name,' . $asset->id,
            'category_id'   => 'sometimes|required|exists:categories,id',
            'brand'         => 'sometimes|nullable|string|max:100',
            'purchase_date' => 'sometimes|nullable|date',
            'condition'     => 'sometimes|required|in:new,good,damaged',
            'status'        => 'sometimes|required|in:available,not_available,under_maintenance',
            'img_path'      => 'nullable|image|mimes:png,jpg,jpeg,webp|max:10000',
        ]);

        if ($request->hasFile('img_path')) {
            if($request->user()->img_path){
                Storage::disk('public')->delete('assets/' . $request->user()->img_path);
            }
            $validated['img_path'] = $request->file('img_path')->store('assets', 'public');
        }

        $asset->update($validated);

        AssetLog::create([
            'asset_id' => $asset->id,
            'user_id'  => Auth::id(),
            'action'   => 'updated',
            'remarks'  => 'Updated by ' . Auth::user()->name,
        ]);

        flash_success('Asset updated successfully.');
        return redirect()->route('home');
    }

    public function destroy(Asset $asset)
    {
        if ($asset->status === 'not_available') {
            flash_error('Cannot delete an asset that is currently requested or assigned.');
            return back();
        }

        AssetLog::create([
            'asset_id' => $asset->id,
            'user_id'  => Auth::id(),
            'action'   => 'deleted',
            'remarks'  => 'Soft-deleted by ' . Auth::user()->name,
        ]);

        $asset->delete();

        flash_success('Asset moved to trash. It can be restored.');
        return redirect()->route('home');
    }

    // Trash 

    public function trash()
    {
        $assets = Asset::onlyTrashed()->with('category')->latest('deleted_at')->paginate(10);

        return Inertia::render('Assets/Trash', [
            'assets' => $assets,
        ]);
    }

    public function restore($id)
    {
        $asset = Asset::onlyTrashed()->findOrFail($id);
        $asset->restore();

        AssetLog::create([
            'asset_id' => $asset->id,
            'user_id'  => Auth::id(),
            'action'   => 'restored',
            'remarks'  => 'Restored from trash by ' . Auth::user()->name,
        ]);

        flash_success('Asset restored successfully.');
        return back();
    }

    public function forceDelete($id)
    {
        $asset = Asset::onlyTrashed()->findOrFail($id);
        $asset->forceDelete();

        flash_success('Asset permanently deleted.');
        return back();
    }
}