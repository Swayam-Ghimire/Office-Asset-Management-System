<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AssetController extends Controller
{
    public function index(Request $request) {
        $assets = Asset::with('category')->paginate(10);
        return Inertia::render('Home', [
            'assets' => $assets,
            'isAdmin' => $request->user()->hasRole('admin')
        ]); 
    }

    public function create() {

    }

    public function store() {

    }

    public function show() {

    }

    public function edit() {


    }

    public function update() {


    }

    public function destroy() {

    }
}
