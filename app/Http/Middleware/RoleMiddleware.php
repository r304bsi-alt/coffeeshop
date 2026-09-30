<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
        }

        $user = Auth::user();

        // Check if user is banned
        if ($user->is_banned) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan (BANNED) oleh Owner. Hubungi administrator.');
        }

        // If no roles specified, just ensure authenticated
        if (empty($roles)) {
            return $next($request);
        }

        // Check role
        if (!in_array($user->role, $roles, true)) {
            // If user doesn't have role, redirect to their role home
            $dashboardRoute = match ($user->role) {
                'owner' => 'owner.dashboard',
                'gudang' => 'gudang.dashboard',
                'pengadaan' => 'pengadaan.dashboard',
                'kasir' => 'kasir.dashboard',
                'dapur' => 'dapur.dashboard',
                'customer' => 'customer.menu',
                default => 'home',
            };

            return redirect()->route($dashboardRoute)->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
        }

        return $next($request);
    }
}
