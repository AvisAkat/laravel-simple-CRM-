<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function showLoginForm()
    {
        $data = [
            'pageTitle' => 'Login',
        ];

        return view('auth.login', $data);
    }

    public function loginHandler(Request $request)
    {

            $request->validate([
                'loginEmail' => 'required|email|exists:users,email',
                'loginPassword' => 'required',
            ], [
                'loginEmail.required' => 'Enter your email',
                'loginEmail.email' => 'Invalid email address',
                'loginEmail.exists' => 'No account found for this email',
                'loginPassword.required' => 'Enter your password',
            ]);

        $creds = [
            'email' => $request->loginEmail,
            'password' => $request->loginPassword,
        ];

        if (Auth::attempt($creds)) {
            // Check if account is inactive mode
            if (auth()->user()->status == 'inactive') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('auth.loginForm')->with('fail', 'Your account is currently inactive. Please, contact support at (support@larablog.test) for further assistance.');
            }

            // check if the account is in Pending mode
            if (auth()->user()->status == 'pending') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('auth.loginForm')->with('fail', 'Your account is currently pending approval. Please, check your email for futher instructions or contact support at (support@larablog.test) for assistance.');
            }

            // redirect the user to dashboard
            return redirect()->route('admin.dashboard')->with('notification', [
                'type' => 'success',
                'message' => 'Welcome back, ' . auth()->user()->first_name . '!',
            ]);
        } else {
            return redirect()->route('auth.loginForm')->withInput()->with('fail', 'Incorrect password.');
        }
    }
}
