<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Use the User model to access ROLES
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // NOTE: We no longer need the private $staffRoles property,
    // as we use User::ROLES directly for consistency.

    /**
     * Display the Admin Dashboard with user management.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $data = [
            'flowCounts' => [], 
            'totalVisits' => User::whereDate('created_at', today())->count(), 
            'avgConsultTime' => '15.2', 
            'unpaidBills' => 42, 
            // Fetch staff users using the constant from the User model
            'staff' => User::whereIn('role', User::ROLES)
                                 ->orderBy('role')->paginate(10),
        ];

        return view('outpatient.dashboard', [
            'role' => 'admin', 
            'data' => $data,
        ]);
    }

    /**
     * Display a list of all users for management.
     *
     * @return \Illuminate\View\View
     */
    public function listUsers(Request $request)
    {
        $query = User::orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        if ($request->filled('role') && $request->role != 'all') {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(20);
        // Use the constant from the User model
        $roles = User::ROLES;

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function createUser()
    {
        // Use the constant from the User model
        $roles = User::ROLES;
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            // Validate against the roles constant
            'role' => ['required', 'string', Rule::in(User::ROLES)],
            'password' => 'required|min:8',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            // Since User model uses 'password' => 'hashed' casting, Hash::make is optional, 
            // but explicitly calling it here ensures creation is secure.
            'password' => Hash::make($request->password), 
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }


    /**
     * Show the form for editing the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        // Use the constant from the User model
        $roles = User::ROLES;
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            // Validate against the roles constant
            'role' => ['required', 'string', Rule::in(User::ROLES)],
        ]);

        $user->update($request->only(['name', 'email', 'role']));

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }
    
    /**
     * Update the password for the specified user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'password_new' => 'required|min:8|confirmed',
        ]);
        
        $user->password = Hash::make($request->password_new);
        $user->save();

        return redirect()->route('admin.users.edit', $user->id)->with('success', 'Password updated successfully.');
    }


    /**
     * Remove the specified user from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}