<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use App\Services\SessionLimitService;
use App\Services\SubscriptionLimitsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleActiveSession
{
    public function __construct(
        protected SessionLimitService $sessionLimitService,
        protected SubscriptionLimitsService $subscriptionLimitsService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $sessionToken = $request->session()->getId();

        if ($this->shouldEnforce($user)) {
            $existingSession = UserSession::where('user_id', $user->user_id)
                ->where('session_token', $sessionToken)
                ->first();

            if ($existingSession && ! $existingSession->is_active) {
                Auth::logout();
                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                return redirect('/login')->with('forced_logout_message', 'Anda logout karena akun ini digunakan di perangkat lain.');
            }

            if (! $existingSession) {
                $pendingLogin = \App\Models\PendingLogin::where('session_token', $sessionToken)
                    ->where('status', 'pending')
                    ->where('expires_at', '>', now())
                    ->first();

                if ($pendingLogin) {
                    $routeName = $request->route()?->getName() ?? '';
                    if (in_array($routeName, [
                        'session.security_notifications',
                        'session.pending_login.status',
                        'session.pending_login.wait',
                    ], true)) {
                        return $next($request);
                    }

                    return redirect()->route('session.pending_login.wait', ['id' => $pendingLogin->id]);
                }

                Auth::logout();
                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                return redirect('/login')->with('error', 'Sesi Anda tidak aktif atau tidak ditemukan.');
            }
        }

        return $next($request);
    }

    protected function shouldEnforce($user): bool
    {
        if (! $user) {
            return false;
        }

        // Hanya enforce untuk customer (group_id = 3). Admin/pengelola tidak dibatasi.
        return (int) $user->group_id === 3;
    }
}
