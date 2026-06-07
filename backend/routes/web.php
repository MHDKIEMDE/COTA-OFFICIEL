<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PredictionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AffiliateController;
use App\Http\Controllers\Admin\BookmakerController;
use App\Http\Controllers\Admin\AdminBookmakerController;
use App\Http\Controllers\Admin\BookmakerCandidateController;
use App\Http\Controllers\Admin\BookmakerBlogController;
use App\Http\Controllers\Admin\CompetitionController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\ReferralController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\Admin\ApiMonitorController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\SocialAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes - Frontend Public (Blade + Livewire)
|--------------------------------------------------------------------------
*/

// ── Well-Known (App Links Android + Universal Links iOS) ─────────────────────
Route::get('/.well-known/assetlinks.json', function () {
    return response()->file(public_path('.well-known/assetlinks.json'), ['Content-Type' => 'application/json']);
});
Route::get('/.well-known/apple-app-site-association', function () {
    return response()->file(public_path('.well-known/apple-app-site-association'), ['Content-Type' => 'application/json']);
});

// ── Liens trackés influenceurs — /r/{slug} ───────────────────────────────────
Route::get('/r/{slug}', [\App\Http\Controllers\Api\InfluencerController::class, 'redirect'])
    ->name('influencer.redirect');

// ── Page Politique de confidentialité ────────────────────────────────────────
Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

// Public pages (Guest mode allowed)
Route::middleware(['web'])->group(function () {
    
    // Home / Dashboard
    Route::get('/', [PageController::class, 'home'])->name('home');
    
    // Live / Direct
    Route::get('/live', [PageController::class, 'live'])->name('live');
    
    // Predictions
    Route::get('/predictions', [PageController::class, 'predictions'])->name('predictions.index');
    Route::get('/predictions/{prediction}', [PageController::class, 'showPrediction'])->name('predictions.show');
    
    // Favorites
    Route::get('/favorites', [PageController::class, 'favorites'])->name('favorites');
    
    // History & Statistics (show page even for guests, but with limited data)
    Route::get('/history', [PageController::class, 'history'])->name('history');
    Route::get('/statistics', [PageController::class, 'statistics'])->name('statistics');
    
    // Subscription (public - show plans)
    Route::get('/subscription', [PageController::class, 'subscription'])->name('subscription');
    
    // Referral (show page for guests too)
    Route::get('/referral', [PageController::class, 'referral'])->name('referral');
    
    // Competitions / Leagues
    Route::get('/competitions', [PageController::class, 'competitions'])->name('competitions');
});

// Auth routes (guest only)
Route::middleware(['web', 'guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('auth.verify-otp');

    // Social OAuth — Google & Facebook
    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('auth.social.redirect');
    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('auth.social.callback');
});

// Protected routes (auth required)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/profile', [PageController::class, 'profile'])->name('profile');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Blade)
|--------------------------------------------------------------------------
*/

// Auth Admin (sans middleware)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

