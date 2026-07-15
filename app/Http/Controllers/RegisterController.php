<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OrganizationInvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifikasiEmail;
use Illuminate\Support\Facades\RateLimiter;

class RegisterController extends Controller
{
    public function registerView()
    {
        if (Auth::check()) {
            return redirect('/data');
        }

        return view('pages.auth.register', [
            'invitation_token' => request('invitation_token'),
            'invitation_email' => request('invitation_email'),
        ]);
    }

    public function register(Request $request)
    {
        if (Auth::check()) {
            return redirect('/data');
        }

        // ── Rate Limiting per IP ─────────────────────────────
        $key = 'register:' . $request->ip();

        // maksimal 10 percobaan
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);

            // ubah detik → menit + detik
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;

            $message = $minutes > 0
                ? "Terlalu banyak percobaan pendaftaran. Coba lagi dalam {$minutes} menit {$remainingSeconds} detik."
                : "Terlalu banyak percobaan pendaftaran. Coba lagi dalam {$remainingSeconds} detik.";

            return back()
                ->withErrors([
                    'email' => $message
                ])
                ->withInput();
        }

        // reset dalam 10 menit (600 detik)
        RateLimiter::hit($key, 600);

        // If reCAPTCHA is enabled, verify token
        if (env('RECAPTCHA_SECRET')) {
            $token = $request->input('g-recaptcha-response');
            if (! $token) {
                return back()->withErrors(['recaptcha' => 'reCAPTCHA diperlukan.'])->withInput();
            }

            try {
                $resp = \Illuminate\Support\Facades\Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => env('RECAPTCHA_SECRET'),
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);

                if (! $resp->successful() || ! ($resp->json('success') ?? false)) {
                    return back()->withErrors(['recaptcha' => 'reCAPTCHA gagal.'])->withInput();
                }
            } catch (\Throwable $e) {
                return back()->withErrors(['recaptcha' => 'Verifikasi reCAPTCHA gagal.'])->withInput();
            }
        }

        // ── Validasi ─────────────────────────────────────────
        $request->validate([
            'name'               => 'required|string|max:200',
            'username'           => 'required|string|max:50|unique:user,username',
            'email'              => 'required|email|max:50|unique:user,email',
            'password'           => 'required|string|min:8|confirmed',
            'privacy_policy'     => 'accepted',
            'invitation_token'   => 'nullable|string',
            'invitation_email'   => 'nullable|email',
        ], [
            'name.required'           => 'Nama wajib diisi.',
            'username.required'       => 'Username wajib diisi.',
            'username.unique'         => 'Username sudah digunakan.',
            'email.required'          => 'Email wajib diisi.',
            'email.unique'            => 'Email sudah terdaftar.',
            'password.required'       => 'Password wajib diisi.',
            'password.min'            => 'Password minimal 8 karakter.',
            'password.confirmed'      => 'Konfirmasi password tidak cocok.',
            'privacy_policy.accepted' => 'Anda harus menyetujui Kebijakan Privasi.',
        ]);

        $token = Str::random(64);

        $user = User::create([
            'name'          => $request->name,
            'username'      => $request->username,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'group_id'      => 3,
            'block'         => 0,
            'registerdate'  => now(),
            'lastvisitdate' => now(),
            'activation'    => $token,
        ]);

        Mail::to($user->email)->send(new VerifikasiEmail($user, $token));

        if ($request->filled('invitation_token')) {
            $invitationService = app(OrganizationInvitationService::class);
            $member = $invitationService->acceptInvitationForUser($request->input('invitation_token'), $user);

            if ($member) {
                return redirect()->route('login')
                    ->with('success', 'Registrasi berhasil! Silakan cek email untuk verifikasi akun. Undangan organisasi juga berhasil diterima setelah login.');
            }
        }

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Registrasi berhasil! Silakan cek email untuk verifikasi akun.'
            );
    }

    public function verify(Request $request, string $token)
    {
        $user = User::where('activation', $token)->first();

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['username' => 'Link verifikasi tidak valid atau sudah digunakan.']);
        }

        $user->update(['activation' => 'activated']);

        return redirect()->route('login')
            ->with('success', 'Email berhasil diverifikasi! Silakan login.');
    }
}