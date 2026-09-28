<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Support\LegacyPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        $vendor = Vendor::query()
            ->where('email', $credentials['email'])
            ->where('status', 'active')
            ->first();

        $stored = $vendor?->getRawOriginal('password');

        if (! $vendor || ! LegacyPassword::check($credentials['password'], $stored)) {
            return back()
                ->withErrors(['email' => 'Invalid credentials or inactive account.'])
                ->onlyInput('email');
        }

        $normalized = LegacyPassword::normalize($stored);
        if ($stored !== $normalized || Hash::needsRehash($normalized)) {
            $vendor->password = $credentials['password'];
            $vendor->save();
        }

        Auth::guard('vendor')->login($vendor, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('vendor.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('vendor')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('vendor.login');
    }
}
