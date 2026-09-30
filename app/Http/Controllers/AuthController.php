<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->is_banned) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun Anda dinonaktifkan (BANNED) oleh Administrator.',
                ]);
            }

            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }

    /**
     * Quick Switcher to test any role instantly.
     */
    public function quickLogin(string $role): RedirectResponse
    {
        $validRoles = ['owner', 'gudang', 'pengadaan', 'kasir', 'dapur', 'customer'];
        if (!in_array($role, $validRoles, true)) {
            abort(404);
        }

        $user = User::where('role', $role)->first();
        if (!$user) {
            return redirect()->route('login')->with('error', "User dengan role {$role} belum ditemukan.");
        }

        Auth::login($user);
        request()->session()->regenerate();

        return $this->redirectBasedOnRole($user)->with('success', "Berhasil masuk sebagai {$user->name} ({$user->role})");
    }

    /**
     * Google Sign-In simulation for customer.
     */
    public function googleSignIn(Request $request): RedirectResponse
    {
        $email = $request->input('email', 'customer.google@coffeeshop.test');
        $name = $request->input('name', 'Google Customer');
        $googleId = 'goog_' . uniqid();

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(uniqid()),
                'role' => 'customer',
                'google_id' => $googleId,
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=0D8ABC&color=fff',
                'is_banned' => false,
            ]
        );

        if ($user->is_banned) {
            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan (BANNED).');
        }

        Auth::login($user);
        $request->session()->regenerate();

        $table = $request->input('table');
        if ($table) {
            return redirect()->route('customer.menu', ['table' => $table])->with('success', 'Berhasil masuk dengan Google Sign-In!');
        }

        return redirect()->route('customer.menu')->with('success', 'Berhasil masuk dengan Google Sign-In!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }

    private function redirectBasedOnRole(User $user): RedirectResponse
    {
        return match ($user->role) {
            'owner' => redirect()->route('owner.dashboard'),
            'gudang' => redirect()->route('gudang.dashboard'),
            'pengadaan' => redirect()->route('pengadaan.dashboard'),
            'kasir' => redirect()->route('kasir.dashboard'),
            'dapur' => redirect()->route('dapur.dashboard'),
            'customer' => redirect()->route('customer.menu'),
            default => redirect()->route('login'),
        };
    }
}
