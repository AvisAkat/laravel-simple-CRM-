<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

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
}
