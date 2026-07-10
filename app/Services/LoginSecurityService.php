<?php

namespace App\Services;

use App\Models\LoginVerification;
use App\Models\PendingLogin;
use App\Models\UserSession;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class LoginSecurityService
{
    public function __construct(
        protected SubscriptionLimitsService $subscriptionLimitsService,
        protected IpGeolocationService $ipGeolocationService,
        protected DatabaseManager $db,
    ) {
    }

    public function detectDeviceInfo(Request $request): array
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent() ?? '');

        $deviceType = $agent->device();
        if ($agent->isDesktop()) {
            $deviceType = 'Desktop';
        } elseif ($agent->isTablet()) {
            $deviceType = 'Tablet';
        } elseif ($agent->isMobile()) {
            $deviceType = 'Mobile';
        }

        return [
            'device_type' => $deviceType ?: 'Unknown',
            'browser' => $agent->browser() ?: 'Unknown',
            'platform' => $agent->platform() ?: 'Unknown',
        ];
    }

    public function buildDeviceInfoString(Request $request): string
    {
        $device = $this->detectDeviceInfo($request);

        return trim(sprintf('%s • %s • %s', $device['device_type'], $device['browser'], $device['platform']));
    }

    public function createPendingLogin(Request $request, UserSession $targetSession): PendingLogin
    {
        $deviceInfo = $this->buildDeviceInfoString($request);
        $location = $this->ipGeolocationService->resolve($request->ip());

        return PendingLogin::create([
            'user_id' => $targetSession->user_id,
            'ip_address' => $request->ip(),
            'device_info' => $deviceInfo,
            'target_session_id' => $targetSession->user_session_id,
            'session_token' => $request->session()->getId(),
            'user_agent' => $request->userAgent(),
            'device_type' => $deviceInfo === '' ? null : $this->detectDeviceInfo($request)['device_type'],
            'browser' => $this->detectDeviceInfo($request)['browser'],
            'estimated_location' => $location,
            'status' => 'pending',
            'created_at' => now(),
            'expires_at' => now()->addMinutes(PendingLogin::LOGIN_CONFIRMATION_TIMEOUT_MINUTES),
        ]);
    }

    public function createLoginVerification(Request $request, UserSession $newSession): ?LoginVerification
    {
        $activeOtherSessions = UserSession::where('user_id', $newSession->user_id)
            ->where('is_active', true)
            ->where('user_session_id', '!=', $newSession->user_session_id)
            ->exists();

        if (! $activeOtherSessions) {
            return null;
        }

        $device = $this->detectDeviceInfo($request);
        $location = $this->ipGeolocationService->resolve($request->ip());

        return LoginVerification::create([
            'user_id' => $newSession->user_id,
            'new_session_id' => $newSession->user_session_id,
            'device_type' => $device['device_type'],
            'browser' => $device['browser'],
            'estimated_location' => $location,
            'ip_address' => $request->ip(),
            'status' => 'pending',
        ]);
    }

    public function isOrganizationPackage(UserSession $session): bool
    {
        $limit = $this->subscriptionLimitsService->getMaxConcurrentSessions($session->user);

        return $limit > 1;
    }
}
