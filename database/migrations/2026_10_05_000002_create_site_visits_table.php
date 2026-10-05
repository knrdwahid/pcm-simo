<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 64)->nullable()->index();
            $table->string('ip_hash', 64)->index();
            $table->string('path', 255)->index();
            $table->string('url', 2048);
            $table->string('route_name', 64)->nullable()->index();
            $table->string('page_title', 255)->nullable();
            $table->string('referrer', 2048)->nullable();
            $table->string('referrer_type', 32)->default('direct')->index();
            $table->string('referrer_host', 128)->nullable();
            $table->string('device_type', 32)->default('desktop')->index();
            $table->string('browser', 64)->nullable()->index();
            $table->string('platform', 64)->nullable()->index();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('created_at')->index();

            // Composite indexes for fast analytics queries
            $table->index(['created_at', 'device_type']);
            $table->index(['created_at', 'referrer_type']);
            $table->index(['path', 'created_at']);
        });

        // Seed initial realistic historical visits for the past 14 days
        $this->seedInitialVisits();
    }

    /**
     * Seed initial realistic visits for rich first-time analytics display.
     */
    protected function seedInitialVisits(): void
    {
        $articles = DB::table('articles')
            ->select('id', 'title', 'slug', 'views')
            ->where('status', 'published')
            ->get();

        $devices = ['mobile' => 68, 'desktop' => 28, 'tablet' => 4];
        $browsers = [
            'mobile' => ['Chrome Mobile' => 55, 'Safari Mobile' => 25, 'Samsung Browser' => 15, 'Opera Mini' => 5],
            'desktop' => ['Chrome' => 60, 'Edge' => 22, 'Firefox' => 12, 'Safari' => 6],
            'tablet' => ['Safari Mobile' => 60, 'Chrome Mobile' => 40],
        ];
        $platforms = [
            'mobile' => ['Android' => 78, 'iOS' => 22],
            'desktop' => ['Windows' => 84, 'macOS' => 12, 'Linux' => 4],
            'tablet' => ['iOS' => 65, 'Android' => 35],
        ];
        $referrers = [
            ['type' => 'whatsapp', 'host' => 'api.whatsapp.com', 'weight' => 42],
            ['type' => 'direct', 'host' => null, 'weight' => 32],
            ['type' => 'google', 'host' => 'www.google.com', 'weight' => 16],
            ['type' => 'facebook', 'host' => 'm.facebook.com', 'weight' => 7],
            ['type' => 'instagram', 'host' => 'l.instagram.com', 'weight' => 3],
        ];

        $pages = [
            ['path' => '/', 'route_name' => 'home', 'page_title' => 'Beranda Utama PCM Simo', 'weight' => 40],
            ['path' => '/profil-organisasi', 'route_name' => 'organization.profile', 'page_title' => 'Profil Organisasi & Pimpinan Cabang', 'weight' => 25],
        ];

        foreach ($articles as $art) {
            $pages[] = [
                'path' => '/berita/' . $art->slug,
                'route_name' => 'article.show',
                'page_title' => $art->title,
                'weight' => max(5, min(20, (int) round(($art->views ?? 10) / 10))),
            ];
        }

        $records = [];
        $now = now();

        // Generate data distributed across the last 14 days
        for ($daysAgo = 13; $daysAgo >= 0; $daysAgo--) {
            $date = $now->copy()->subDays($daysAgo);
            // Day factor: weekend vs weekday
            $isWeekend = $date->isWeekend();
            $baseVisits = $isWeekend ? rand(35, 60) : rand(45, 90);

            // If today, only generate up to current hour
            $maxHour = $daysAgo === 0 ? (int) $date->format('H') : 23;

            for ($i = 0; $i < $baseVisits; $i++) {
                // Peak hours: 07:00-09:00, 12:00-14:00, 19:00-22:00
                $hour = rand(0, 100) < 65
                    ? [7, 8, 9, 12, 13, 14, 19, 20, 21, 22][array_rand([7, 8, 9, 12, 13, 14, 19, 20, 21, 22])]
                    : rand(5, 23);

                if ($hour > $maxHour) {
                    $hour = rand(0, max(0, $maxHour));
                }

                $minute = rand(0, 59);
                $second = rand(0, 59);
                $visitTime = $date->copy()->setTime($hour, $minute, $second);

                // Pick device
                $randDevice = rand(1, 100);
                $device = $randDevice <= 68 ? 'mobile' : ($randDevice <= 96 ? 'desktop' : 'tablet');

                // Pick browser & platform
                $browserList = $browsers[$device];
                $browser = array_keys($browserList)[rand(0, count($browserList) - 1)];

                $platformList = $platforms[$device];
                $platform = array_keys($platformList)[rand(0, count($platformList) - 1)];

                // Pick referrer
                $ref = $referrers[array_rand($referrers)];

                // Pick page
                $page = $pages[array_rand($pages)];

                $ipSuffix = rand(1, 40); // Simulate around 40 unique visitors per day
                $ipHash = hash('sha256', "simulated-user-{$daysAgo}-{$ipSuffix}");

                $records[] = [
                    'session_id' => 'sess_' . substr($ipHash, 0, 16),
                    'ip_hash' => $ipHash,
                    'path' => $page['path'],
                    'url' => 'http://pcmsimo.test' . $page['path'],
                    'route_name' => $page['route_name'],
                    'page_title' => $page['page_title'],
                    'referrer' => $ref['host'] ? 'https://' . $ref['host'] . '/' : null,
                    'referrer_type' => $ref['type'],
                    'referrer_host' => $ref['host'],
                    'device_type' => $device,
                    'browser' => $browser,
                    'platform' => $platform,
                    'user_agent' => "Mozilla/5.0 ({$platform}) {$browser}",
                    'created_at' => $visitTime,
                ];

                if (count($records) >= 200) {
                    DB::table('site_visits')->insert($records);
                    $records = [];
                }
            }
        }

        if (! empty($records)) {
            DB::table('site_visits')->insert($records);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};
