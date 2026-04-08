<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('users')->latest()->paginate(20);

        return Inertia::render('Departments/Index', [
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:departments,name|max:100',
        ]);

        Department::create(['name' => $request->name]);

        flash_success('Department created.');
        return back();
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:departments,name,' . $department->id,
        ]);

        $department->update(['name' => $request->name]);

        flash_success('Department updated.');
        return back();
    }

    public function destroy(Department $department)
    {
        if ($department->users()->count() > 0) {
            flash_error('Cannot delete a department that has employees. Reassign them first.');
            return back();
        }

        $department->delete();

        flash_success('Department deleted.');
        return back();
    }
}