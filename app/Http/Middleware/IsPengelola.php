<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsPengelola
{
    // Route yang TIDAK boleh diakses Pengelola (group_id = 2)
    private array $blockedRoutes = [
        'admin.users.*',
        'admin.groups.*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Hanya berlaku untuk Pengelola (group_id = 2)
        if ($user->group_id !== 2) {
            return $next($request);
        }

        $currentRoute = $request->route()?->getName() ?? '';

        foreach ($this->blockedRoutes as $pattern) {
            if (fnmatch($pattern, $currentRoute)) {
                abort(403, 'Anda tidak memiliki akses ke halaman ini.');
            }
        }

        return $next($request);
    }
}