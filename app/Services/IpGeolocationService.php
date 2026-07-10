<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class IpGeolocationService
{
    protected string $defaultLocation = 'Lokasi tidak diketahui';

    public function resolve(string $ipAddress): string
    {
        try {
            $response = Http::timeout(3)
                ->get('https://ipapi.co/'.urlencode($ipAddress).'/json/');

            if (! $response->successful()) {
                return $this->defaultLocation;
            }

            $payload = $response->json();
            $city = trim((string) ($payload['city'] ?? ''));
            $region = trim((string) ($payload['region'] ?? ''));
            $country = trim((string) ($payload['country_name'] ?? ''));

            $parts = array_filter([$city, $region, $country]);
            if (empty($parts)) {
                return $this->defaultLocation;
            }

            return implode(', ', $parts);
        } catch (\Throwable) {
            return $this->defaultLocation;
        }
    }
}
