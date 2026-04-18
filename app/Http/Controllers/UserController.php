<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['department', 'roles']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', (int) $request->status);
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $departments = Department::all();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'departments' => $departments,
            'filters' => $request->only(['search', 'status', 'department']),
        ]);
    }

    public function create()
    {
        $departments = Department::all();

        return Inertia::render('Users/Create', [
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            // 'password' => 'required|string|min:8|confirmed',
            'department_id' => 'nullable|exists:departments,id',
            'role' => 'required|in:admin,employee',
            'status' => 'required',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(Str::random(8)),
            'department_id' => $validated['department_id'] ?? null,
            'status' => $validated['status'],
        ]);

        $user->assignRole($validated['role']);

        // Email to the user ..... for password reset link
        Password::broker()->sendResetLink([
            'email' => $user->email,
        ]);
        flash_success('User created');

        return redirect()->route('users.index');
    }

    public function edit(User $user)
    {
        $departments = Department::all();
        $roles = Role::all();

        return Inertia::render('Users/Edit', [
            'user' => $user->load(['department', 'roles']),
            'departments' => $departments,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'department_id' => 'nullable|exists:departments,id',
            'role' => 'required|in:admin,employee',
            'status' => 'required|integer',
            'password' => 'nullable|string|min:8|confirmed',
            'img_path' => 'sometimes|nullable|image|max:2048',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'department_id' => $validated['department_id'] ?? null,
            'status' => $validated['status'],
        ]);

        if (! empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->syncRoles([$validated['role']]);

        flash_success('User updated successfully.');

        return redirect()->route('users.index');
    }

    public function show(User $user)
    {
        $user->load([
            'department',
            'roles',
            'assignments' => fn ($q) => $q->with('asset.category')->withTrashed()->latest(),
            'requests' => fn ($q) => $q->with('asset')->withTrashed()->latest()->take(20),
        ]);

        return Inertia::render('Users/Show', [
            'user' => $user,
        ]);
    }

    /**
     * Fix: guard against admin deleting their own account.
     * Without this, the admin is logged out mid-session and the
     * subsequent redirect fails with an unauthenticated error.
     */
    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            flash_error('You cannot delete your own account.');

            return back();
        }

        $user->delete();

        flash_success('User deleted successfully.');

        return redirect()->route('users.index');
    }
}
