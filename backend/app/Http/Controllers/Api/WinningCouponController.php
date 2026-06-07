<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WinningCoupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WinningCouponController extends Controller
{
    // POST /winning-coupons — Enregistrer un coupon gagnant
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'picks'          => 'required|array|min:1|max:10',
            'picks.*.match'  => 'required|string',
            'picks.*.odds'   => 'required|numeric|min:1',
            'total_odds'     => 'required|numeric|min:1',
            'played_at'      => 'required|date',
            'stake'          => 'nullable|numeric|min:0',
            'actual_gain'    => 'nullable|numeric|min:0',
            'ai_analysis'    => 'nullable|array',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $picks = $request->picks;
        $coupon = WinningCoupon::create([
            'user_id'        => $request->user()->id,
            'picks'          => $picks,
            'total_odds'     => $request->total_odds,
            'picks_count'    => count($picks),
            'avg_confidence' => collect($picks)->avg('confidence') ?? null,
            'avg_stars'      => round(collect($picks)->avg('stars') ?? 0),
            'stake'          => $request->stake,
            'actual_gain'    => $request->actual_gain,
            'ai_analysis'    => $request->ai_analysis,
            'played_at'      => $request->played_at,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $coupon,
            'message' => 'Coupon gagnant enregistré.',
        ], 201);
    }

    // GET /winning-coupons — Liste des coupons gagnants de l'utilisateur
    public function index(Request $request): JsonResponse
    {
        $coupons = WinningCoupon::where('user_id', $request->user()->id)
            ->orderByDesc('played_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $coupons,
        ]);
    }

    // DELETE /winning-coupons/{id} — Supprimer un coupon
    public function destroy(Request $request, int $id): JsonResponse
    {
        $coupon = WinningCoupon::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $coupon->delete();

        return response()->json(['success' => true, 'message' => 'Coupon supprimé.']);
    }

    // GET /winning-coupons/profile — Profil de cote personnalisé
    public function profile(Request $request): JsonResponse
    {
        $profile = WinningCoupon::personalOddsProfile($request->user()->id);

        return response()->json([
            'success' => true,
            'data'    => $profile,
            'message' => $profile['count'] === 0
                ? 'Aucun coupon gagnant enregistré. Ajoutez vos premiers pour personnaliser votre profil.'
                : "Profil basé sur {$profile['count']} coupons gagnants.",
        ]);
    }
}
