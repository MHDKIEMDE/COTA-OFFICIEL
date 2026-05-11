<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PredictionController;
use App\Http\Controllers\Api\AffiliateController;
use App\Http\Controllers\Api\OddsController;
use App\Http\Controllers\Api\MatchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route de test pour vérifier que l'API fonctionne
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'COTA Backend API is running!',
        'version' => '1.0.0-MVP',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Status des providers football (accessible sans auth pour monitoring)
Route::get('/health/football', function () {
    $chain = app(\App\Services\Football\FootballDataChain::class);
    return response()->json([
        'active_provider' => $chain->activeProvider(),
        'providers'       => $chain->status(),
        'timestamp'       => now()->toIso8601String(),
    ]);
});

// Routes publiques (pas besoin d'authentification)
// Rate limiting strict : 10 requêtes/min pour éviter spam OTP
Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/facebook', [AuthController::class, 'loginWithFacebook']);
});

// Routes des pronostics publiques (accessibles sans authentification - mode invité)
Route::get('/predictions/today', [PredictionController::class, 'today']);
Route::get('/predictions/competitions', [PredictionController::class, 'competitions']);
Route::get('/predictions/search', [PredictionController::class, 'search']);
Route::get('/predictions/welcome-combined', [PredictionController::class, 'welcomeCombined']);

// COTA LIVE - Scores en direct (routes publiques)
Route::get('/matches/live', [MatchController::class, 'live']);
Route::get('/matches/today', [MatchController::class, 'today']);
Route::get('/matches/date/{date}', [MatchController::class, 'byDate'])
    ->where('date', '\d{4}-\d{2}-\d{2}');
Route::get('/matches/{id}', [MatchController::class, 'show']);
Route::get('/matches/{id}/events', [MatchController::class, 'events']);
Route::get('/matches/{id}/stats', [MatchController::class, 'stats']);
Route::get('/matches/{id}/lineups', [MatchController::class, 'lineups']);
Route::get('/matches/{id}/h2h', [MatchController::class, 'h2h']);
Route::get('/standings/{competition}', [MatchController::class, 'standings']);

// Routes publiques pour les cotes bookmakers (pas d'auth requise, pas de stockage backend)
Route::get('/odds/match/{matchId}', [OddsController::class, 'getMatchOdds']);
Route::get('/odds/batch', [OddsController::class, 'getBatchOdds']);
Route::get('/odds/bookmakers', [OddsController::class, 'getBookmakers']);

// Config dynamique (publique - l'app doit charger la config au démarrage)
Route::get('/config/app', [App\Http\Controllers\Api\ConfigController::class, 'getAppConfig']);

// Bookmakers configurables (publique - liste des bookmakers actifs)
Route::get('/bookmakers', [App\Http\Controllers\Api\BookmakerController::class, 'index']);

// Routes protégées des pronostics (nécessitent authentification)
// Note: Placées AVANT /predictions/{id} pour éviter conflit de routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/predictions/history', [PredictionController::class, 'history']);
    Route::get('/predictions/statistics', [PredictionController::class, 'statistics']);
    Route::post('/predictions/feedback', [PredictionController::class, 'feedback']);
    Route::get('/predictions/combined-daily', [PredictionController::class, 'combinedDaily']);
    Route::get('/predictions/coupon', [PredictionController::class, 'coupon']);
});

// Route dynamique pour détails d'une prédiction (doit être APRÈS les routes spécifiques)
Route::get('/predictions/{id}', [PredictionController::class, 'show'])->where('id', '[0-9]+');

// Routes publiques pour abonnements (affichage des plans)
Route::get('/subscriptions/plans', [App\Http\Controllers\Api\SubscriptionController::class, 'getPlans']);

