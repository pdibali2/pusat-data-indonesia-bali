<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MailNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\RecaptchaService;

class PasswordResetController extends Controller
{
    // ── Halaman form input email ──────────────────────────────
    public function requestView()
    {
        return view('pages.auth.forgot-password');
    }

    // ── Proses kirim email reset ──────────────────────────────
    public function sendResetLink(Request $request, RecaptchaService $recaptcha, MailNotifier $mailNotifier)
    {
        // Rate limiting: 3x per jam per IP
        $key = 'password-reset:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $menit   = ceil($seconds / 60);
            return back()
                ->withErrors(['email' => "Terlalu banyak percobaan. Coba lagi dalam {$menit} menit."])
                ->withInput();
        }

        RateLimiter::hit($key, 3600);

        if (! app()->environment('testing') && $recaptcha->isEnabled()) {
            $token = $request->input('g-recaptcha-response');

            if (! $recaptcha->verify($token, 'forgot_password', $request->ip())) {
                return back()->withErrors(['recaptcha' => 'Verifikasi keamanan gagal, silakan coba lagi.'])->withInput();
            }
        }

        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        $user = User::where('email', $request->email)->first();

        // Selalu tampilkan pesan sukses meski email tidak ditemukan
        // (mencegah user menebak email terdaftar)
        if (!$user) {
            return back()->with('success', 'Jika email terdaftar, link reset password telah dikirim.');
        }

        // Hapus token lama jika ada
        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->delete();

        // Buat token baru
        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email'      => $user->email,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        // Kirim email lewat MailNotifier (otomatis pilih GAS / SMTP / Mailtrap
        // sesuai MAIL_MAILER di .env, tidak perlu diubah manual di sini)
        $mailNotifier->kirimResetPassword($user, $token);

        return back()->with('success', 'Jika email terdaftar, link reset password telah dikirim.');
    }

    // ── Halaman form input password baru ─────────────────────
    public function resetView(Request $request, string $token)
    {
        return view('pages.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    // ── Proses reset password ─────────────────────────────────
    public function reset(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'token'    => 'required',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required'      => 'Email wajib diisi.',
            'password.required'   => 'Password wajib diisi.',
            'password.min'        => 'Password minimal 8 karakter.',
            'password.confirmed'  => 'Konfirmasi password tidak cocok.',
        ]);

        // Cari record token
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        // Validasi token tidak ditemukan
        if (!$record) {
            return back()->withErrors(['email' => 'Link reset tidak valid atau sudah digunakan.']);
        }

        // Validasi token tidak cocok
        if (!Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Link reset tidak valid atau sudah digunakan.']);
        }

        // Validasi token kadaluarsa (24 jam)
        $expiredAt = Carbon::parse($record->created_at)->addHours(24);
        if (Carbon::now()->greaterThan($expiredAt)) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Link reset password sudah kadaluarsa. Silakan minta link baru.']);
        }

        // Update password
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Akun tidak ditemukan.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Hapus token setelah dipakai
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return back()->with('reset_success', true);
    }
}