<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    // Events P0 autorisés (whitelist — aucun event arbitraire)
    private const ALLOWED_EVENTS = [
        'app_opened',
        'scratch_card_seen',
        'scratch_attempt_blocked',
        'signup_started',
        'signup_completed',
        'premium_wall_hit',
        'subscription_started',
        'subscription_completed',
        'bet_now_clicked',
        'affiliate_redirect',
    ];

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_name'  => 'required|string|max:60',
            'properties'  => 'nullable|array',
            'user_id'     => 'nullable|integer',
        ]);

        $eventName = $validated['event_name'];

        if (!in_array($eventName, self::ALLOWED_EVENTS, true)) {
            return response()->json(['success' => false, 'message' => 'Event non autorisé'], 422);
        }

        // Hash IP pour RGPD — jamais stocker l'IP brute
        $sessionHash = hash('sha256',
            ($request->ip() ?? '') . ($request->userAgent() ?? '') . date('Y-m-d')
        );

        // user_id : priorité au token auth, sinon payload (invité peut transmettre null)
        $userId = auth('sanctum')->id() ?? ($validated['user_id'] ?? null);

        AnalyticsEvent::log(
            eventName:   $eventName,
            properties:  $validated['properties'] ?? [],
            userId:      $userId,
            source:      'flutter_app',
            sessionHash: $sessionHash,
        );

        return response()->json(['success' => true]);
    }
}
