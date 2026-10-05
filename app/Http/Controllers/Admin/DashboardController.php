<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Aum;
use App\Models\Category;
use App\Models\SiteVisit;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalArticles = Article::count();
        $publishedArticles = Article::where('status', 'published')->count();
        $totalCategories = Category::count();
        $totalAums = Aum::count();
        $totalArticleViews = Article::sum('views');

        // Site visit analytics
        $totalVisits = SiteVisit::count();
        $onlineVisitors = SiteVisit::online(5)->distinct('ip_hash')->count('ip_hash');
        $todayVisits = SiteVisit::today()->count();

        $recentArticles = Article::with('category')
            ->latest()
            ->take(6)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'user' => auth()->user(),
            'stats' => [
                'totalArticles' => $totalArticles,
                'publishedArticles' => $publishedArticles,
                'totalCategories' => $totalCategories,
                'totalAums' => $totalAums,
                'totalViews' => $totalVisits ?: $totalArticleViews,
                'onlineVisitors' => $onlineVisitors,
                'todayVisits' => $todayVisits,
            ],
            'recentArticles' => $recentArticles,
        ]);
    }
}