// Routes protégées (nécessitent authentification Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // Authentification
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Abonnements (Paydunya Mobile Money) - nécessitent authentification
    Route::get('/subscriptions/me', [App\Http\Controllers\Api\SubscriptionController::class, 'getMySubscription']);
    Route::post('/subscriptions/purchase', [App\Http\Controllers\Api\SubscriptionController::class, 'initiatePurchase']);
    Route::get('/subscriptions/verify/{token}', [App\Http\Controllers\Api\SubscriptionController::class, 'verifyPayment']);

    // Parrainage
    Route::get('/referrals/stats', [App\Http\Controllers\Api\ReferralController::class, 'getReferralStats']);
    Route::get('/referrals/my-code', [App\Http\Controllers\Api\ReferralController::class, 'getMyReferralCode']);
    Route::get('/referrals/list', [App\Http\Controllers\Api\ReferralController::class, 'listReferrals']);
    Route::post('/referrals/apply', [App\Http\Controllers\Api\ReferralController::class, 'applyReferralCode']);

    // Statistiques
    // TODO: Créer StatisticsController (Semaine 2)
    // Route::get('/statistics/global', [StatisticsController::class, 'global']);
    // Route::get('/statistics/by-competition', [StatisticsController::class, 'byCompetition']);

    // Feedback & Support
    // TODO: Créer FeedbackController (Semaine 3)
    // Route::post('/feedback', [FeedbackController::class, 'store']);
    // Route::get('/faq', [FeedbackController::class, 'faq']);

    // Utilisateur (RGPD)
    Route::get('/user/profile', [App\Http\Controllers\Api\UserController::class, 'profile']);
    Route::put('/user/profile', [App\Http\Controllers\Api\UserController::class, 'update']);
    Route::put('/user/preferences', [App\Http\Controllers\Api\UserController::class, 'updatePreferences']);
    Route::get('/user/data-access', [App\Http\Controllers\Api\UserController::class, 'dataAccess']);
    Route::post('/user/data-export', [App\Http\Controllers\Api\UserController::class, 'exportData']);
    Route::delete('/user/data-delete', [App\Http\Controllers\Api\UserController::class, 'deleteAccount']);

    // Favoris
    Route::get('/favorites', [App\Http\Controllers\Api\FavoriteController::class, 'index']);
    Route::post('/favorites', [App\Http\Controllers\Api\FavoriteController::class, 'store']);
    Route::delete('/favorites/{id}', [App\Http\Controllers\Api\FavoriteController::class, 'destroy']);
    Route::delete('/favorites', [App\Http\Controllers\Api\FavoriteController::class, 'destroyByItem']);
    Route::get('/favorites/check', [App\Http\Controllers\Api\FavoriteController::class, 'check']);

    // Notifications
    Route::post('/notifications/register', [App\Http\Controllers\Api\NotificationController::class, 'register']);
    Route::get('/notifications/settings', [App\Http\Controllers\Api\NotificationController::class, 'getSettings']);
    Route::put('/notifications/settings', [App\Http\Controllers\Api\NotificationController::class, 'updateSettings']);
    Route::delete('/notifications/unregister', [App\Http\Controllers\Api\NotificationController::class, 'unregister']);

    // Tracking clic bookmaker (nécessite auth pour tracker l'utilisateur)
    Route::post('/bookmakers/{id}/click', [App\Http\Controllers\Api\BookmakerController::class, 'trackClick']);
});

// Affiliation Bookmakers (routes protégées)
Route::middleware('auth:sanctum')->prefix('affiliate')->group(function () {
    Route::get('/bookmakers', [AffiliateController::class, 'listBookmakers']);
    Route::get('/link/{bookmaker}', [AffiliateController::class, 'getTrackingLink']);
    Route::get('/status', [AffiliateController::class, 'getStatus']);
    
    // Vérification ID joueur - L'utilisateur soumet son ID bookmaker
    Route::post('/verify-player', [AffiliateController::class, 'verifyPlayerId']);
});

// Routes Admin Affiliation (à protéger avec middleware admin en production)
Route::middleware('auth:sanctum')->prefix('admin/affiliate')->group(function () {
    Route::get('/pending', [AffiliateController::class, 'getPendingVerifications']);
    Route::post('/approve/{id}', [AffiliateController::class, 'approveVerification']);
    Route::post('/reject/{id}', [AffiliateController::class, 'rejectVerification']);
});

// Webhooks (pas d'authentification Sanctum, mais vérification signature/IP)
Route::prefix('webhooks')->group(function () {
    Route::post('/paydunya', [App\Http\Controllers\Api\SubscriptionController::class, 'webhook']);
    
    // Webhook AffiliateControl - accepte GET et POST
    Route::match(['get', 'post'], '/affiliate', [AffiliateController::class, 'handlePostback']);
});
