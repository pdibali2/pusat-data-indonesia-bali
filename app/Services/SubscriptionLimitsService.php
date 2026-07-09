<?php

namespace App\Services;

use App\Models\Layanan;
use App\Models\Tampilan;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;

class SubscriptionLimitsService
{
    public function hasActiveSubscription(User $user): bool
    {
        return $this->getActiveSubscription($user) !== null;
    }

    public function getActiveSubscription(User $user): ?Layanan
    {
        $transaction = Transaksi::where('user_id', $user->user_id)
            ->where('status', 'success')
            ->where(function ($query): void {
                $query->whereNull('aktif_sampai')
                    ->orWhere('aktif_sampai', '>=', now());
            })
            ->with('layanan')
            ->latest('aktif_sampai')
            ->latest('created_at')
            ->first();

        return $transaction?->layanan;
    }

    public function getMaxConcurrentSessions(User $user): int
    {
        $subscription = $this->getActiveSubscription($user);

        if ($subscription) {
            return (int) ($subscription->max_concurrent_sessions ?? $this->getDefaultMaxConcurrentSessions($subscription));
        }

        return 1;
    }

    public function getMaxTemplates(User $user): int
    {
        $subscription = $this->getActiveSubscription($user);

        if ($subscription) {
            return (int) ($subscription->max_templates ?? $this->getDefaultMaxTemplates($subscription));
        }

        return 5;
    }

    public function getTemplateCountForUser(User $user): int
    {
        return (int) Tampilan::where('user_id', $user->user_id)->count();
    }

    public function canCreateTemplate(User $user): bool
    {
        return $this->getTemplateCountForUser($user) < $this->getMaxTemplates($user);
    }

    public function enforceConcurrentSessionLimit(User $user, string $sessionId, ?Request $request = null): array
    {
        if (! $this->hasActiveSubscription($user)) {
            return ['limit' => 1, 'evicted_count' => 0];
        }

        $limit = max(1, $this->getMaxConcurrentSessions($user));

        UserSession::updateOrCreate(
            [
                'user_id' => $user->user_id,
                'session_id' => $sessionId,
            ],
            [
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'login_at' => now(),
            ]
        );

        $activeSessions = UserSession::where('user_id', $user->user_id)
            ->orderBy('login_at')
            ->orderBy('created_at')
            ->get();

        if ($activeSessions->count() <= $limit) {
            return ['limit' => $limit, 'evicted_count' => 0];
        }

        $toEvict = $activeSessions
            ->reject(fn (UserSession $session): bool => $session->session_id === $sessionId)
            ->take($activeSessions->count() - $limit);

        foreach ($toEvict as $session) {
            $this->destroySessionRecord($session);
        }

        if ($toEvict->isNotEmpty()) {
            $warningKey = 'subscription-limit-warning:' . $user->user_id;
            cache()->put($warningKey, 'Sesi lama Anda otomatis ditutup karena batas perangkat paket Anda.', now()->addMinutes(5));

            if ($request?->hasSession()) {
                $request->session()->flash('warning', 'Sesi lama Anda otomatis ditutup karena batas perangkat paket Anda.');
            }
        }

        return ['limit' => $limit, 'evicted_count' => $toEvict->count()];
    }

    protected function getDefaultMaxConcurrentSessions(Layanan $subscription): int
    {
        return $this->normalizeCategory($subscription) === 'organisasi' ? 5 : 1;
    }

    protected function getDefaultMaxTemplates(Layanan $subscription): int
    {
        return $this->normalizeCategory($subscription) === 'organisasi' ? 50 : 10;
    }

    protected function normalizeCategory(Layanan $subscription): string
    {
        $category = (string) ($subscription->category ?? $subscription->audience_type ?? 'personal');

        return match ($category) {
            'organisasi', 'organization', 'org' => 'organisasi',
            default => 'personal',
        };
    }

    protected function destroySessionRecord(UserSession $session): void
    {
        if ($session->session_id) {
            try {
                app('session')->driver()->destroy($session->session_id);
            } catch (\Throwable) {
                // Ignore storage errors and delete the tracking row.
            }
        }

        $session->delete();
    }
}
