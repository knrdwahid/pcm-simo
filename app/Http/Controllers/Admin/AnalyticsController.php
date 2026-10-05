<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteVisit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    /**
     * Display the main visitor analytics & monitoring page.
     */
    public function index(Request $request): Response
    {
        $today = today();
        $startOfWeek = now()->startOfWeek();
        $startOfMonth = now()->startOfMonth();

        // 1. KPI Counts
        $onlineVisitors = SiteVisit::online(5)->distinct('ip_hash')->count('ip_hash');
        $todayPageviews = SiteVisit::today()->count();
        $todayUnique = SiteVisit::today()->distinct('ip_hash')->count('ip_hash');

        $yesterdayPageviews = SiteVisit::yesterday()->count();
        $yesterdayUnique = SiteVisit::yesterday()->distinct('ip_hash')->count('ip_hash');

        $weekPageviews = SiteVisit::where('created_at', '>=', $startOfWeek)->count();
        $weekUnique = SiteVisit::where('created_at', '>=', $startOfWeek)->distinct('ip_hash')->count('ip_hash');

        $monthPageviews = SiteVisit::where('created_at', '>=', $startOfMonth)->count();
        $totalPageviews = SiteVisit::count();
        $totalUnique = SiteVisit::distinct('ip_hash')->count('ip_hash');

        // Percentage change today vs yesterday
        $growthToday = $yesterdayPageviews > 0
            ? round((($todayPageviews - $yesterdayPageviews) / $yesterdayPageviews) * 100, 1)
            : 0;

        // 2. 14 Days Trend
        $dailyTrend = $this->getDailyTrend(14);

        // 3. Hourly Visits Today (00:00 - 23:00)
        $hourlyToday = $this->getHourlyToday();

        // 4. Top Visited Pages
        $topPages = SiteVisit::select('path', 'page_title', DB::raw('count(*) as views_count'), DB::raw('count(distinct ip_hash) as unique_count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('path', 'page_title')
            ->orderByDesc('views_count')
            ->limit(8)
            ->get();

        // 5. Device Breakdown (Last 30 Days)
        $deviceStats = $this->getDeviceStats();

        // 6. Browser Breakdown (Last 30 Days)
        $browserStats = $this->getBrowserStats();

        // 7. Referrer / Traffic Sources (Last 30 Days)
        $referrerStats = $this->getReferrerStats();

        // 8. Geo & Zonasi (Wilayah Provinsi, Kota & Negara)
        $provinceStats = $this->getProvinceStats();
        $countryStats = $this->getCountryStats();
        $cityStats = $this->getCityStats();

        // 9. Recent Visits Stream
        $recentVisits = $this->getRecentVisits(15);

        return Inertia::render('Admin/Monitoring/Index', [
            'metrics' => [
                'onlineVisitors' => $onlineVisitors,
                'todayPageviews' => $todayPageviews,
                'todayUnique' => $todayUnique,
                'yesterdayPageviews' => $yesterdayPageviews,
                'yesterdayUnique' => $yesterdayUnique,
                'growthToday' => $growthToday,
                'weekPageviews' => $weekPageviews,
                'weekUnique' => $weekUnique,
                'monthPageviews' => $monthPageviews,
                'totalPageviews' => $totalPageviews,
                'totalUnique' => $totalUnique,
                'timestamp' => now()->format('H:i:s'),
            ],
            'dailyTrend' => $dailyTrend,
            'hourlyToday' => $hourlyToday,
            'topPages' => $topPages,
            'deviceStats' => $deviceStats,
            'browserStats' => $browserStats,
            'referrerStats' => $referrerStats,
            'provinceStats' => $provinceStats,
            'countryStats' => $countryStats,
            'cityStats' => $cityStats,
            'recentVisits' => $recentVisits,
        ]);
    }

    /**
     * Real-time polling endpoint for live counters and recent stream.
     */
    public function live(): JsonResponse
    {
        $onlineVisitors = SiteVisit::online(5)->distinct('ip_hash')->count('ip_hash');
        $todayPageviews = SiteVisit::today()->count();
        $todayUnique = SiteVisit::today()->distinct('ip_hash')->count('ip_hash');
        $totalPageviews = SiteVisit::count();

        $recentVisits = $this->getRecentVisits(12);

        return response()->json([
            'onlineVisitors' => $onlineVisitors,
            'todayPageviews' => $todayPageviews,
            'todayUnique' => $todayUnique,
            'totalPageviews' => $totalPageviews,
            'recentVisits' => $recentVisits,
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Compute daily visits & unique visitors for the past N days.
     */
    protected function getDailyTrend(int $days = 14): array
    {
        $startDate = now()->subDays($days - 1)->startOfDay();
        $driver = DB::connection()->getDriverName();
        $dateExpr = $driver === 'sqlite' ? "strftime('%Y-%m-%d', created_at)" : "DATE(created_at)";

        $rows = SiteVisit::select(
            DB::raw("{$dateExpr} as visit_date"),
            DB::raw('count(*) as pageviews'),
            DB::raw('count(distinct ip_hash) as unique_visitors')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy(DB::raw($dateExpr))
            ->get()
            ->keyBy('visit_date');

        $result = [];
        $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

        for ($i = $days - 1; $i >= 0; $i--) {
            $current = now()->subDays($i);
            $dateStr = $current->format('Y-m-d');
            $row = $rows->get($dateStr);

            $result[] = [
                'date' => $dateStr,
                'label' => $current->format('d M'),
                'day' => $dayNames[(int) $current->format('w')],
                'isToday' => $i === 0,
                'pageviews' => $row ? (int) $row->pageviews : 0,
                'unique' => $row ? (int) $row->unique_visitors : 0,
            ];
        }

        return $result;
    }

    /**
     * Compute hourly distribution for today (00:00 to 23:00).
     */
    protected function getHourlyToday(): array
    {
        $driver = DB::connection()->getDriverName();
        $hourExpr = $driver === 'sqlite'
            ? "CAST(strftime('%H', created_at) AS INTEGER)"
            : "HOUR(created_at)";

        $rows = SiteVisit::select(
            DB::raw("{$hourExpr} as visit_hour"),
            DB::raw('count(*) as count')
        )
            ->whereDate('created_at', today())
            ->groupBy(DB::raw($hourExpr))
            ->pluck('count', 'visit_hour');

        $currentHour = (int) now()->format('H');
        $hourly = [];

        for ($h = 0; $h < 24; $h++) {
            $hourly[] = [
                'hour' => $h,
                'label' => sprintf('%02d:00', $h),
                'visits' => (int) ($rows[$h] ?? 0),
                'isCurrent' => $h === $currentHour,
            ];
        }

        return $hourly;
    }

    /**
     * Compute device category breakdown.
     */
    protected function getDeviceStats(): array
    {
        $total = SiteVisit::where('created_at', '>=', now()->subDays(30))->count();
        if ($total === 0) $total = 1;

        $rows = SiteVisit::select('device_type', DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('device_type')
            ->pluck('count', 'device_type');

        $mobile = (int) ($rows['mobile'] ?? 0);
        $desktop = (int) ($rows['desktop'] ?? 0);
        $tablet = (int) ($rows['tablet'] ?? 0);

        return [
            'total' => $total,
            'mobile' => [
                'count' => $mobile,
                'percentage' => round(($mobile / $total) * 100, 1),
            ],
            'desktop' => [
                'count' => $desktop,
                'percentage' => round(($desktop / $total) * 100, 1),
            ],
            'tablet' => [
                'count' => $tablet,
                'percentage' => round(($tablet / $total) * 100, 1),
            ],
        ];
    }

    /**
     * Compute browser breakdown.
     */
    protected function getBrowserStats(): array
    {
        $total = SiteVisit::where('created_at', '>=', now()->subDays(30))->count();
        if ($total === 0) $total = 1;

        $rows = SiteVisit::select('browser', DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return $rows->map(function ($row) use ($total) {
            return [
                'browser' => $row->browser ?: 'Browser Lainnya',
                'count' => (int) $row->count,
                'percentage' => round(($row->count / $total) * 100, 1),
            ];
        })->toArray();
    }

    /**
     * Compute referrer sources breakdown.
     */
    protected function getReferrerStats(): array
    {
        $total = SiteVisit::where('created_at', '>=', now()->subDays(30))->count();
        if ($total === 0) $total = 1;

        $labels = [
            'direct' => ['name' => 'Langsung (Direct URL)', 'icon' => 'mdi-link-variant', 'color' => '#006837'],
            'whatsapp' => ['name' => 'WhatsApp Broadcast', 'icon' => 'mdi-whatsapp', 'color' => '#25d366'],
            'google' => ['name' => 'Google Pencarian', 'icon' => 'mdi-google', 'color' => '#4285f4'],
            'facebook' => ['name' => 'Facebook Medsos', 'icon' => 'mdi-facebook', 'color' => '#1877f2'],
            'instagram' => ['name' => 'Instagram', 'icon' => 'mdi-instagram', 'color' => '#e1306c'],
            'twitter' => ['name' => 'X (Twitter)', 'icon' => 'mdi-twitter', 'color' => '#0f172a'],
            'social' => ['name' => 'Media Sosial Lainnya', 'icon' => 'mdi-share-variant-outline', 'color' => '#8b5cf6'],
            'other' => ['name' => 'Tautan Luar / Lainnya', 'icon' => 'mdi-earth', 'color' => '#64748b'],
        ];

        $rows = SiteVisit::select('referrer_type', DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('referrer_type')
            ->orderByDesc('count')
            ->get();

        return $rows->map(function ($row) use ($total, $labels) {
            $type = $row->referrer_type ?? 'other';
            $meta = $labels[$type] ?? $labels['other'];

            return [
                'type' => $type,
                'name' => $meta['name'],
                'icon' => $meta['icon'],
                'color' => $meta['color'],
                'count' => (int) $row->count,
                'percentage' => round(($row->count / $total) * 100, 1),
            ];
        })->toArray();
    }

    /**
     * Compute province breakdown (Wilayah Provinsi di Indonesia).
     */
    protected function getProvinceStats(): array
    {
        $total = SiteVisit::where('created_at', '>=', now()->subDays(30))->count();
        if ($total === 0) $total = 1;

        $rows = SiteVisit::select('region', DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('region')
            ->groupBy('region')
            ->orderByDesc('count')
            ->limit(7)
            ->get();

        $palette = ['#006837', '#10b981', '#0ea5e9', '#f59e0b', '#8b5cf6', '#ec4899', '#64748b'];

        return $rows->map(function ($row, $index) use ($total, $palette) {
            return [
                'province' => $row->region ?: 'Lainnya',
                'count' => (int) $row->count,
                'percentage' => round(($row->count / $total) * 100, 1),
                'color' => $palette[$index % count($palette)],
            ];
        })->toArray();
    }

    /**
     * Compute country breakdown (Zonasi Negara).
     */
    protected function getCountryStats(): array
    {
        $total = SiteVisit::where('created_at', '>=', now()->subDays(30))->count();
        if ($total === 0) $total = 1;

        $rows = SiteVisit::select('country', 'country_code', DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('country')
            ->groupBy('country', 'country_code')
            ->orderByDesc('count')
            ->limit(6)
            ->get();

        return $rows->map(function ($row) use ($total) {
            $code = strtoupper($row->country_code ?: 'ID');
            return [
                'country' => $row->country ?: 'Indonesia',
                'code' => $code,
                'flag' => $this->getCountryFlagEmoji($code),
                'count' => (int) $row->count,
                'percentage' => round(($row->count / $total) * 100, 1),
            ];
        })->toArray();
    }

    /**
     * Compute city breakdown (Kota / Kabupaten Teratas).
     */
    protected function getCityStats(): array
    {
        $rows = SiteVisit::select('city', 'region', DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('city')
            ->groupBy('city', 'region')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        return $rows->map(function ($row) {
            return [
                'city' => $row->city ?: 'Lainnya',
                'region' => $row->region ?: '',
                'count' => (int) $row->count,
            ];
        })->toArray();
    }

    /**
     * Get country flag emoji for ISO 2-letter code.
     */
    protected function getCountryFlagEmoji(string $code): string
    {
        $flags = [
            'ID' => '🇮🇩',
            'MY' => '🇲🇾',
            'SA' => '🇸🇦',
            'SG' => '🇸🇬',
            'TW' => '🇹🇼',
            'JP' => '🇯🇵',
            'KR' => '🇰🇷',
            'US' => '🇺🇸',
            'AU' => '🇦🇺',
            'GB' => '🇬🇧',
            'TR' => '🇹🇷',
            'EG' => '🇪🇬',
        ];

        return $flags[$code] ?? '🌐';
    }

    /**
     * Format recent visit items with location badges.
     */
    protected function getRecentVisits(int $limit = 15): array
    {
        return SiteVisit::latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($visit) {
                $code = strtoupper($visit->country_code ?: 'ID');
                $flag = $this->getCountryFlagEmoji($code);
                $location = $visit->region
                    ? ($visit->city ? "{$visit->region} ({$visit->city})" : $visit->region)
                    : ($visit->country ?: 'Indonesia');

                return [
                    'id' => $visit->id,
                    'path' => $visit->path,
                    'page_title' => $visit->page_title ?: $visit->path,
                    'device_type' => $visit->device_type,
                    'browser' => $visit->browser,
                    'platform' => $visit->platform,
                    'referrer_type' => $visit->referrer_type,
                    'referrer_host' => $visit->referrer_host,
                    'country' => $visit->country ?: 'Indonesia',
                    'country_code' => $code,
                    'flag' => $flag,
                    'region' => $visit->region ?: 'Jawa Tengah',
                    'city' => $visit->city ?: 'Boyolali',
                    'location_label' => "{$flag} {$location}",
                    'created_at_human' => $this->formatDiffHuman($visit->created_at),
                    'time' => $visit->created_at ? $visit->created_at->format('H:i:s') : '-',
                ];
            })
            ->toArray();
    }

    /**
     * Format Indonesian diffForHumans.
     */
    protected function formatDiffHuman(?Carbon $date): string
    {
        if (! $date) return '-';
        $diff = now()->diffInSeconds($date);

        if ($diff < 15) return 'Baru saja';
        if ($diff < 60) return "{$diff} dtk lalu";
        $mins = round($diff / 60);
        if ($mins < 60) return "{$mins} mnt lalu";
        $hours = round($mins / 60);
        if ($hours < 24) return "{$hours} jam lalu";
        $days = round($hours / 24);
        return "{$days} hr lalu";
    }
}
