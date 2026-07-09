<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;

class SessionLimitService
{
    public function __construct(protected SubscriptionLimitsService $subscriptionLimitsService)
    {
    }

    public function enforceSessionLimit(User $user, ?Request $request = null): void
    {
        $sessionToken = $request?->session()?->getId();

        if (blank($sessionToken)) {
            return;
        }

        $this->upsertCurrentSession($user, $sessionToken, $request);

        $limit = max(1, $this->subscriptionLimitsService->getMaxConcurrentSessions($user));
        $activeSessions = UserSession::where('user_id', $user->user_id)
            ->where('is_active', true)
            ->orderBy('login_at')
            ->orderBy('created_at')
            ->get();

        if ($activeSessions->count() <= $limit) {
            return;
        }

        $toInvalidate = $activeSessions
            ->reject(fn (UserSession $session): bool => $this->isCurrentSession($session, $sessionToken))
            ->take($activeSessions->count() - $limit);

        foreach ($toInvalidate as $session) {
            $this->invalidateSession($session);
        }

        if ($toInvalidate->isNotEmpty() && $request?->hasSession()) {
            $request->session()->flash(
                'success',
                'Login berhasil. Sesi Anda di perangkat lain yang paling lama tidak aktif telah otomatis diakhiri karena mencapai batas maksimal perangkat aktif.'
            );
        }
    }

    protected function upsertCurrentSession(User $user, string $sessionToken, ?Request $request): UserSession
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
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'login_at' => now(),
            'is_active' => true,
        ]);

        $session->save();

        return $session;
    }

    protected function invalidateSession(UserSession $session): void
    {
        $session->forceFill(['is_active' => false])->save();

        $warningKey = 'session-limit-warning:' . ($session->session_token ?? $session->session_id);
        cache()->put(
            $warningKey,
            'Sesi Anda telah diakhiri karena akun login di perangkat lain.',
            now()->addMinutes(5)
        );
    }

    protected function isCurrentSession(UserSession $session, string $sessionToken): bool
    {
        return $session->session_token === $sessionToken
            || $session->session_id === $sessionToken;
    }
}
