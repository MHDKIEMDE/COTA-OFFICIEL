<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PredictionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AffiliateController;
use App\Http\Controllers\Admin\BookmakerController;
use App\Http\Controllers\Admin\CompetitionController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\ReferralController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes - Frontend Public (Blade + Livewire)
|--------------------------------------------------------------------------
*/

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
    Route::get('/auth/facebook', [AuthController::class, 'facebookCallback'])->name('auth.facebook');
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

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

    // Bookmakers
    Route::resource('bookmakers', BookmakerController::class)->parameters(['bookmakers' => 'id']);
    Route::post('/bookmakers/{id}/toggle', [BookmakerController::class, 'toggleActive'])->name('bookmakers.toggle');

    // Statistiques avancées
    Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');

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

    // Paramètres
    Route::get('/settings', [App\Http\Controllers\Admin\AdminSettingsController::class, 'index'])->name('settings.index');

    // Compétitions (Gestion des tendances)
    Route::resource('competitions', CompetitionController::class);
    Route::patch('/competitions/{competition}/toggle-trending', [CompetitionController::class, 'toggleTrending'])->name('competitions.toggle-trending');
    Route::patch('/competitions/{competition}/toggle-active', [CompetitionController::class, 'toggleActive'])->name('competitions.toggle-active');
    Route::patch('/competitions/{competition}/trending-period', [CompetitionController::class, 'setTrendingPeriod'])->name('competitions.trending-period');
    Route::post('/competitions/clear-cache', [CompetitionController::class, 'clearCache'])->name('competitions.clear-cache');
});
