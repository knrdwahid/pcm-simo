<?php

namespace App\Http\Middleware;

use App\Models\Article;
use App\Models\SiteVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackSiteVisit
{
    /**
     * Handle an incoming request and track public visitor traffic.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track successful GET requests on public pages
        if ($request->isMethod('GET') && $response->getStatusCode() === 200) {
            $this->recordVisit($request);
        }

        return $response;
    }

    /**
     * Record visit details asynchronously / safely.
     */
    protected function recordVisit(Request $request): void
    {
        // Ignore dashboard, backend, assets, and system routes
        if ($request->is('dashboard*', 'api*', 'storage*', 'up*', '_ignition*')) {
            return;
        }

        $userAgent = (string) $request->header('User-Agent', '');

        // Ignore common web crawlers and automated bots
        if ($this->isBot($userAgent)) {
            return;
        }

        try {
            $ip = $request->ip() ?? '127.0.0.1';
            $ipHash = hash('sha256', $ip . config('app.key', 'pcm-simo'));
            $sessionId = $request->hasSession() ? $request->session()->getId() : null;
            $path = '/' . ltrim($request->path(), '/');

            // Debounce: prevent duplicate logging within 45 seconds for same path & visitor
            $dedupeKey = 'visit_debounce:' . $ipHash . ':' . md5($path);
            if (Cache::has($dedupeKey)) {
                return;
            }
            Cache::put($dedupeKey, true, now()->addSeconds(45));

            $url = $request->fullUrl();
            $routeName = $request->route() ? $request->route()->getName() : null;
            $pageTitle = $this->determinePageTitle($request, $path, $routeName);

            $referrer = (string) $request->header('referer', '');
            $refData = $this->parseReferrer($referrer, $request->getHost());

            $deviceType = $this->parseDeviceType($userAgent);
            $browser = $this->parseBrowser($userAgent);
            $platform = $this->parsePlatform($userAgent);
            $location = \App\Services\GeoIpService::resolve($request);

            SiteVisit::create([
                'session_id' => $sessionId,
                'ip_hash' => $ipHash,
                'path' => substr($path, 0, 255),
                'url' => substr($url, 0, 2048),
                'route_name' => $routeName ? substr($routeName, 0, 64) : null,
                'page_title' => $pageTitle ? substr($pageTitle, 0, 255) : null,
                'referrer' => $referrer ? substr($referrer, 0, 2048) : null,
                'referrer_type' => $refData['type'],
                'referrer_host' => $refData['host'],
                'device_type' => $deviceType,
                'browser' => $browser,
                'platform' => $platform,
                'user_agent' => substr($userAgent, 0, 512),
                'country' => $location['country'],
                'country_code' => $location['country_code'],
                'region' => $location['region'],
                'city' => $location['city'],
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Silently continue so tracking errors never break public page delivery
            report($e);
        }
    }

    /**
     * Determine a human-readable title for the visited page.
     */
    protected function determinePageTitle(Request $request, string $path, ?string $routeName): string
    {
        if ($path === '/' || $routeName === 'home') {
            return 'Beranda Utama PCM Simo';
        }

        if (str_starts_with($path, '/profil-organisasi') || str_starts_with($path, '/organisasi')) {
            return 'Profil Organisasi & Pimpinan Cabang';
        }

        if (str_starts_with($path, '/berita/')) {
            $slug = basename($path);
            // Fetch article title from DB or humanize slug
            $title = Article::where('slug', $slug)->value('title');
            if ($title) {
                return $title;
            }
            return 'Berita: ' . ucwords(str_replace('-', ' ', $slug));
        }

        return ucwords(trim(str_replace(['/', '-'], ' ', $path))) ?: 'Halaman Publik';
    }

    /**
     * Detect if User Agent belongs to a web crawler or search engine bot.
     */
    protected function isBot(string $userAgent): bool
    {
        if (empty($userAgent)) {
            return true;
        }

        $botPatterns = [
            'bot', 'crawl', 'spider', 'slurp', 'facebookexternalhit',
            'whatsapp', 'telegrambot', 'twitterbot', 'semrush', 'ahrefs',
            'mj12bot', 'dotbot', 'petalbot', 'yandex', 'bingbot', 'googlebot',
        ];

        $lower = strtolower($userAgent);
        foreach ($botPatterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse device type (mobile, tablet, desktop).
     */
    protected function parseDeviceType(string $ua): string
    {
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $ua)) {
            return 'tablet';
        }

        if (preg_match('/(mobile|iphone|ipod|blackberry|iemobile|opera mini|opera mobi|android)/i', $ua)) {
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * Parse browser family name.
     */
    protected function parseBrowser(string $ua): string
    {
        if (preg_match('/SamsungBrowser/i', $ua)) return 'Samsung Browser';
        if (preg_match('/Edge|Edg/i', $ua)) return 'Edge';
        if (preg_match('/Chrome/i', $ua) && !preg_match('/Edg/i', $ua)) return 'Chrome';
        if (preg_match('/Safari/i', $ua) && !preg_match('/Chrome|CriOS/i', $ua)) return 'Safari';
        if (preg_match('/Firefox|FxiOS/i', $ua)) return 'Firefox';
        if (preg_match('/Opera|OPR/i', $ua)) return 'Opera';
        if (preg_match('/UCBrowser/i', $ua)) return 'UC Browser';

        return 'Browser Lainnya';
    }

    /**
     * Parse operating system platform.
     */
    protected function parsePlatform(string $ua): string
    {
        if (preg_match('/Android/i', $ua)) return 'Android';
        if (preg_match('/iPhone|iPad|iPod/i', $ua)) return 'iOS';
        if (preg_match('/Windows/i', $ua)) return 'Windows';
        if (preg_match('/Macintosh|Mac OS X/i', $ua)) return 'macOS';
        if (preg_match('/Linux/i', $ua)) return 'Linux';

        return 'Lainnya';
    }

    /**
     * Categorize traffic referrer source.
     */
    protected function parseReferrer(string $referrer, string $currentHost): array
    {
        if (empty($referrer)) {
            return ['type' => 'direct', 'host' => null];
        }

        $parsed = parse_url($referrer);
        $host = strtolower($parsed['host'] ?? '');

        if (empty($host) || $host === strtolower($currentHost)) {
            return ['type' => 'direct', 'host' => null];
        }

        if (str_contains($host, 'whatsapp') || str_contains($host, 'wa.me')) {
            return ['type' => 'whatsapp', 'host' => $host];
        }

        if (str_contains($host, 'google.')) {
            return ['type' => 'google', 'host' => $host];
        }

        if (str_contains($host, 'facebook.') || str_contains($host, 'fb.com') || str_contains($host, 'fb.me')) {
            return ['type' => 'facebook', 'host' => $host];
        }

        if (str_contains($host, 'instagram.')) {
            return ['type' => 'instagram', 'host' => $host];
        }

        if (str_contains($host, 't.co') || str_contains($host, 'twitter.') || str_contains($host, 'x.com')) {
            return ['type' => 'twitter', 'host' => $host];
        }

        if (str_contains($host, 'youtube.') || str_contains($host, 'tiktok.')) {
            return ['type' => 'social', 'host' => $host];
        }

        return ['type' => 'other', 'host' => substr($host, 0, 128)];
    }
}
