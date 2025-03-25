<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%");
        })
        ->with('roles')
        ->paginate(10)
        ->withQueryString();

        return inertia('Admin/AdminUsers', [
            'users' => $users,
            'roles' => Role::all(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'position' => 'required|string|max:255',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'position' => $request->position,
            'profile_image' => 'no-image',
            'password' => Hash::make($request->password),
        ]);

        $role = Role::findById($request->role);
        $user->assignRole($role);

        return redirect()->route('AdminUsers')->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'position' => 'required|string|max:255',
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'position' => $request->position,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        $role = Role::findById($request->role);
        $user->syncRoles($role);

        return redirect()->route('AdminUsers')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('AdminUsers')->with('error', 'Self-deletion is not allowed. To delete this account, another admin must log in and remove it.');
        }

        $user->delete();
        return redirect()->route('AdminUsers')->with('success', 'User deleted successfully.');
    }
}
