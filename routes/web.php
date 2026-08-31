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
use App\Http\Controllers\Admin\SupplyStatusController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StatisticsExportController;
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
Route::redirect('/login', '/masuk');

Route::middleware('guest')->group(function (): void {
    Route::get('/masuk', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/masuk', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:10,1');
});

Route::post('/keluar', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('news-articles', AdminNewsArticleController::class);
    Route::put('news-articles/{newsArticle}/publish', [PublishedNewsArticleController::class, 'update'])->name('news-articles.publish');
    Route::delete('news-articles/{newsArticle}/publish', [PublishedNewsArticleController::class, 'destroy'])->name('news-articles.unpublish');
    Route::get('population-statistic-imports/create', [PopulationStatisticImportController::class, 'create'])->name('population-statistic-imports.create');
    Route::post('population-statistic-imports/preview', [PopulationStatisticImportController::class, 'store'])->middleware('throttle:10,1')->name('population-statistic-imports.preview');
    Route::post('population-statistic-imports', [PopulationStatisticImportConfirmationController::class, 'store'])->middleware('throttle:10,1')->name('population-statistic-imports.store');
    Route::resource('population-statistics', PopulationStatisticController::class);
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
