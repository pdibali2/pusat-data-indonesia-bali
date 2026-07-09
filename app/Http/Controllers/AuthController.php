<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSession;
use App\Services\OrganizationInvitationService;
use App\Services\SessionLimitService;
use App\Services\SubscriptionLimitsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function loginView()
    {
        if (Auth::check()){
            return back();
        }
        return view('pages.auth.login');
    }

    public function login(Request $request)
    {
        if (Auth::check()){
            return back();
        }
        
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
            'invitation_token' => ['nullable', 'string'],
            'invitation_email' => ['nullable', 'email'],
        ]);

        // Cari user berdasarkan username
        $user = User::where('username', $validated['username'])->first();

        // Username tidak ditemukan
        if (!$user) {
            return back()
                ->withErrors(['username' => 'Username tidak ditemukan.'])
                ->withInput();
        }

        // Jika status tidak aktif
        if ($user->activation !== 'activated') {
            return back()
                ->withErrors(['username' => 'Akun belum aktif.'])
                ->withInput();
        }

        // Password salah
        if (!Hash::check($validated['password'], $user->password)) {
            return back()
                ->withErrors(['password' => 'Password yang Anda masukkan salah.'])
                ->withInput();
        }

        // Login berhasil
        Auth::login($user);
        $request->session()->regenerate();

        if ($request->filled('invitation_token')) {
            $invitationService = app(OrganizationInvitationService::class);
            $membership = $invitationService->acceptInvitationForUser($request->input('invitation_token'), $user);

            if ($membership) {
                return redirect('/data')->with('success', 'Undangan organisasi berhasil diterima. Selamat bergabung!');
            }

            $invitation = $invitationService->findPendingInvitationByToken($request->input('invitation_token'));

            if ($invitation && $invitation->email !== $user->email) {
                return redirect('/data')->withErrors(['invitation' => 'Silakan login menggunakan akun yang diundang untuk menerima undangan organisasi.']);
            }

            return redirect('/data')->withErrors(['invitation' => 'Tidak dapat menerima undangan organisasi. Silakan periksa kembali tautan undangan atau hubungi pemilik organisasi.']);
        }

        // Only enforce session limits for customers (group_id === 3). Admin/pengelola are unlimited.
        if ((int) $user->group_id === 3) {
            $limitsService = app(SessionLimitService::class);
            $limitsService->enforceSessionLimit($user, $request);
        }

        return redirect('/data');
    }

    public function logout(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/');
        }

        Auth::logout();

        if ($request->hasSession()) {
            UserSession::where('session_id', $request->session()->getId())->delete();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}