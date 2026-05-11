<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        $data = [
            'pageTitle' => 'Dashboard',
        ];

        return view('dashboard.index', $data);
    }

    public function logoutHandler(Request $request)
    {
        $user_id = Auth::id(); 

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Activity::create([
            'type' => 'logout',
            'message' => 'User logged out',
            'icon' => 'fa-sign-out-alt',
            'user_id' => $user_id,
        ]);

        return redirect()->route('auth.loginForm')->with('notification', [
            'type' => 'success',
            'message' => 'You have been logged out successfully.',
        ]);
    }

    public function showCustomers()
    {
        $data = [
            'pageTitle' => 'Customers',
        ];

        return view('dashboard.customers', $data);
    }

    public function showLeads()
    {
        $data = [
            'pageTitle' => 'Leads',
        ];

        return view('dashboard.leads', $data);
    }

    public function showTasks()
    {
        $data = [
            'pageTitle' => 'Tasks and Follow-Ups',
        ];

        return view('dashboard.tasks', $data);
    }

    public function showProfile()
    {
        $data = [
            'pageTitle' => 'Profile',
        ];

        return view('dashboard.profile', $data);
    }

    public function showUsers()
    {
        $data = [
            'pageTitle' => 'Users',
        ];

        return view('dashboard.all_users', $data);
    }

    public function showAddUserForm()
    {
        $data = [
            'pageTitle' => 'Add User',
            'isEditUser' => false,
            'user' => null,
        ];

        return view('dashboard.add_user', $data);
    }

    public function addUser(Request $request)
    {
        $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'status' => 'nullable|in:active,inactive',
            'role' => 'required|in:admin,manager,agent',
        ], [
            'firstName.required' => 'Enter the user\'s first name',
            'lastName.required' => 'Enter the user\'s last name',
            'email.required' => 'Enter the user\'s email address',
            'email.email' => 'Enter a valid email address',
            'email.unique' => 'A user with that email address already exists',
            'role.required' => 'Select a role for the new user',
            'role.in' => 'Select a valid user role',
            'status.in' => 'Select a valid user status',
        ]);

        $default_password = $request->input('firstName').'.'.$request->input('lastName');

        $user = new User;
        $user->first_name = $request->input('firstName');
        $user->last_name = $request->input('lastName');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
        $user->role = $request->input('role');
        $user->password = Hash::make($default_password);
        $user->status = $request->input('status');
        $created = $user->save();

        if ($created) {
            Activity::create([
                'type' => 'user_created',
                'message' => 'New user registered: ' . $user->first_name . ' ' . $user->last_name,
                'icon' => 'fa-user-plus',
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('admin.allUsers')->with('notification', [
                'type' => 'success',
                'message' => 'New user has been registered successfully. The default password is "firstName.lastName".',
            ]);
        } else {
            return redirect()->back()->with('notification', [
                'type' => 'error',
                'message' => 'New user has not been registered. Please Try Again!',
            ]);
        }

    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);

        $data = [
            'pageTitle' => 'Edit User',
            'isEditUser' => true,
            'user' => $user,
        ];

        return view('dashboard.add_user', $data);

    }

    public function updateUser(Request $request)
    {
        $request->validate([
            'userId' => 'required|exists:users,id',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$request->input('userId'),
            'phone' => 'nullable|string|max:30',
            'status' => 'nullable|in:active,inactive',
            'role' => 'required|in:admin,manager,agent',
        ], [
            'firstName.required' => 'Enter the user\'s first name',
            'lastName.required' => 'Enter the user\'s last name',
            'email.required' => 'Enter the user\'s email address',
            'email.email' => 'Enter a valid email address',
            'email.unique' => 'A user with that email address already exists',
            'role.required' => 'Select a role for the user',
            'role.in' => 'Select a valid user role',
            'status.in' => 'Select a valid user status',
        ]);

        $user = User::findOrFail($request->input('userId'));
        $user->first_name = $request->input('firstName');
        $user->last_name = $request->input('lastName');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
        $user->role = $request->input('role');
        $user->status = $request->input('status');

        $updated = $user->save();

        if ($updated) {
            Activity::create([
                'type' => 'user_updated',
                'message' => 'User updated: ' . $user->first_name . ' ' . $user->last_name,
                'icon' => 'fa-user-edit',
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('admin.allUsers')->with('notification', [
                'type' => 'success',
                'message' => 'User details have been updated successfully.',
            ]);
        } else {
            return redirect()->back()->with('notification', [
                'type' => 'error',
                'message' => 'User details has not been updated. Please Try Again!',
            ]);
        }
    }
}
