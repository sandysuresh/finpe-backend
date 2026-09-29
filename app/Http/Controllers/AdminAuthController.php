<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Support\AdminAudit;
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
            AdminAudit::record('Admin login', 'Success', 'Admin #'.$admin->id.' '.$admin->email, null, null, $admin);

            $intended = $request->session()->pull('url.intended');
            if (is_string($intended) && $this->isSafeInternalUrl($intended)) {
                return redirect()->to($intended);
            }

            return redirect()->to(\App\Support\AdminModules::firstUrl($admin));
        }

        $known = Admin::query()->where('email', $credentials['email'])->first();
        AdminAudit::record(
            'Admin login',
            'Failed',
            $credentials['email'],
            null,
            'Invalid credentials or inactive account',
            $known,
            'admin',
            $credentials['email'],
        );

        return back()
            ->withErrors(['email' => 'Invalid admin credentials or inactive account.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if ($admin) {
            AdminAudit::record('Admin logout', 'Success', 'Admin #'.$admin->id.' '.$admin->email, null, null, $admin);
        }

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