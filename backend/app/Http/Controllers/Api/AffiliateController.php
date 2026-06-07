<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AffiliationBonus;
use App\Models\User;
use App\Services\AffiliateControlService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Controller pour la gestion des affiliations bookmakers
 * 
 * Intégration avec AffiliateControl:
 * - Génération de liens trackés
 * - Réception des postbacks (webhooks)
 * - Activation des bonus premium
 */
class AffiliateController extends Controller
{
    /**
     * Obtenir un lien d'affiliation tracké pour un bookmaker
     *
     * GET /api/affiliate/link/{bookmaker}
     * 
     * @param Request $request
     * @param string $bookmaker betwinner|1xbet|melbet
     * @return JsonResponse
     */
    public function getTrackingLink(Request $request, string $bookmaker): JsonResponse
    {
        $user = $request->user();

        // Vérifier que le bookmaker est valide
        if (!array_key_exists($bookmaker, AffiliationBonus::BOOKMAKERS)) {
            return response()->json([
                'success' => false,
                'message' => 'Bookmaker invalide',
            ], 400);
        }

        // Vérifier si l'utilisateur a déjà utilisé ce bonus
        if (AffiliationBonus::hasExistingBonus($user->id, $bookmaker)) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà utilisé le bonus pour ce bookmaker',
                'data' => [
                    'already_used' => true,
                ],
            ], 400);
        }

        // Obtenir l'URL de base depuis la config
        $baseUrl = config("affiliates.{$bookmaker}.tracking_url");

        if (!$baseUrl) {
            return response()->json([
                'success' => false,
                'message' => 'Lien d\'affiliation non configuré pour ce bookmaker',
            ], 500);
        }

        // Créer ou récupérer l'enregistrement d'affiliation
        $affiliation = AffiliationBonus::getOrCreateForUser(
            $user->id,
            $bookmaker,
            $baseUrl
        );

        // Enregistrer le clic
        $affiliation->recordClick(
            $request->ip(),
            $request->userAgent()
        );

        // Générer le lien tracké
        $trackingLink = AffiliationBonus::generateTrackingLink($user, $bookmaker, $baseUrl);

        $bookmakerInfo = AffiliationBonus::BOOKMAKERS[$bookmaker];

        return response()->json([
            'success' => true,
            'data' => [
                'bookmaker' => $bookmaker,
                'bookmaker_name' => $bookmakerInfo['name'],
                'tracking_link' => $trackingLink,
                'bonus_days' => $bookmakerInfo['bonus_days'],
                'message' => "Inscrivez-vous via ce lien pour recevoir {$bookmakerInfo['bonus_days']} jours premium gratuits !",
            ],
        ]);
    }

    /**
     * Lister les bookmakers disponibles avec leur statut de bonus
     *
     * GET /api/affiliate/bookmakers
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function listBookmakers(Request $request): JsonResponse
    {
        $user = $request->user();

        $bookmakers = [];

        foreach (AffiliationBonus::BOOKMAKERS as $key => $info) {
            $existingBonus = AffiliationBonus::where('user_id', $user->id)
                ->where('bookmaker', $key)
                ->first();

            $bookmakers[] = [
                'id' => $key,
                'name' => $info['name'],
                'bonus_days' => $info['bonus_days'],
                'already_used' => $existingBonus?->registration_confirmed ?? false,
                'bonus_active' => $existingBonus?->isBonusActive() ?? false,
                'bonus_expires_at' => $existingBonus?->bonus_expires_at,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $bookmakers,
        ]);
    }

    /**
     * Webhook pour recevoir les postbacks d'AffiliateControl
     *
     * GET/POST /api/webhooks/affiliate
     * 
     * Paramètres attendus (macros AffiliateControl):
     * - extid: {extid} - ID utilisateur COTA
     * - eventType: {eventType} - Type d'événement (registration, firstDeposit, deposit)
     * - playerId: {playerId} - ID joueur sur le bookmaker
     * - revenue: {revenue} - Commission affilié
     * - requestId: {requestId} - ID unique de l'événement
     * - subid1-5: {subid1}-{subid5} - SubIDs additionnels
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function handlePostback(Request $request): JsonResponse
    {
        // Vérifier la clé secrète Betwinner pour rejeter les appels non autorisés
        $expectedKey = config('services.betwinner.affiliate_key');
        $receivedKey = $request->query('key') ?? $request->header('X-Affiliate-Key');

        if ($expectedKey && $receivedKey !== $expectedKey) {
            Log::warning('Affiliate postback: clé invalide', [
                'ip'   => $request->ip(),
                'key'  => $receivedKey ? substr($receivedKey, 0, 8) . '...' : null,
            ]);
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        // Récupérer les données (GET ou POST)
        $data = $request->all();

        Log::info('Affiliate postback received', [
            'method' => $request->method(),
            'ip'     => $request->ip(),
            'event'  => $data['eventType'] ?? $data['event'] ?? 'unknown',
            'extid'  => $data['extid'] ?? null,
        ]);

        // Valider les données minimales
        $extid = $data['extid'] ?? null;

        if (!$extid) {
            Log::warning('Affiliate postback missing extid', ['data' => $data]);
            return response()->json([
                'success' => false,
                'error' => 'Missing extid parameter',
            ], 400);
        }

        // Vérifier que l'utilisateur existe
        $user = User::find($extid);

        if (!$user) {
            Log::warning('Affiliate postback user not found', [
                'extid' => $extid,
                'data' => $data,
            ]);
            return response()->json([
                'success' => false,
                'error' => 'User not found',
            ], 404);
        }

        // Déterminer le bookmaker depuis les subids ou autres paramètres
        $bookmaker = $data['subid2'] ?? $data['bookmaker'] ?? AffiliationBonus::BOOKMAKER_BETWINNER;

        // Récupérer ou créer l'enregistrement d'affiliation
        $affiliation = AffiliationBonus::where('user_id', $extid)
            ->where('bookmaker', $bookmaker)
            ->first();

        if (!$affiliation) {
            // Créer un nouvel enregistrement si nécessaire
            $affiliation = AffiliationBonus::create([
                'user_id' => $extid,
                'bookmaker' => $bookmaker,
                'affiliate_link' => 'postback_created',
            ]);
        }

        // Traiter le postback
        $eventType = $data['eventType'] ?? $data['event'] ?? 'registration';

        // Confirmer l'inscription
        $affiliation->confirmRegistration([
            'playerId' => $data['playerId'] ?? null,
            'eventType' => $eventType,
            'revenue' => $data['revenue'] ?? null,
            'requestId' => $data['requestId'] ?? null,
            'depositAmount' => $data['depositAmount'] ?? null,
            'subid1' => $data['subid1'] ?? null,
            'subid2' => $data['subid2'] ?? null,
            'subid3' => $data['subid3'] ?? null,
            'raw_data' => $data,
        ]);

        // Activer le bonus dès inscription ou premier dépôt
        // Événements AffiliateControl : registration | firstDeposit | deposit | newBet | betResult | withdrawal | chargeback
        if (in_array($eventType, ['registration', 'firstDeposit']) && !$affiliation->bonus_activated) {
            $affiliation->activateBonus();
        }

        // Enregistrer la conversion influenceur si cookie présent
        if (in_array($eventType, ['registration']) && $user) {
            \App\Models\Influencer::where('is_active', true)->each(function ($inf) use ($user) {
                // L'attribution est gérée par le cookie côté app — ici on vérifie via le subid
            });
        }

        Log::info('Affiliate postback processed successfully', [
            'user_id' => $extid,
            'bookmaker' => $bookmaker,
            'event_type' => $eventType,
            'bonus_activated' => $affiliation->bonus_activated,
        ]);

        // Retourner 200 OK pour confirmer la réception
        return response()->json([
            'success' => true,
            'message' => 'Postback processed',
        ]);
    }

    /**
     * Vérifier le statut d'affiliation d'un utilisateur
     *
     * GET /api/affiliate/status
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        $affiliations = AffiliationBonus::where('user_id', $user->id)->get();

        $data = $affiliations->map(function ($affiliation) {
            return [
                'bookmaker' => $affiliation->bookmaker,
                'bookmaker_name' => $affiliation->getBookmakerName(),
                'clicks_count' => $affiliation->clicks_count,
                'clicked_at' => $affiliation->clicked_at,
                'registration_confirmed' => $affiliation->registration_confirmed,
                'registration_confirmed_at' => $affiliation->registration_confirmed_at,
                'player_id' => $affiliation->player_id,
                'bonus_activated' => $affiliation->bonus_activated,
                'bonus_activated_at' => $affiliation->bonus_activated_at,
                'bonus_expires_at' => $affiliation->bonus_expires_at,
                'bonus_active' => $affiliation->isBonusActive(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Soumettre un ID joueur pour vérification et activation du bonus
     *
     * POST /api/affiliate/verify-player
     * 
     * L'utilisateur entre son ID joueur (BetWinner, 1xBet, etc.)
     * Le système vérifie via l'API AffiliateControl si cet ID
     * correspond à une inscription faite avec notre code promo.
     * Si oui, le bonus premium est automatiquement activé.
     * 
     * Body: {
     *   "bookmaker": "betwinner",
     *   "player_id": "123456789"
     * }
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyPlayerId(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bookmaker' => 'required|string|in:betwinner,1xbet,melbet',
            'player_id' => 'required|string|min:3|max:50',
        ], [
            'bookmaker.required' => 'Veuillez sélectionner un bookmaker',
            'bookmaker.in' => 'Bookmaker invalide',
            'player_id.required' => 'Veuillez entrer votre ID joueur',
            'player_id.min' => 'L\'ID joueur doit contenir au moins 3 caractères',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $bookmaker = $request->input('bookmaker');
        $playerId = trim($request->input('player_id'));

        // Vérifier si l'utilisateur a déjà un bonus activé pour ce bookmaker
        $existingBonus = AffiliationBonus::where('user_id', $user->id)
            ->where('bookmaker', $bookmaker)
            ->where('bonus_activated', true)
            ->first();

        if ($existingBonus) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà bénéficié du bonus pour ' . AffiliationBonus::BOOKMAKERS[$bookmaker]['name'],
                'data' => [
                    'already_claimed' => true,
                    'bonus_expires_at' => $existingBonus->bonus_expires_at,
                ],
            ], 400);
        }

        // Vérifier si ce player_id a déjà été utilisé par un autre utilisateur
        $usedByOther = AffiliationBonus::where('player_id', $playerId)
            ->where('bookmaker', $bookmaker)
            ->where('user_id', '!=', $user->id)
            ->exists();

        if ($usedByOther) {
            return response()->json([
                'success' => false,
                'message' => 'Cet ID joueur a déjà été utilisé par un autre compte',
            ], 400);
        }

        // Vérifier via l'API AffiliateControl
        $apiService = new \App\Services\AffiliateControlApiService();
        $found      = $apiService->verifyPlayerConversion($playerId);

        $result = ['found' => $found, 'conversion' => []];

        if ($result['found']) {
            // L'ID est vérifié ! Activer le bonus
            return $this->activateBonusForPlayer($user, $bookmaker, $playerId, $result['conversion']);
        }

        // ID non trouvé - proposer une vérification manuelle
        return $this->createPendingVerification($user, $bookmaker, $playerId);
    }

    /**
     * Activer le bonus pour un joueur vérifié
     */
    private function activateBonusForPlayer(User $user, string $bookmaker, string $playerId, array $conversionData): JsonResponse
    {
        $affiliation = AffiliationBonus::updateOrCreate(
            [
                'user_id' => $user->id,
                'bookmaker' => $bookmaker,
            ],
            [
                'affiliate_link' => 'manual_verification',
                'player_id' => $playerId,
                'registration_confirmed' => true,
                'registration_confirmed_at' => now(),
                'event_type' => 'manual_verification',
                'tracking_details' => [
                    'verification_method' => 'api',
                    'conversion_data' => $conversionData,
                    'verified_at' => now()->toIso8601String(),
                ],
            ]
        );

        // Activer le bonus premium
        $affiliation->activateBonus();

        $bonusDays = AffiliationBonus::BOOKMAKERS[$bookmaker]['bonus_days'];
        $bookmakerName = AffiliationBonus::BOOKMAKERS[$bookmaker]['name'];

        Log::info('Affiliate bonus activated via player ID verification', [
            'user_id' => $user->id,
            'bookmaker' => $bookmaker,
            'player_id' => $playerId,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Félicitations ! Votre inscription sur {$bookmakerName} a été vérifiée. Vous avez reçu {$bonusDays} jours premium !",
            'data' => [
                'verified' => true,
                'bonus_days' => $bonusDays,
                'bonus_expires_at' => $affiliation->fresh()->bonus_expires_at,
                'is_premium' => $user->fresh()->isPremium(),
            ],
        ]);
    }

    /**
     * Créer une demande de vérification en attente
     */
    private function createPendingVerification(User $user, string $bookmaker, string $playerId): JsonResponse
    {
        $affiliation = AffiliationBonus::updateOrCreate(
            [
                'user_id' => $user->id,
                'bookmaker' => $bookmaker,
            ],
            [
                'affiliate_link' => 'pending_verification',
                'player_id' => $playerId,
                'registration_confirmed' => false,
                'tracking_details' => [
                    'verification_method' => 'pending',
                    'submitted_at' => now()->toIso8601String(),
                    'status' => 'pending_review',
                ],
            ]
        );

        $bookmakerName = AffiliationBonus::BOOKMAKERS[$bookmaker]['name'];

        Log::info('Affiliate verification pending', [
            'user_id' => $user->id,
            'bookmaker' => $bookmaker,
            'player_id' => $playerId,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Votre ID joueur {$bookmakerName} a été enregistré. Notre équipe va vérifier votre inscription et activer votre bonus sous 24-48h.",
            'data' => [
                'verified' => false,
                'pending' => true,
                'player_id' => $playerId,
                'bookmaker' => $bookmakerName,
            ],
        ]);
    }

    /**
     * Obtenir les demandes de vérification en attente (pour admin)
     *
     * GET /api/admin/affiliate/pending
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getPendingVerifications(Request $request): JsonResponse
    {
        $pending = AffiliationBonus::where('registration_confirmed', false)
            ->whereNotNull('player_id')
            ->with('user:id,name,phone,email')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pending->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user' => [
                        'id' => $item->user->id,
                        'name' => $item->user->name,
                        'phone' => $item->user->phone,
                    ],
                    'bookmaker' => $item->bookmaker,
                    'bookmaker_name' => $item->getBookmakerName(),
                    'player_id' => $item->player_id,
                    'submitted_at' => $item->created_at,
                    'tracking_details' => $item->tracking_details,
                ];
            }),
            'count' => $pending->count(),
        ]);
    }

    /**
     * Approuver manuellement une demande de vérification (admin)
     *
     * POST /api/admin/affiliate/approve/{id}
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function approveVerification(Request $request, int $id): JsonResponse
    {
        $affiliation = AffiliationBonus::find($id);

        if (!$affiliation) {
            return response()->json([
                'success' => false,
                'message' => 'Demande non trouvée',
            ], 404);
        }

        if ($affiliation->bonus_activated) {
            return response()->json([
                'success' => false,
                'message' => 'Le bonus a déjà été activé',
            ], 400);
        }

        // Confirmer et activer le bonus
        $affiliation->update([
            'registration_confirmed' => true,
            'registration_confirmed_at' => now(),
            'tracking_details' => array_merge(
                $affiliation->tracking_details ?? [],
                [
                    'approved_by' => $request->user()?->id ?? 'admin',
                    'approved_at' => now()->toIso8601String(),
                    'verification_method' => 'manual_admin',
                ]
            ),
        ]);

        $affiliation->activateBonus();

        $bookmakerName = AffiliationBonus::BOOKMAKERS[$affiliation->bookmaker]['name'];

        Log::info('Affiliate verification approved by admin', [
            'affiliation_id' => $id,
            'user_id' => $affiliation->user_id,
            'bookmaker' => $affiliation->bookmaker,
            'approved_by' => $request->user()?->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Bonus {$bookmakerName} activé pour l'utilisateur",
            'data' => [
                'user_id' => $affiliation->user_id,
                'bonus_expires_at' => $affiliation->fresh()->bonus_expires_at,
            ],
        ]);
    }

    /**
     * Rejeter une demande de vérification (admin)
     *
     * POST /api/admin/affiliate/reject/{id}
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function rejectVerification(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
        ]);

        $affiliation = AffiliationBonus::find($id);

        if (!$affiliation) {
            return response()->json([
                'success' => false,
                'message' => 'Demande non trouvée',
            ], 404);
        }

        $reason = $request->input('reason', 'ID joueur non trouvé dans nos conversions');

        $affiliation->update([
            'tracking_details' => array_merge(
                $affiliation->tracking_details ?? [],
                [
                    'rejected_by' => $request->user()?->id ?? 'admin',
                    'rejected_at' => now()->toIso8601String(),
                    'rejection_reason' => $reason,
                    'status' => 'rejected',
                ]
            ),
        ]);

        Log::info('Affiliate verification rejected by admin', [
            'affiliation_id' => $id,
            'user_id' => $affiliation->user_id,
            'reason' => $reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande rejetée',
        ]);
    }
}

