<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $input = $data['username'];

        if (str_contains($input, '@')) {
            $field = 'email';
        } else {
            $field = 'username';
        }

        $credentials = [
            $field => $input,
            'password' => $data['password'],
            'status' => 'ACTIVE',
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user && method_exists($user, 'update')) {
                $user->update(['last_login_at' => now()]);
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withErrors(['username' => 'Email atau Password tidak sesuai.'])
            ->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'current_password' => ['required'],
            'password' => ['required', 'string', 'confirmed'],
        ];

        // Username validation (unique except for current user)
        if ($request->filled('username')) {
            $rules['username'] = ['string', 'max:255', 'regex:/^[a-zA-Z0-9_]+$/', 'unique:users,username,' . $user->id];
        }

        $validated = $request->validate($rules);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password saat ini tidak sesuai.',
            ]);
        }

        $updateData = [];

        // Update username if provided and changed
        if ($request->filled('username') && $request->username !== $user->username) {
            $updateData['username'] = $request->username;
        }

        // Update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->input('password'));
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        $msg = [];
        if (isset($updateData['username'])) {
            $msg[] = 'Username';
        }
        if (isset($updateData['password'])) {
            $msg[] = 'Password';
        }

        $successMsg = !empty($msg)
            ? implode(' dan ', $msg) . ' berhasil diperbarui.'
            : 'Tidak ada perubahan.';

        return back()->with('success', $successMsg);
    }
}
