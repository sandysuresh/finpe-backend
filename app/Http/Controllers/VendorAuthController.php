<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('vendor')->check()) {
            return redirect()->route('vendor.dashboard');
        }

        return view('auth.vendor-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('vendor')->attempt([
            ...$credentials,
            'status' => 'active',
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('vendor.dashboard'));
        }

        return back()
            ->withErrors(['email' => 'Invalid credentials or inactive account.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('vendor')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('vendor.login');
    }
}
