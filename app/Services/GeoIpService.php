<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoIpService
{
    /**
     * Resolve geolocation details (Country, Code, Region/Province, City) from Request.
     */
    public static function resolve(Request $request): array
    {
        $ip = $request->ip();

        // 1. Check Cloudflare headers first (if proxied via Cloudflare)
        if ($request->header('cf-ipcountry')) {
            $countryCode = strtoupper(trim($request->header('cf-ipcountry')));
            $region = $request->header('cf-region') ?: null;
            $city = $request->header('cf-ipcity') ?: null;

            return [
                'country' => self::countryNameFromCode($countryCode),
                'country_code' => $countryCode,
                'region' => $region ?: 'Jawa Tengah',
                'city' => $city ?: 'Boyolali',
            ];
        }

        // 2. Check for private/local IP ranges
        if (self::isPrivateIp($ip)) {
            return [
                'country' => 'Indonesia',
                'country_code' => 'ID',
                'region' => 'Jawa Tengah',
                'city' => 'Boyolali (Lokal)',
            ];
        }

        // 3. For public IP, perform cached lookup
        return Cache::remember("geoip_{$ip}", 86400 * 7, function () use ($ip) {
            try {
                $response = Http::timeout(1.2)->get("http://ip-api.com/json/{$ip}?fields=status,country,countryCode,regionName,city");
                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['status'] ?? '') === 'success') {
                        return [
                            'country' => $data['country'] ?: 'Indonesia',
                            'country_code' => strtoupper($data['countryCode'] ?: 'ID'),
                            'region' => $data['regionName'] ?: 'Jawa Tengah',
                            'city' => $data['city'] ?: 'Boyolali',
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // Ignore API lookup errors or timeout; fallback gracefully
            }

            return [
                'country' => 'Indonesia',
                'country_code' => 'ID',
                'region' => 'Jawa Tengah',
                'city' => 'Boyolali',
            ];
        });
    }

    /**
     * Check if an IP is local, loopback, or in private address space.
     */
    protected static function isPrivateIp(?string $ip): bool
    {
        if (! $ip || $ip === '127.0.0.1' || $ip === '::1' || $ip === 'localhost') {
            return true;
        }

        return ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * Map common ISO country code to human-friendly name.
     */
    protected static function countryNameFromCode(string $code): string
    {
        $map = [
            'ID' => 'Indonesia',
            'MY' => 'Malaysia',
            'SG' => 'Singapura',
            'SA' => 'Arab Saudi',
            'TW' => 'Taiwan',
            'JP' => 'Jepang',
            'KR' => 'Korea Selatan',
            'US' => 'Amerika Serikat',
            'AU' => 'Australia',
            'GB' => 'Inggris',
            'TR' => 'Turki',
            'EG' => 'Mesir',
        ];

        return $map[$code] ?? $code;
    }
}
