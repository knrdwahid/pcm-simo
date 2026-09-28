<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Aum;
use App\Models\Category;
use App\Models\Official;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PublicController extends Controller
{
    // Koordinat Simo, Boyolali
    private const SIMO_LAT = -7.4625;
    private const SIMO_LON = 110.6783;
    private const SIMO_ELEV = 200;
    private const TIMEZONE = 7;
    private const IHTIYAT = 2; // menit ihtiyat sesuai Muhammadiyah

    /**
     * Ambil jadwal shalat dari Hisabmu.org (KHGT Muhammadiyah)
     * Dengan caching 6 jam dan fallback ke data statis
     */
    private function getPrayerSchedule(): array
    {
        $today = now()->timezone('Asia/Jakarta')->format('Y-m-d');
        $cacheKey = "prayer_schedule_{$today}";

        return Cache::remember($cacheKey, 6 * 60 * 60, function () use ($today) {
            try {
                $response = Http::timeout(10)->get('https://hisabmu.org/api/jadwal-sholat', [
                    'date' => $today,
                    'lat' => self::SIMO_LAT,
                    'lon' => self::SIMO_LON,
                    'tz' => self::TIMEZONE,
                    'elev' => self::SIMO_ELEV,
                    'ihtiyat' => self::IHTIYAT,
                    'accuracy' => 'menit',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $times = $data['times'] ?? [];

                    return [
                        'source' => 'hisabmu',
                        'date' => $today,
                        'times' => [
                            ['name' => 'Subuh',   'time' => $times['subuh'] ?? '04:18',   'icon' => 'mdi-weather-sunset-up'],
                            ['name' => 'Syuruq',  'time' => $times['syuruq'] ?? '05:23',  'icon' => 'mdi-weather-sunny'],
                            ['name' => 'Dzuhur',  'time' => $times['zuhur'] ?? '11:33',   'icon' => 'mdi-white-balance-sunny'],
                            ['name' => 'Ashar',   'time' => $times['asar'] ?? '14:45',    'icon' => 'mdi-weather-sunny-alert'],
                            ['name' => 'Maghrib', 'time' => $times['maghrib'] ?? '17:37', 'icon' => 'mdi-weather-sunset-down'],
                            ['name' => 'Isya',    'time' => $times['isya'] ?? '18:47',    'icon' => 'mdi-weather-night'],
                        ],
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('Hisabmu API failed: ' . $e->getMessage());
            }

            // Fallback statis jika API gagal
            return $this->getStaticPrayerSchedule($today);
        });
    }

    /**
     * Fallback data statis jika API Hisabmu gagal
     */
    private function getStaticPrayerSchedule(string $date): array
    {
        return [
            'source' => 'static',
            'date' => $date,
            'times' => [
                ['name' => 'Subuh',   'time' => '04:18', 'icon' => 'mdi-weather-sunset-up'],
                ['name' => 'Syuruq',  'time' => '05:23', 'icon' => 'mdi-weather-sunny'],
                ['name' => 'Dzuhur',  'time' => '11:33', 'icon' => 'mdi-white-balance-sunny'],
                ['name' => 'Ashar',   'time' => '14:45', 'icon' => 'mdi-weather-sunny-alert'],
                ['name' => 'Maghrib', 'time' => '17:37', 'icon' => 'mdi-weather-sunset-down'],
                ['name' => 'Isya',    'time' => '18:47', 'icon' => 'mdi-weather-night'],
            ],
        ];
    }

    public function index(Request $request): Response
    {
        $selectedCategory = $request->query('kategori');
        $search = $request->query('cari');

        $articlesQuery = Article::with('category')
            ->where('status', 'published')
            ->latest('published_at');

        if ($selectedCategory && $selectedCategory !== 'semua') {
            $articlesQuery->whereHas('category', function ($q) use ($selectedCategory) {
                $q->where('slug', $selectedCategory);
            });
        }

        if ($search) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $articlesQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $articles = $articlesQuery->get();

        $featuredArticles = Article::with('category')
            ->where('status', 'published')
            ->where('is_featured', true)
            ->latest('published_at')
            ->take(4)
            ->get();

        if ($featuredArticles->isEmpty()) {
            $featuredArticles = $articles->take(3);
        }

        $categories = Category::withCount(['articles' => function ($q) {
            $q->where('status', 'published');
        }])->get();

        $officials = $this->getOfficials();
        $aums = Aum::all();

        // Jadwal Shalat KHGT Muhammadiyah via Hisabmu API
        $prayerData = $this->getPrayerSchedule();

        return Inertia::render('Welcome', [
            'appName' => 'PCM Simo',
            'articles' => $articles,
            'featuredArticles' => $featuredArticles,
            'categories' => $categories,
            'officials' => $officials,
            'aums' => $aums,
            'prayerSchedule' => $prayerData['times'],
            'prayerSource' => $prayerData['source'],
            'prayerDate' => $prayerData['date'],
            'filters' => [
                'kategori' => $selectedCategory ?? 'semua',
                'cari' => $search ?? '',
            ],
        ]);
    }

    public function showArticle(string $slug): Response
    {
        $article = Article::with(['category', 'user'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Increment views (session-protected to prevent manipulation)
        $viewedKey = 'viewed_article_' . $article->id;
        if (!session()->has($viewedKey)) {
            $article->increment('views');
            session()->put($viewedKey, true);
        }

        $relatedArticles = Article::with('category')
            ->where('status', 'published')
            ->where('id', '!=', $article->id)
            ->where('category_id', $article->category_id)
            ->latest('published_at')
            ->take(4)
            ->get();

        if ($relatedArticles->isEmpty()) {
            $relatedArticles = Article::with('category')
                ->where('status', 'published')
                ->where('id', '!=', $article->id)
                ->latest('published_at')
                ->take(4)
                ->get();
        }

        $popularArticles = Article::with('category')
            ->where('status', 'published')
            ->where('id', '!=', $article->id)
            ->orderByDesc('views')
            ->take(5)
            ->get();

        $categories = Category::withCount(['articles' => function ($q) {
            $q->where('status', 'published');
        }])->get();

        // Jadwal Shalat KHGT Muhammadiyah via Hisabmu API
        $prayerData = $this->getPrayerSchedule();

        return Inertia::render('ArticleDetail', [
            'article' => $article,
            'relatedArticles' => $relatedArticles,
            'popularArticles' => $popularArticles,
            'categories' => $categories,
            'prayerSchedule' => $prayerData['times'],
            'prayerSource' => $prayerData['source'],
            'prayerDate' => $prayerData['date'],
        ]);
    }

    public function organizationProfile(): Response
    {
        $officials = $this->getOfficials();
        $aums = Aum::all();
        $prayerData = $this->getPrayerSchedule();

        return Inertia::render('OrganizationProfile', [
            'appName' => 'PCM Simo',
            'officials' => $officials,
            'aums' => $aums,
            'prayerSchedule' => $prayerData['times'],
            'prayerSource' => $prayerData['source'],
            'prayerDate' => $prayerData['date'],
            'skInfo' => [
                'nomor' => '100 / KEP / III.0 / D / 2023',
                'tentang' => 'Penetapan Ketua dan Anggota Pimpinan Cabang Muhammadiyah Simo Periode 2023 - 2028',
                'penerbit' => 'Pimpinan Daerah Muhammadiyah Boyolali',
                'tanggal_hijriyah' => '15 Rabiul Akhir 1445 H',
                'tanggal_masehi' => '30 Oktober 2023 M',
                'surat_permohonan' => 'Nomor 064/IV.0/A/2023 tanggal 20 Oktober 2023 M / 5 Robiul Akhir 1445 H',
                'ketua_pdm' => 'Drs. H. Ali Muhson, M.Ag., M.PdI., M.H., M.M.',
                'nbm_ketua' => '772695',
                'sekretaris_pdm' => 'Drs. H. Aminudin Aziz',
                'nbm_sekretaris' => '919303',
                'periode' => '2023 - 2028',
            ],
        ]);
    }

    /**
     * Dapatkan daftar Pengurus PCM Simo Periode 2023 - 2028 (SK PDM Boyolali No. 100/KEP/III.0/D/2023).
     * Otomatis menyinkronkan data resmi jika database di hosting masih menyimpan data dummy lama.
     */
    private function getOfficials()
    {
        $authenticOfficials = [
            [
                'name' => 'H. Sholihin, S.Pd',
                'position' => 'Ketua PCM Simo',
                'period' => '2023 - 2028',
                'image_url' => null,
                'sort_order' => 1,
            ],
            [
                'name' => 'Sayyaf, S.PdI',
                'position' => 'Anggota Pimpinan Cabang',
                'period' => '2023 - 2028',
                'image_url' => null,
                'sort_order' => 2,
            ],
            [
                'name' => 'Syarif Widodo, M.PdI',
                'position' => 'Anggota Pimpinan Cabang',
                'period' => '2023 - 2028',
                'image_url' => null,
                'sort_order' => 3,
            ],
            [
                'name' => 'Drs. Mukridin',
                'position' => 'Anggota Pimpinan Cabang',
                'period' => '2023 - 2028',
                'image_url' => null,
                'sort_order' => 4,
            ],
            [
                'name' => 'Drs. Qomarudin',
                'position' => 'Anggota Pimpinan Cabang',
                'period' => '2023 - 2028',
                'image_url' => null,
                'sort_order' => 5,
            ],
            [
                'name' => 'Mushowir, S.Ag., S.Kom',
                'position' => 'Anggota Pimpinan Cabang',
                'period' => '2023 - 2028',
                'image_url' => null,
                'sort_order' => 6,
            ],
            [
                'name' => 'Suryani, S.Si., S.H',
                'position' => 'Anggota Pimpinan Cabang',
                'period' => '2023 - 2028',
                'image_url' => null,
                'sort_order' => 7,
            ],
            [
                'name' => 'H. Suyono, S.H',
                'position' => 'Anggota Pimpinan Cabang',
                'period' => '2023 - 2028',
                'image_url' => null,
                'sort_order' => 8,
            ],
            [
                'name' => 'Drs. Suramto, M.Pd',
                'position' => 'Anggota Pimpinan Cabang',
                'period' => '2023 - 2028',
                'image_url' => null,
                'sort_order' => 9,
            ],
        ];

        // Periksa apakah database masih berisi data dummy lama
        $hasCorrectKetua = Official::where('sort_order', 1)->where('name', 'like', '%Sholihin%')->exists();
        if (! $hasCorrectKetua || Official::count() !== 9) {
            Official::truncate();
            foreach ($authenticOfficials as $item) {
                Official::create($item);
            }
        }

        return Official::orderBy('sort_order')->get();
    }
}
