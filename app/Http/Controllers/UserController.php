<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['role', 'position']);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10);
        $roles = Role::all();
        $positions = Position::all();
        return view('users.index', compact('users', 'roles', 'positions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'nullable|exists:roles,id',
            'position_id' => 'nullable|exists:positions,id',
            'disk_quota_value' => 'nullable|numeric|min:0',
            'disk_quota_unit' => 'nullable|in:MB,GB,TB',
        ]);

        $role_id = $request->role_id;
        $disk_quota = null;
        if (auth()->user()->role->name !== 'root') {
            $memberRole = Role::where('name', 'member')->first();
            if ($memberRole) $role_id = $memberRole->id;
        } else {
            if ($request->filled('disk_quota_value')) {
                $val = $request->disk_quota_value;
                $unit = $request->disk_quota_unit ?? 'GB';
                $multiplier = 1024 * 1024 * 1024;
                if ($unit === 'MB') $multiplier = 1024 * 1024;
                if ($unit === 'TB') $multiplier = 1024 * 1024 * 1024 * 1024;
                
                $disk_quota = $val * $multiplier;
            }
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role_id,
            'position_id' => $request->position_id,
            'disk_quota' => $disk_quota,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'nullable|exists:roles,id',
            'position_id' => 'nullable|exists:positions,id',
            'disk_quota_value' => 'nullable|numeric|min:0',
            'disk_quota_unit' => 'nullable|in:MB,GB,TB',
        ]);

        if (auth()->user()->role->name !== 'root' && $user->role->name === 'root') {
            abort(403, 'Admins cannot modify Root users.');
        }

        $data = $request->only('name', 'email', 'position_id');
        if (auth()->user()->role->name === 'root') {
            if ($request->filled('role_id')) {
                $data['role_id'] = $request->role_id;
            }
            if ($request->filled('disk_quota_value')) {
                $val = $request->disk_quota_value;
                $unit = $request->disk_quota_unit ?? 'GB';
                $multiplier = 1024 * 1024 * 1024;
                if ($unit === 'MB') $multiplier = 1024 * 1024;
                if ($unit === 'TB') $multiplier = 1024 * 1024 * 1024 * 1024;
                $data['disk_quota'] = $val * $multiplier;
            } else {
                $data['disk_quota'] = null;
            }
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if (auth()->user()->role->name !== 'root' && $user->role->name === 'root') {
            abort(403, 'Admins cannot delete Root users.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function impersonate(User $user)
    {
        if (Auth::user()->role->name !== 'root') {
            abort(403, 'Only Root can impersonate users.');
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot impersonate yourself.');
        }

        session(['impersonator_id' => Auth::id()]);
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'You are now logged in as ' . $user->name);
    }

    public function stopImpersonating()
    {
        if (!session()->has('impersonator_id')) {
            return redirect()->route('dashboard');
        }

        $originalUserId = session('impersonator_id');
        Auth::loginUsingId($originalUserId);
        session()->forget('impersonator_id');

        return redirect()->route('users.index')->with('success', 'You are back to your original account.');
    }
}
