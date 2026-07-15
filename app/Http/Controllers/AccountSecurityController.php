<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AccountSecurityController extends Controller
{
    public function unlock(Request $request, string $token)
    {
        $user = User::where('unlock_token', $token)
            ->where('unlock_token_expires_at', '>', now())
            ->first();

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['username' => 'Tautan unlock tidak valid atau sudah kadaluarsa.']);
        }

        $user->update([
            'locked_at' => null,
            'unlock_token' => null,
            'unlock_token_expires_at' => null,
        ]);

        RateLimiter::clear(Str::transliterate(Str::lower($user->username).'|'.request()->ip()));

        return redirect()->route('login')
            ->with('success', 'Akun berhasil dibuka kunci. Silakan login.');
    }
}
