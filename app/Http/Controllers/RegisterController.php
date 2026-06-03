<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifikasiEmail;
use Illuminate\Support\Facades\RateLimiter;

class RegisterController extends Controller
{
    public function registerView()
    {
        if (auth()->check()) {
            return redirect('/data');
        }
        return view('pages.auth.register');
    }

    public function register(Request $request)
    {
        if (auth()->check()) {
            return redirect('/data');
        }

        // ── Rate Limiting per IP ──────────────────────────────────
        $key = 'register:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()
                ->withErrors(['email' => "Terlalu banyak percobaan pendaftaran. Coba lagi dalam {$seconds} detik."])
                ->withInput();
        }

        RateLimiter::hit($key, 3600); // 5 percobaan per jam

        // ── Validasi ──────────────────────────────────────────────
        $request->validate([
            'name'           => 'required|string|max:200',
            'username'       => 'required|string|max:50|unique:user,username',
            'email'          => 'required|email|max:50|unique:user,email',
            'password'       => 'required|string|min:8|confirmed',
            'privacy_policy' => 'accepted',
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
            'group_id'      => 3, // Customer
            'block'         => 0,
            'registerdate'  => now(),
            'lastvisitdate' => now(),
            'activation'    => $token, // token verifikasi, bukan 'activated'
        ]);

        Mail::to($user->email)->send(new VerifikasiEmail($user, $token));

        return redirect()->route('login')
            ->with('success', 'Registrasi berhasil! Silakan cek email untuk verifikasi akun.');
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