<?php

namespace App\Http\Controllers;

use App\Models\AssetMaintenance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MaintenanceController extends Controller
{
    public function index() {
        $maintenances = AssetMaintenance::with('asset')->latest()->paginate(10);
        return Inertia::render('Maintenance/Index', [
            'maintenances' => $maintenances
        ]);
        }

    public function show() {

    }

    public function resolve() {

    }
    public function create() {

    }

    public function store() {

    }
}