// Panel Admin (avec middleware super_admin)
Route::prefix('admin')->name('admin.')->middleware(['super_admin'])->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::redirect('/dashboard', '/admin')->name('dashboard.index');

    // Pronostics
    Route::resource('predictions', PredictionController::class);
    Route::patch('/predictions/{prediction}/status', [PredictionController::class, 'updateStatus'])->name('predictions.updateStatus');
    Route::post('/predictions/bulk-status', [PredictionController::class, 'bulkUpdateStatus'])->name('predictions.bulkStatus');

    // Utilisateurs
    Route::resource('users', UserController::class)->except(['create', 'store']);
    Route::post('/users/{user}/add-premium', [UserController::class, 'addPremiumDays'])->name('users.addPremium');
    Route::post('/users/{user}/lifetime-premium', [UserController::class, 'grantLifetimePremium'])->name('users.lifetimePremium');
    Route::post('/users/{user}/revoke-premium', [UserController::class, 'revokePremium'])->name('users.revokePremium');
    Route::get('/users-export', [UserController::class, 'export'])->name('users.export');

    // Affiliations
    Route::resource('affiliates', AffiliateController::class)->except(['create', 'store', 'edit', 'update']);
    Route::post('/affiliates/{affiliation}/verify', [AffiliateController::class, 'verify'])->name('affiliates.verify');
    Route::post('/affiliates/{affiliation}/reject', [AffiliateController::class, 'reject'])->name('affiliates.reject');
    Route::post('/affiliates/bulk-verify', [AffiliateController::class, 'bulkVerify'])->name('affiliates.bulkVerify');

    // Bookmakers — CRUD complet (vues Blade)
    Route::get('/bookmakers', [AdminBookmakerController::class, 'listView'])->name('admin.bookmakers.list');
    Route::get('/bookmakers/{bookmaker}/edit', [AdminBookmakerController::class, 'editView'])->name('admin.bookmakers.edit');
    Route::put('/bookmakers/{bookmaker}', [AdminBookmakerController::class, 'updateFull'])->name('admin.bookmakers.update');
    Route::post('/bookmakers/{bookmaker}/toggle', [AdminBookmakerController::class, 'toggleActive'])->name('admin.bookmakers.toggle');
    // (garde l'ancienne resource pour l'API JSON admin)
    Route::resource('bookmakers-api', BookmakerController::class)->parameters(['bookmakers-api' => 'id']);

    // Bookmakers — Blogs marketing (guides, vidéos, promos, tutoriels)
    Route::resource('bookmaker-blogs', BookmakerBlogController::class)
        ->names('admin.bookmaker-blogs')
        ->parameters(['bookmaker-blogs' => 'bookmakerBlog']);
    Route::post('/bookmaker-blogs/{bookmakerBlog}/toggle-featured', [BookmakerBlogController::class, 'toggleFeatured'])
        ->name('admin.bookmaker-blogs.toggle-featured');
    Route::post('/bookmaker-blogs/generate-ai', [BookmakerBlogController::class, 'generateWithAI'])
        ->name('admin.bookmaker-blogs.generate-ai');

    // Bookmakers — Liste d'attente (candidats depuis APIs)
    Route::prefix('bookmaker-candidates')->name('admin.bookmaker-candidates.')->group(function () {
        Route::get('/',                                          [BookmakerCandidateController::class, 'index'])->name('index');
        Route::get('/{candidate}',                              [BookmakerCandidateController::class, 'show'])->name('show');
        Route::post('/fetch',                                   [BookmakerCandidateController::class, 'fetch'])->name('fetch');
        Route::post('/{candidate}/approve',                     [BookmakerCandidateController::class, 'approve'])->name('approve');
        Route::post('/{candidate}/reject',                      [BookmakerCandidateController::class, 'reject'])->name('reject');
        Route::post('/{candidate}/reset',                       [BookmakerCandidateController::class, 'resetStatus'])->name('reset');
        Route::delete('/{candidate}',                           [BookmakerCandidateController::class, 'destroy'])->name('destroy');
    });

    // Statistiques avancées
    Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');
    Route::get('/stats/active-users', [StatsController::class, 'activeUsers'])->name('stats.active-users');
    Route::get('/stats/funnel', [StatsController::class, 'funnel'])->name('stats.funnel');
    Route::get('/stats/segments', [StatsController::class, 'segments'])->name('stats.segments');

    // Monitoring APIs
    Route::get('/api-monitor', [ApiMonitorController::class, 'index'])->name('api-monitor.index');

    // Abonnements
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::post('/subscriptions/grant', [SubscriptionController::class, 'grantManual'])->name('subscriptions.grant');
    Route::patch('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

    // Parrainages
    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');

    // Feedbacks
    Route::get('/feedbacks', [FeedbackController::class, 'index'])->name('feedbacks.index');
    Route::get('/feedbacks/{feedback}', [FeedbackController::class, 'show'])->name('feedbacks.show');
    Route::patch('/feedbacks/{feedback}/respond', [FeedbackController::class, 'respond'])->name('feedbacks.respond');
    Route::patch('/feedbacks/{feedback}/status', [FeedbackController::class, 'updateStatus'])->name('feedbacks.status');
    Route::delete('/feedbacks/{feedback}', [FeedbackController::class, 'destroy'])->name('feedbacks.destroy');

    // Sources actualités RSS
    Route::get('/news-sources', [App\Http\Controllers\Admin\NewsSourceController::class, 'index'])->name('news-sources.index');
    Route::get('/news-sources/create', [App\Http\Controllers\Admin\NewsSourceController::class, 'create'])->name('news-sources.create');
    Route::post('/news-sources', [App\Http\Controllers\Admin\NewsSourceController::class, 'store'])->name('news-sources.store');
    Route::get('/news-sources/{newsSource}/edit', [App\Http\Controllers\Admin\NewsSourceController::class, 'edit'])->name('news-sources.edit');
    Route::put('/news-sources/{newsSource}', [App\Http\Controllers\Admin\NewsSourceController::class, 'update'])->name('news-sources.update');
    Route::delete('/news-sources/{newsSource}', [App\Http\Controllers\Admin\NewsSourceController::class, 'destroy'])->name('news-sources.destroy');
    Route::post('/news-sources/{newsSource}/toggle', [App\Http\Controllers\Admin\NewsSourceController::class, 'toggle'])->name('news-sources.toggle');
    Route::post('/news-sources/{newsSource}/fetch-now', [App\Http\Controllers\Admin\NewsSourceController::class, 'fetchNow'])->name('news-sources.fetch-now');
    Route::post('/news-sources/fetch-all', [App\Http\Controllers\Admin\NewsSourceController::class, 'fetchAll'])->name('news-sources.fetch-all');
    Route::get('/news-sources/{newsSource}/articles', [App\Http\Controllers\Admin\NewsSourceController::class, 'articles'])->name('news-sources.articles');
    Route::post('/news-sources/articles/{article}/toggle', [App\Http\Controllers\Admin\NewsSourceController::class, 'toggleArticle'])->name('news-sources.toggle-article');

    // Coupon IA
    Route::get('/coupon', [App\Http\Controllers\Admin\CouponController::class, 'index'])->name('coupon.index');
    Route::post('/coupon/{id}/publish', [App\Http\Controllers\Admin\CouponController::class, 'publish'])->name('coupon.publish');
    Route::post('/coupon/{id}/unpublish', [App\Http\Controllers\Admin\CouponController::class, 'unpublish'])->name('coupon.unpublish');
    Route::post('/coupon/{id}/status', [App\Http\Controllers\Admin\CouponController::class, 'updateStatus'])->name('coupon.status');
    Route::delete('/coupon/{id}', [App\Http\Controllers\Admin\CouponController::class, 'destroy'])->name('coupon.destroy');

    // Paramètres
    Route::get('/settings', [App\Http\Controllers\Admin\AdminSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/api-source-toggle', [App\Http\Controllers\Admin\AdminSettingsController::class, 'toggleApiSource'])->name('settings.api-source-toggle');

    // Compétitions (Gestion des tendances)
    Route::resource('competitions', CompetitionController::class);
    Route::patch('/competitions/{competition}/toggle-trending', [CompetitionController::class, 'toggleTrending'])->name('competitions.toggle-trending');
    Route::patch('/competitions/{competition}/toggle-active', [CompetitionController::class, 'toggleActive'])->name('competitions.toggle-active');
    Route::patch('/competitions/{competition}/trending-period', [CompetitionController::class, 'setTrendingPeriod'])->name('competitions.trending-period');
    Route::post('/competitions/clear-cache', [CompetitionController::class, 'clearCache'])->name('competitions.clear-cache');
});
