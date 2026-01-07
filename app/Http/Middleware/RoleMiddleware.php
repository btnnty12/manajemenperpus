<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (! session()->has('role')) {
            if (auth()->check() && auth()->user()->peran) {
                session()->put('role', auth()->user()->peran);
            } else {
                return redirect('/login')->with('error', 'Silakan login terlebih dahulu');
            }
        }

        $normalize = function ($role) {
            $r = strtolower(trim((string) $role));

            return match ($r) {
                'administrator' => 'admin',
                'petugas' => 'staff',
                default => $r,
            };
        };

        $userRole = $normalize(session('role'));
        $allowedRoles = array_map($normalize, $roles);

        if (! in_array($userRole, $allowedRoles)) {
            return match ($userRole) {
                'admin' => redirect()->route('admin')->with('error', 'Tidak punya akses'),
                'staff' => redirect()->route('staff')->with('error', 'Tidak punya akses'),
                default => redirect()->route('home')->with('error', 'Tidak punya akses'),
            };
        }

        return $next($request);
    }
}
