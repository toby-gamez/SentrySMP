<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function loginForm(): View|RedirectResponse
    {
        if (session('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $expectedUsername = config('services.admin.username', 'admin');
        $expectedPassword = config('services.admin.password', 'changeme');

        if ($request->username === $expectedUsername && $request->password === $expectedPassword) {
            session(['admin_logged_in' => true, 'admin_username' => $request->username]);
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['credentials' => 'Invalid username or password.'])->withInput(['username' => $request->username]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['admin_logged_in', 'admin_username']);
        return redirect()->route('admin.login');
    }
}
