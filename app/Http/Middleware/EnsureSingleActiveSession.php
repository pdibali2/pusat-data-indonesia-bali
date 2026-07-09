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

                return redirect('/')->with('forced_logout_message', 'Sesi anda telah habis, dikarenakan akun anda login di device berbeda.');
            }

            $this->sessionLimitService->enforceSessionLimit($user, $request);

            $activeSession = UserSession::where('user_id', $user->user_id)
                ->where('session_token', $sessionToken)
                ->where('is_active', true)
                ->first();

            if (! $activeSession) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/')->with('error', 'Sesi Anda telah berakhir karena login dari perangkat lain.');
            }
        }

        $warningKey = 'session-limit-warning:' . ($sessionToken);
        if (cache()->has($warningKey)) {
            $request->session()->flash('warning', cache()->pull($warningKey));
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
