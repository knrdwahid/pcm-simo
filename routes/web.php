<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AumController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/profil-organisasi', [PublicController::class, 'organizationProfile'])->name('organization.profile');
Route::get('/organisasi', function () {
    return redirect()->route('organization.profile');
});
Route::get('/berita/{slug}', [PublicController::class, 'showArticle'])->name('article.show');

// Fallback untuk serving file storage jika symlink cPanel hosting bermasalah / nonaktif
Route::get('/storage/{path}', function (string $path) {
    if (str_contains($path, '..') || str_starts_with(basename($path), '.') || str_ends_with(strtolower($path), '.php')) {
        abort(404);
    }

    $storageRoot = realpath(storage_path('app/public'));
    $filePath = realpath(storage_path('app/public/' . $path));

    if (! $filePath || ! $storageRoot || ! str_starts_with($filePath, $storageRoot) || ! file_exists($filePath)) {
        abort(404);
    }

    return response()->file($filePath, [
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('path', '.*');

// Admin Guest Routes
Route::middleware('guest')->prefix('dashboard')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('admin.login.store');
});

// Admin Protected Routes
Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Visitor & Traffic Analytics Monitoring
    Route::get('/monitoring', [AnalyticsController::class, 'index'])->name('admin.monitoring.index');
    Route::get('/monitoring/live', [AnalyticsController::class, 'live'])->name('admin.monitoring.live');

    // News Management
    Route::get('/news', [NewsController::class, 'index'])->name('admin.news.index');
    Route::get('/news/create', [NewsController::class, 'create'])->name('admin.news.create');
    Route::post('/news', [NewsController::class, 'store'])->name('admin.news.store');
    Route::get('/news/{news}/edit', [NewsController::class, 'edit'])->name('admin.news.edit');
    Route::put('/news/{news}', [NewsController::class, 'update'])->name('admin.news.update');
    Route::delete('/news/{news}', [NewsController::class, 'destroy'])->name('admin.news.destroy');
    Route::patch('/news/{news}/toggle-publish', [NewsController::class, 'togglePublish'])->name('admin.news.toggle');
    Route::post('/news/upload-image', [NewsController::class, 'uploadContentImage'])->name('admin.news.upload-image');

    // Categories Management
    Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');

    // AUM, Organisasi, Ortom & Masjid Management
    Route::get('/aum', [AumController::class, 'index'])->name('admin.aum.index');
    Route::post('/aum', [AumController::class, 'store'])->name('admin.aum.store');
    // POST (bukan PUT) agar upload gambar via multipart/form-data berfungsi
    Route::post('/aum/{aum}', [AumController::class, 'update'])->name('admin.aum.update');
    Route::patch('/aum/{aum}/toggle-active', [AumController::class, 'toggleActive'])->name('admin.aum.toggle');
    Route::delete('/aum/{aum}', [AumController::class, 'destroy'])->name('admin.aum.destroy');

    // User & Account Management (Super Admin & Admin Only)
    Route::middleware('role:superadmin,admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    });
});

