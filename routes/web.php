<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\HomepageAppearanceController;
use App\Http\Controllers\Admin\LeadershipProfileController;
use App\Http\Controllers\Admin\NewsArticleController as AdminNewsArticleController;
use App\Http\Controllers\Admin\PopulationStatisticController;
use App\Http\Controllers\Admin\PopulationStatisticImportConfirmationController;
use App\Http\Controllers\Admin\PopulationStatisticImportController;
use App\Http\Controllers\Admin\PrintingStatusController;
use App\Http\Controllers\Admin\PublishedNewsArticleController;
use App\Http\Controllers\Admin\PublishedThematicStatisticDatasetController;
use App\Http\Controllers\Admin\SupplyStatusController;
use App\Http\Controllers\Admin\ThematicStatisticDatasetController;
use App\Http\Controllers\Admin\ThematicStatisticImportConfirmationController;
use App\Http\Controllers\Admin\ThematicStatisticImportController;
use App\Http\Controllers\Admin\TwoFactorAuthenticationController as AdminTwoFactorAuthenticationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StatisticsExportController;
use App\Http\Controllers\ThematicStatisticController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/pelayanan', [PublicPageController::class, 'services'])->name('services');
Route::get('/profil', [PublicPageController::class, 'profile'])->name('profile');
Route::get('/informasi', [PublicPageController::class, 'information'])->name('information');
Route::get('/dokumen-publik', [PublicPageController::class, 'documents'])->name('documents');
Route::get('/profil-pegawai', [PublicPageController::class, 'employees'])->name('employees');
Route::get('/aplikasi-pegawai', [PublicPageController::class, 'employeeApps'])->name('employee-apps');
Route::get('/kontak', [PublicPageController::class, 'contact'])->name('contact');
Route::get('/berita', [NewsController::class, 'index'])->name('news.index');
Route::get('/berita/{newsArticle}', [NewsController::class, 'show'])->name('news.show');
Route::get('/pencarian', SearchController::class)->name('search');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/data-kependudukan.csv', StatisticsExportController::class)->middleware('throttle:20,1')->name('statistics.export');
Route::get('/data-tematik', ThematicStatisticController::class)->name('thematic-statistics');
Route::redirect('/login', '/masuk');

Route::middleware('guest')->group(function (): void {
    Route::get('/masuk', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/masuk', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/verifikasi-dua-langkah', [TwoFactorChallengeController::class, 'create'])->name('two-factor.challenge');
    Route::post('/verifikasi-dua-langkah', [TwoFactorChallengeController::class, 'store'])->middleware('throttle:10,1')->name('two-factor.challenge.store');
});

Route::post('/keluar', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('keamanan', [AdminTwoFactorAuthenticationController::class, 'edit'])->name('two-factor.edit');
    Route::post('keamanan', [AdminTwoFactorAuthenticationController::class, 'store'])->middleware('throttle:5,1')->name('two-factor.store');
    Route::put('keamanan', [AdminTwoFactorAuthenticationController::class, 'update'])->middleware('throttle:5,1')->name('two-factor.update');
    Route::post('keamanan/recovery-codes', [AdminTwoFactorAuthenticationController::class, 'regenerateRecoveryCodes'])->middleware('throttle:5,1')->name('two-factor.recovery-codes.store');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'super-admin-mfa'])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('news-articles', AdminNewsArticleController::class);
    Route::put('news-articles/{newsArticle}/publish', [PublishedNewsArticleController::class, 'update'])->name('news-articles.publish');
    Route::delete('news-articles/{newsArticle}/publish', [PublishedNewsArticleController::class, 'destroy'])->name('news-articles.unpublish');
    Route::get('population-statistic-imports/create', [PopulationStatisticImportController::class, 'create'])->name('population-statistic-imports.create');
    Route::post('population-statistic-imports/preview', [PopulationStatisticImportController::class, 'store'])->middleware('throttle:10,1')->name('population-statistic-imports.preview');
    Route::post('population-statistic-imports', [PopulationStatisticImportConfirmationController::class, 'store'])->middleware('throttle:10,1')->name('population-statistic-imports.store');
    Route::resource('population-statistics', PopulationStatisticController::class);
    Route::get('thematic-statistic-datasets', [ThematicStatisticDatasetController::class, 'index'])->name('thematic-statistic-datasets.index');
    Route::get('thematic-statistic-imports/create', [ThematicStatisticImportController::class, 'create'])->name('thematic-statistic-imports.create');
    Route::post('thematic-statistic-imports/preview', [ThematicStatisticImportController::class, 'store'])->middleware('throttle:5,1')->name('thematic-statistic-imports.preview');
    Route::post('thematic-statistic-imports', [ThematicStatisticImportConfirmationController::class, 'store'])->middleware('throttle:5,1')->name('thematic-statistic-imports.store');
    Route::put('published-thematic-statistic-datasets/{thematicStatisticDataset}', [PublishedThematicStatisticDatasetController::class, 'update'])->name('published-thematic-statistic-datasets.update');
    Route::resource('employees', EmployeeController::class);
    Route::resource('leadership-profiles', LeadershipProfileController::class);
    Route::get('printing-statuses', [PrintingStatusController::class, 'index'])->name('printing-statuses.index');
    Route::get('printing-statuses/{printingStatus}/edit', [PrintingStatusController::class, 'edit'])->name('printing-statuses.edit');
    Route::put('printing-statuses/{printingStatus}', [PrintingStatusController::class, 'update'])->name('printing-statuses.update');
    Route::get('supply-statuses', [SupplyStatusController::class, 'index'])->name('supply-statuses.index');
    Route::get('supply-statuses/{supplyStatus}/edit', [SupplyStatusController::class, 'edit'])->name('supply-statuses.edit');
    Route::put('supply-statuses/{supplyStatus}', [SupplyStatusController::class, 'update'])->name('supply-statuses.update');
    Route::get('audit-logs', AuditLogController::class)->name('audit-logs');
    Route::get('homepage', [HomepageAppearanceController::class, 'edit'])->name('homepage.edit');
    Route::put('homepage', [HomepageAppearanceController::class, 'update'])->name('homepage.update');
    Route::delete('homepage/background', [HomepageAppearanceController::class, 'destroy'])->name('homepage.background.destroy');
    Route::resource('hero-slides', HeroSlideController::class)->except(['index', 'show']);
});
