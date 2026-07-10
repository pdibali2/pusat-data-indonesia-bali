<?php

namespace App\Http\Controllers;

use App\Models\LoginVerification;
use App\Models\PendingLogin;
use App\Models\UserSession;
use App\Services\LoginSecurityService;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SessionSecurityController extends Controller
{
    public function __construct(protected LoginSecurityService $loginSecurityService)
    {
    }

    public function securityNotifications(Request $request)
    {
        $user = $request->user();
        $sessionToken = $request->session()->getId();

        $currentSession = UserSession::where('user_id', $user->user_id)
            ->where('session_token', $sessionToken)
            ->where('is_active', true)
            ->first();

        if (! $currentSession) {
            return response()->json(['pending' => false]);
        }

        $pendingLogin = PendingLogin::where('target_session_id', $currentSession->user_session_id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($pendingLogin) {
            return response()->json([
                'pending' => true,
                'type' => 'pending_login',
                'id' => $pendingLogin->id,
                'device_info' => $pendingLogin->device_info,
                'ip_address' => $pendingLogin->ip_address,
                'estimated_location' => $pendingLogin->estimated_location,
                'created_at' => $pendingLogin->created_at?->format('Y-m-d H:i:s'),
            ]);
        }

        $loginVerification = LoginVerification::where('user_id', $user->user_id)
            ->where('status', 'pending')
            ->where('new_session_id', '!=', $currentSession->user_session_id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($loginVerification) {
            return response()->json([
                'pending' => true,
                'type' => 'security_verification',
                'id' => $loginVerification->id,
                'device_type' => $loginVerification->device_type,
                'browser' => $loginVerification->browser,
                'estimated_location' => $loginVerification->estimated_location,
                'created_at' => $loginVerification->created_at?->format('Y-m-d H:i:s'),
            ]);
        }

        return response()->json(['pending' => false]);
    }

    public function approveVerification(int $id, Request $request)
    {
        $user = $request->user();
        $sessionToken = $request->session()->getId();
        $currentSession = UserSession::where('user_id', $user->user_id)
            ->where('session_token', $sessionToken)
            ->where('is_active', true)
            ->firstOrFail();

        $verification = LoginVerification::where('id', $id)
            ->where('user_id', $user->user_id)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($verification->new_session_id === $currentSession->user_session_id) {
            abort(403);
        }

        $verification->update([
            'status' => 'approved',
            'responded_by_session_id' => $currentSession->user_session_id,
            'responded_at' => now(),
        ]);

        return response()->json(['status' => 'approved']);
    }

    public function rejectVerification(int $id, Request $request)
    {
        $user = $request->user();
        $sessionToken = $request->session()->getId();
        $currentSession = UserSession::where('user_id', $user->user_id)
            ->where('session_token', $sessionToken)
            ->where('is_active', true)
            ->firstOrFail();

        $verification = LoginVerification::where('id', $id)
            ->where('user_id', $user->user_id)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($verification->new_session_id === $currentSession->user_session_id) {
            abort(403);
        }

        $verification->update([
            'status' => 'rejected',
            'responded_by_session_id' => $currentSession->user_session_id,
            'responded_at' => now(),
        ]);

        $newSession = UserSession::find($verification->new_session_id);
        if ($newSession) {
            $newSession->update([
                'is_active' => false,
                'logout_reason' => 'security_rejected',
            ]);
        }

        return response()->json([
            'status' => 'rejected',
            'redirect' => route('user-password.edit'),
            'message' => 'Login yang tidak dikenali telah diblokir. Silakan ganti password Anda.',
        ]);
    }

    public function approvePendingLogin(int $id, Request $request)
    {
        $user = $request->user();
        $sessionToken = $request->session()->getId();
        $currentSession = UserSession::where('user_id', $user->user_id)
            ->where('session_token', $sessionToken)
            ->where('is_active', true)
            ->firstOrFail();

        $pendingLogin = PendingLogin::where('id', $id)
            ->where('user_id', $user->user_id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        if ($pendingLogin->target_session_id !== $currentSession->user_session_id) {
            abort(403);
        }

        $newSession = DB::transaction(function () use ($pendingLogin, $currentSession) {
            $pendingLogin->update([
                'status' => 'approved',
                'responded_at' => now(),
            ]);

            $currentSession->update([
                'is_active' => false,
                'logout_reason' => 'device_limit_replaced',
            ]);

            return UserSession::create([
                'user_id' => $pendingLogin->user_id,
                'session_id' => $pendingLogin->session_token,
                'session_token' => $pendingLogin->session_token,
                'ip_address' => $pendingLogin->ip_address,
                'user_agent' => $pendingLogin->user_agent,
                'login_at' => now(),
                'is_active' => true,
            ]);
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => 'approved',
            'redirect' => route('login'),
            'message' => 'Anda logout karena akun ini digunakan di perangkat lain.',
        ]);
    }

    public function rejectPendingLogin(int $id, Request $request)
    {
        $user = $request->user();
        $sessionToken = $request->session()->getId();
        $currentSession = UserSession::where('user_id', $user->user_id)
            ->where('session_token', $sessionToken)
            ->where('is_active', true)
            ->firstOrFail();

        $pendingLogin = PendingLogin::where('id', $id)
            ->where('user_id', $user->user_id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        if ($pendingLogin->target_session_id !== $currentSession->user_session_id) {
            abort(403);
        }

        $pendingLogin->update([
            'status' => 'rejected',
            'responded_at' => now(),
        ]);

        return response()->json(['status' => 'rejected']);
    }

    public function pendingLoginStatus(int $id)
    {
        $pendingLogin = PendingLogin::findOrFail($id);

        if ($pendingLogin->status === 'pending' && $pendingLogin->expires_at->isPast()) {
            $pendingLogin->update(['status' => 'expired']);
        }

        if ($pendingLogin->status === 'pending') {
            return response()->json([
                'status' => 'pending',
                'message' => 'Menunggu konfirmasi dari perangkat aktif lainnya.',
            ]);
        }

        if ($pendingLogin->status === 'approved') {
            return response()->json([
                'status' => 'approved',
                'message' => 'Login disetujui.',
                'redirect' => '/data',
            ]);
        }

        return response()->json([
            'status' => $pendingLogin->status,
            'message' => $pendingLogin->status === 'rejected'
                ? 'Login ditolak oleh pemilik akun aktif.'
                : 'Permintaan login kadaluarsa. Silakan coba lagi.',
        ]);
    }

    public function pendingLoginWait(int $id)
    {
        $pendingLogin = PendingLogin::findOrFail($id);

        return view('pages.auth.pending-login', [
            'pendingLogin' => $pendingLogin,
        ]);
    }
}
