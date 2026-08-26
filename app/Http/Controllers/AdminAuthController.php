<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            $admin->loadMissing('modulePermissions');

            return redirect()->to(\App\Support\AdminModules::firstUrl($admin));
        }

        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('admin')->attempt([
            ...$credentials,
            'status' => 'active',
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $admin = Auth::guard('admin')->user();
            $admin->loadMissing('modulePermissions');

            $intended = $request->session()->pull('url.intended');
            if (is_string($intended) && $this->isSafeInternalUrl($intended)) {
                return redirect()->to($intended);
            }

            return redirect()->to(\App\Support\AdminModules::firstUrl($admin));
        }

        return back()
            ->withErrors(['email' => 'Invalid admin credentials or inactive account.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function isSafeInternalUrl(string $url): bool
    {
        if ($url === '' || str_starts_with($url, '//')) {
            return false;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return true;
        }

        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($appHost) && is_string($host) && strcasecmp($appHost, $host) === 0 && URL::isValidUrl($url);
    }
}