<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    protected ?string $secret;
    protected float $threshold;

    public function __construct()
    {
        $this->secret = env('RECAPTCHA_SECRET');
        $this->threshold = (float) env('RECAPTCHA_THRESHOLD', 0.5);
    }

    /**
     * Cek apakah reCAPTCHA aktif (secret sudah di-set di .env).
     */
    public function isEnabled(): bool
    {
        return ! empty($this->secret);
    }

    /**
     * Verifikasi token reCAPTCHA v3 ke Google, termasuk cek score & action.
     *
     * @param string|null $token   Token dari input g-recaptcha-response
     * @param string      $action  Action yang diharapkan (mis. 'login', 'register')
     * @param string|null $ip      IP address request
     * @return bool
     */
    public function verify(?string $token, string $action, ?string $ip = null): bool
    {
        if (! $this->isEnabled()) {
            return true; // reCAPTCHA dimatikan, anggap lolos
        }

        if (! $token) {
            return false;
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $this->secret,
                'response' => $token,
                'remoteip' => $ip,
            ]);

            

            if (! $response->successful()) {
                return false;
            }

            $body = $response->json();

            if (! ($body['success'] ?? false)) {
                return false;
            }

            if (($body['action'] ?? null) !== $action) {
                return false;
            }

            if (($body['score'] ?? 0) < $this->threshold) {
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}