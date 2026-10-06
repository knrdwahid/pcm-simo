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
     * Daftar periode filter yang tersedia.
     */
    protected const PERIODS = [
        'today' => 'Hari Ini',
        'yesterday' => 'Kemarin',
        'this_week' => 'Minggu Ini',
        'last_week' => 'Minggu Lalu',
        'last_7_days' => '7 Hari Terakhir',
        'last_30_days' => '30 Hari Terakhir',
        'this_month' => 'Bulan Ini',
        'last_month' => 'Bulan Lalu',
        'this_year' => 'Tahun Ini',
        'last_year' => 'Tahun Lalu',
        'custom' => 'Rentang Kustom',
    ];

    protected const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    protected const MONTHS_FULL = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    protected const DAYS = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

    /**
     * Display the main visitor analytics & monitoring page.
     */
    public function index(Request $request): Response
    {
        $period = $this->resolvePeriod($request);
        [$start, $end] = [$period['start'], $period['end']];

        // ── 1. Ringkasan real-time (tidak terpengaruh filter) ──
        $onlineVisitors = SiteVisit::online(5)->distinct('ip_hash')->count('ip_hash');
        $todayPageviews = SiteVisit::today()->count();
        $todayUnique = SiteVisit::today()->distinct('ip_hash')->count('ip_hash');
        $yesterdayPageviews = SiteVisit::yesterday()->count();
        $yesterdayUnique = SiteVisit::yesterday()->distinct('ip_hash')->count('ip_hash');

        $startOfWeek = now()->startOfWeek(Carbon::MONDAY);
        $weekPageviews = SiteVisit::where('created_at', '>=', $startOfWeek)->count();
        $weekUnique = SiteVisit::where('created_at', '>=', $startOfWeek)->distinct('ip_hash')->count('ip_hash');
        $monthPageviews = SiteVisit::where('created_at', '>=', now()->startOfMonth())->count();
        $totalPageviews = SiteVisit::count();
        $totalUnique = SiteVisit::distinct('ip_hash')->count('ip_hash');

        $growthToday = $this->growth($todayPageviews, $yesterdayPageviews);

        // ── 2. Ringkasan periode terpilih vs periode sebelumnya ──
        $periodMetrics = $this->getPeriodMetrics($period);

        // ── 3. Grafik tren & distribusi jam sesuai periode ──
        $dailyTrend = $this->getTrend($period);
        $hourlyToday = $this->getHourlyDistribution($period);

        // ── 4. Statistik lain sesuai periode ──
        $topPages = $this->rangeQuery($start, $end)
            ->select('path', 'page_title', DB::raw('count(*) as views_count'), DB::raw('count(distinct ip_hash) as unique_count'))
            ->groupBy('path', 'page_title')
            ->orderByDesc('views_count')
            ->limit(8)
            ->get();

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
            'period' => [
                'key' => $period['key'],
                'label' => $period['label'],
                'rangeLabel' => $period['rangeLabel'],
                'compareLabel' => $period['compareLabel'],
                'granularity' => $period['granularity'],
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
                'options' => collect(self::PERIODS)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            ],
            'periodMetrics' => $periodMetrics,
            'dailyTrend' => $dailyTrend,
            'hourlyToday' => $hourlyToday,
            'topPages' => $topPages,
            'deviceStats' => $this->getDeviceStats($start, $end),
            'browserStats' => $this->getBrowserStats($start, $end),
            'referrerStats' => $this->getReferrerStats($start, $end),
            'provinceStats' => $this->getProvinceStats($start, $end),
            'countryStats' => $this->getCountryStats($start, $end),
            'cityStats' => $this->getCityStats($start, $end),
            'recentVisits' => $this->getRecentVisits(15),
        ]);
    }

    /**
     * Real-time polling endpoint for live counters and recent stream.
     */
    public function live(): JsonResponse
    {
        return new JsonResponse([
            'onlineVisitors' => SiteVisit::online(5)->distinct('ip_hash')->count('ip_hash'),
            'todayPageviews' => SiteVisit::today()->count(),
            'todayUnique' => SiteVisit::today()->distinct('ip_hash')->count('ip_hash'),
            'totalPageviews' => SiteVisit::count(),
            'recentVisits' => $this->getRecentVisits(12),
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  PERIODE
    // ─────────────────────────────────────────────────────────────

    /**
     * Tentukan rentang waktu, periode pembanding, dan granularitas grafik.
     */
    protected function resolvePeriod(Request $request): array
    {
        $key = $request->query('period', 'last_7_days');
        if (! array_key_exists($key, self::PERIODS)) {
            $key = 'last_7_days';
        }

        $now = now();

        switch ($key) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                $prevStart = $start->copy()->subDay();
                $prevEnd = $end->copy()->subDay();
                $compare = 'kemarin';
                break;

            case 'yesterday':
                $start = $now->copy()->subDay()->startOfDay();
                $end = $now->copy()->subDay()->endOfDay();
                $prevStart = $start->copy()->subDay();
                $prevEnd = $end->copy()->subDay();
                $compare = 'hari sebelumnya';
                break;

            case 'this_week':
                $start = $now->copy()->startOfWeek(Carbon::MONDAY);
                $end = $now->copy()->endOfWeek(Carbon::SUNDAY);
                $prevStart = $start->copy()->subWeek();
                $prevEnd = $end->copy()->subWeek();
                $compare = 'minggu lalu';
                break;

            case 'last_week':
                $start = $now->copy()->subWeek()->startOfWeek(Carbon::MONDAY);
                $end = $now->copy()->subWeek()->endOfWeek(Carbon::SUNDAY);
                $prevStart = $start->copy()->subWeek();
                $prevEnd = $end->copy()->subWeek();
                $compare = '2 minggu lalu';
                break;

            case 'last_30_days':
                $start = $now->copy()->subDays(29)->startOfDay();
                $end = $now->copy()->endOfDay();
                $prevStart = $start->copy()->subDays(30);
                $prevEnd = $start->copy()->subSecond();
                $compare = '30 hari sebelumnya';
                break;

            case 'this_month':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $prevStart = $start->copy()->subMonthNoOverflow()->startOfMonth();
                $prevEnd = $prevStart->copy()->endOfMonth();
                $compare = 'bulan lalu';
                break;

            case 'last_month':
                $start = $now->copy()->subMonthNoOverflow()->startOfMonth();
                $end = $start->copy()->endOfMonth();
                $prevStart = $start->copy()->subMonthNoOverflow()->startOfMonth();
                $prevEnd = $prevStart->copy()->endOfMonth();
                $compare = '2 bulan lalu';
                break;

            case 'this_year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                $prevStart = $start->copy()->subYear();
                $prevEnd = $end->copy()->subYear();
                $compare = 'tahun lalu';
                break;

            case 'last_year':
                $start = $now->copy()->subYear()->startOfYear();
                $end = $start->copy()->endOfYear();
                $prevStart = $start->copy()->subYear();
                $prevEnd = $end->copy()->subYear();
                $compare = '2 tahun lalu';
                break;

            case 'custom':
                try {
                    $start = Carbon::parse($request->query('start', $now->copy()->subDays(6)->toDateString()))->startOfDay();
                    $end = Carbon::parse($request->query('end', $now->toDateString()))->endOfDay();
                } catch (\Throwable $e) {
                    $start = $now->copy()->subDays(6)->startOfDay();
                    $end = $now->copy()->endOfDay();
                }
                if ($start->greaterThan($end)) {
                    [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
                }
                // Batasi maksimal 3 tahun agar query tetap ringan
                if ($start->diffInDays($end) > 1100) {
                    $start = $end->copy()->subDays(1100)->startOfDay();
                }
                $lengthDays = (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
                $prevEnd = $start->copy()->subSecond();
                $prevStart = $start->copy()->subDays($lengthDays);
                $compare = "{$lengthDays} hari sebelumnya";
                break;

            case 'last_7_days':
            default:
                $key = 'last_7_days';
                $start = $now->copy()->subDays(6)->startOfDay();
                $end = $now->copy()->endOfDay();
                $prevStart = $start->copy()->subDays(7);
                $prevEnd = $start->copy()->subSecond();
                $compare = '7 hari sebelumnya';
                break;
        }

        $days = (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
        $granularity = $days <= 1 ? 'hour' : ($days <= 92 ? 'day' : 'month');

        return [
            'key' => $key,
            'label' => self::PERIODS[$key],
            'rangeLabel' => $this->formatRange($start, $end),
            'compareLabel' => $compare,
            'granularity' => $granularity,
            'start' => $start,
            'end' => $end,
            'prevStart' => $prevStart,
            'prevEnd' => $prevEnd,
            'days' => $days,
        ];
    }

    protected function formatRange(Carbon $start, Carbon $end): string
    {
        $fmt = fn (Carbon $d) => $d->day . ' ' . self::MONTHS[$d->month - 1] . ' ' . $d->year;

        if ($start->isSameDay($end)) {
            return $fmt($start);
        }

        return $fmt($start) . ' – ' . $fmt($end);
    }

    protected function rangeQuery(Carbon $start, Carbon $end)
    {
        return SiteVisit::query()->whereBetween('created_at', [$start, $end]);
    }

    protected function growth(int|float $current, int|float $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * KPI ringkasan untuk periode terpilih.
     */
    protected function getPeriodMetrics(array $period): array
    {
        $pageviews = $this->rangeQuery($period['start'], $period['end'])->count();
        $unique = $this->rangeQuery($period['start'], $period['end'])->distinct('ip_hash')->count('ip_hash');
        $prevPageviews = $this->rangeQuery($period['prevStart'], $period['prevEnd'])->count();
        $prevUnique = $this->rangeQuery($period['prevStart'], $period['prevEnd'])->distinct('ip_hash')->count('ip_hash');

        // Hari yang sudah berjalan (untuk periode berjalan seperti "Bulan Ini")
        $effectiveEnd = $period['end']->greaterThan(now()) ? now() : $period['end'];
        $elapsedDays = max(1, (int) $period['start']->copy()->startOfDay()->diffInDays($effectiveEnd->copy()->startOfDay()) + 1);

        return [
            'pageviews' => $pageviews,
            'unique' => $unique,
            'prevPageviews' => $prevPageviews,
            'prevUnique' => $prevUnique,
            'pageviewsGrowth' => $this->growth($pageviews, $prevPageviews),
            'uniqueGrowth' => $this->growth($unique, $prevUnique),
            'avgPerDay' => round($pageviews / $elapsedDays, 1),
            'pagesPerVisitor' => $unique > 0 ? round($pageviews / $unique, 2) : 0,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  GRAFIK
    // ─────────────────────────────────────────────────────────────

    /**
     * Data tren berdasarkan granularitas (per jam / per hari / per bulan).
     */
    protected function getTrend(array $period): array
    {
        $driver = DB::connection()->getDriverName();
        $granularity = $period['granularity'];

        $formats = [
            'hour' => ['%Y-%m-%d %H', 'Y-m-d H'],
            'day' => ['%Y-%m-%d', 'Y-m-d'],
            'month' => ['%Y-%m', 'Y-m'],
        ];
        [$sqlFormat, $phpFormat] = $formats[$granularity];

        $bucketExpr = $driver === 'sqlite'
            ? "strftime('{$sqlFormat}', created_at)"
            : "DATE_FORMAT(created_at, '{$sqlFormat}')";

        $rows = $this->rangeQuery($period['start'], $period['end'])
            ->select(
                DB::raw("{$bucketExpr} as bucket"),
                DB::raw('count(*) as pageviews'),
                DB::raw('count(distinct ip_hash) as unique_visitors')
            )
            ->groupBy(DB::raw($bucketExpr))
            ->get()
            ->keyBy('bucket');

        // Jangan tampilkan bucket di masa depan (misal sisa hari di "Minggu Ini")
        $limit = $period['end']->greaterThan(now()) ? now() : $period['end'];

        $cursor = match ($granularity) {
            'hour' => $period['start']->copy()->startOfHour(),
            'day' => $period['start']->copy()->startOfDay(),
            'month' => $period['start']->copy()->startOfMonth(),
        };

        $result = [];
        $guard = 0;

        while ($cursor->lessThanOrEqualTo($limit) && $guard < 400) {
            $bucket = $cursor->format($phpFormat);
            $row = $rows->get($bucket);

            [$short, $long, $isCurrent] = match ($granularity) {
                'hour' => [
                    $cursor->format('H'),
                    $cursor->format('H:00') . ' – ' . $cursor->format('H:59'),
                    $cursor->isSameHour(now()),
                ],
                'day' => [
                    $period['days'] <= 14 ? self::DAYS[$cursor->dayOfWeek] : (string) $cursor->day,
                    self::DAYS[$cursor->dayOfWeek] . ', ' . $cursor->day . ' ' . self::MONTHS[$cursor->month - 1] . ' ' . $cursor->year,
                    $cursor->isToday(),
                ],
                'month' => [
                    self::MONTHS[$cursor->month - 1],
                    self::MONTHS_FULL[$cursor->month - 1] . ' ' . $cursor->year,
                    $cursor->isSameMonth(now()),
                ],
            };

            $result[] = [
                'date' => $bucket,
                'day' => $short,
                'label' => $long,
                'isToday' => $isCurrent,
                'pageviews' => $row ? (int) $row->pageviews : 0,
                'unique' => $row ? (int) $row->unique_visitors : 0,
            ];

            match ($granularity) {
                'hour' => $cursor->addHour(),
                'day' => $cursor->addDay(),
                'month' => $cursor->addMonthNoOverflow(),
            };
            $guard++;
        }

        return $result;
    }

    /**
     * Distribusi kunjungan per jam (00–23) dalam periode terpilih.
     */
    protected function getHourlyDistribution(array $period): array
    {
        $driver = DB::connection()->getDriverName();
        $hourExpr = $driver === 'sqlite'
            ? "CAST(strftime('%H', created_at) AS INTEGER)"
            : 'HOUR(created_at)';

        $rows = $this->rangeQuery($period['start'], $period['end'])
            ->select(DB::raw("{$hourExpr} as visit_hour"), DB::raw('count(*) as count'))
            ->groupBy(DB::raw($hourExpr))
            ->pluck('count', 'visit_hour');

        $includesNow = now()->between($period['start'], $period['end']);
        $currentHour = (int) now()->format('H');

        $hourly = [];
        for ($h = 0; $h < 24; $h++) {
            $hourly[] = [
                'hour' => $h,
                'label' => sprintf('%02d:00', $h),
                'visits' => (int) ($rows[$h] ?? 0),
                'isCurrent' => $includesNow && $h === $currentHour,
            ];
        }

        return $hourly;
    }

    // ─────────────────────────────────────────────────────────────
    //  SEGMENTASI AUDIENS (mengikuti periode)
    // ─────────────────────────────────────────────────────────────

    protected function getDeviceStats(Carbon $start, Carbon $end): array
    {
        $rows = $this->rangeQuery($start, $end)
            ->select('device_type', DB::raw('count(*) as count'))
            ->groupBy('device_type')
            ->pluck('count', 'device_type');

        $total = max(1, (int) $rows->sum());
        $make = fn ($key) => [
            'count' => (int) ($rows[$key] ?? 0),
            'percentage' => round(((int) ($rows[$key] ?? 0) / $total) * 100, 1),
        ];

        return [
            'total' => $total,
            'mobile' => $make('mobile'),
            'desktop' => $make('desktop'),
            'tablet' => $make('tablet'),
        ];
    }

    protected function getBrowserStats(Carbon $start, Carbon $end): array
    {
        $total = max(1, $this->rangeQuery($start, $end)->count());

        return $this->rangeQuery($start, $end)
            ->select('browser', DB::raw('count(*) as count'))
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'browser' => $row->browser ?: 'Browser Lainnya',
                'count' => (int) $row->count,
                'percentage' => round(($row->count / $total) * 100, 1),
            ])->toArray();
    }

    protected function getReferrerStats(Carbon $start, Carbon $end): array
    {
        $total = max(1, $this->rangeQuery($start, $end)->count());

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

        return $this->rangeQuery($start, $end)
            ->select('referrer_type', DB::raw('count(*) as count'))
            ->groupBy('referrer_type')
            ->orderByDesc('count')
            ->get()
            ->map(function ($row) use ($total, $labels) {
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

    protected function getProvinceStats(Carbon $start, Carbon $end): array
    {
        $total = max(1, $this->rangeQuery($start, $end)->count());
        $palette = ['#006837', '#10b981', '#0ea5e9', '#f59e0b', '#8b5cf6', '#ec4899', '#64748b'];

        return $this->rangeQuery($start, $end)
            ->select('region', DB::raw('count(*) as count'))
            ->whereNotNull('region')
            ->groupBy('region')
            ->orderByDesc('count')
            ->limit(7)
            ->get()
            ->values()
            ->map(fn ($row, $index) => [
                'province' => $row->region ?: 'Lainnya',
                'count' => (int) $row->count,
                'percentage' => round(($row->count / $total) * 100, 1),
                'color' => $palette[$index % count($palette)],
            ])->toArray();
    }

    protected function getCountryStats(Carbon $start, Carbon $end): array
    {
        $total = max(1, $this->rangeQuery($start, $end)->count());

        return $this->rangeQuery($start, $end)
            ->select('country', 'country_code', DB::raw('count(*) as count'))
            ->whereNotNull('country')
            ->groupBy('country', 'country_code')
            ->orderByDesc('count')
            ->limit(6)
            ->get()
            ->map(function ($row) use ($total) {
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

    protected function getCityStats(Carbon $start, Carbon $end): array
    {
        return $this->rangeQuery($start, $end)
            ->select('city', 'region', DB::raw('count(*) as count'))
            ->whereNotNull('city')
            ->groupBy('city', 'region')
            ->orderByDesc('count')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'city' => $row->city ?: 'Lainnya',
                'region' => $row->region ?: '',
                'count' => (int) $row->count,
            ])->toArray();
    }

    /**
     * Get country flag emoji for ISO 2-letter code.
     */
    protected function getCountryFlagEmoji(string $code): string
    {
        $flags = [
            'ID' => '🇮🇩', 'MY' => '🇲🇾', 'SA' => '🇸🇦', 'SG' => '🇸🇬',
            'TW' => '🇹🇼', 'JP' => '🇯🇵', 'KR' => '🇰🇷', 'US' => '🇺🇸',
            'AU' => '🇦🇺', 'GB' => '🇬🇧', 'TR' => '🇹🇷', 'EG' => '🇪🇬',
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
        $diff = abs(now()->diffInSeconds($date));

        if ($diff < 15) return 'Baru saja';
        if ($diff < 60) return (int) $diff . ' dtk lalu';
        $mins = round($diff / 60);
        if ($mins < 60) return "{$mins} mnt lalu";
        $hours = round($mins / 60);
        if ($hours < 24) return "{$hours} jam lalu";
        $days = round($hours / 24);
        return "{$days} hr lalu";
    }
}
