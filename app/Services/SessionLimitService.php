<?php

namespace App\Services;

use App\Models\PendingLogin;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;

class SessionLimitService
{
    public function __construct(
        protected SubscriptionLimitsService $subscriptionLimitsService,
        protected LoginSecurityService $loginSecurityService,
    ) {
    }

    public function handleLoginAttempt(User $user, Request $request): array
    {
        $sessionToken = $request->session()->getId();

        if (blank($sessionToken)) {
            return ['status' => 'error', 'message' => 'Tidak dapat mengambil session token.'];
        }

        $limit = max(1, $this->subscriptionLimitsService->getMaxConcurrentSessions($user));

        // Mark stale sessions as inactive. A session is considered stale when
        // it has not been active for `session.stale_timeout_minutes`.
        $staleMinutes = (int) config('session.stale_timeout_minutes', 2);
        if ($staleMinutes > 0) {
            UserSession::where('user_id', $user->user_id)
                ->where('is_active', true)
                ->where('updated_at', '<', now()->subMinutes($staleMinutes))
                ->update([
                    'is_active' => false,
                    'logout_reason' => 'stale_session',
                ]);
        }

        $activeSessions = UserSession::where('user_id', $user->user_id)
            ->where('is_active', true)
            ->orderBy('login_at')
            ->orderBy('created_at')
            ->get();

        if ($activeSessions->count() >= $limit) {
            $oldestSession = $activeSessions->first();
            if (! $oldestSession) {
                return ['status' => 'error', 'message' => 'Tidak dapat memproses login.'];
            }

            $pendingLogin = $this->loginSecurityService->createPendingLogin($request, $oldestSession);

            return ['status' => 'pending_login', 'pending_login' => $pendingLogin];
        }

        $session = $this->upsertCurrentSession($user, $sessionToken, $request);

        return [
            'status' => 'active',
            'session' => $session,
        ];
    }

    protected function upsertCurrentSession(User $user, string $sessionToken, Request $request): UserSession
    {
        $session = UserSession::firstOrNew([
            'user_id' => $user->user_id,
            'session_token' => $sessionToken,
        ]);

        if ($session->exists && ! $session->is_active) {
            return $session;
        }

        $session->fill([
            'session_id' => $sessionToken,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_at' => now(),
            'is_active' => true,
        ]);

        $session->save();

        return $session;
    }
}
