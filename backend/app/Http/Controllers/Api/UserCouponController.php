<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserCoupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserCouponController extends Controller
{
    // GET /user-coupons
    public function index(Request $request): JsonResponse
    {
        $coupons = UserCoupon::where('user_id', $request->user()->id)
            ->orderByDesc('played_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn(UserCoupon $c) => $this->format($c));

        return response()->json(['success' => true, 'data' => $coupons]);
    }

    // POST /user-coupons
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'              => 'nullable|string|max:100',
            'picks'             => 'required|array|min:1|max:15',
            'picks.*.prediction_id' => 'nullable|integer',
            'picks.*.match'     => 'required|string',
            'picks.*.league'    => 'nullable|string',
            'picks.*.prediction'=> 'required|string',
            'picks.*.odds'      => 'required|numeric|min:1',
            'picks.*.stars'     => 'nullable|integer|min:1|max:4',
            'picks.*.confidence'=> 'nullable|numeric',
            'total_odds'        => 'required|numeric|min:1',
            'stake'             => 'nullable|numeric|min:0',
            'played_at'         => 'required|date',
        ]);

        $coupon = UserCoupon::create([
            'user_id'     => $request->user()->id,
            'name'        => $validated['name'] ?? null,
            'picks'       => $validated['picks'],
            'total_odds'  => $validated['total_odds'],
            'picks_count' => count($validated['picks']),
            'stake'       => $validated['stake'] ?? null,
            'status'      => 'pending',
            'played_at'   => $validated['played_at'],
        ]);

        return response()->json([
            'success' => true,
            'data'    => $this->format($coupon),
            'message' => 'Coupon créé.',
        ], 201);
    }

    // PATCH /user-coupons/{id}/result
    public function updateResult(Request $request, int $id): JsonResponse
    {
        $coupon = UserCoupon::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validated = $request->validate([
            'status'      => ['required', Rule::in(['pending', 'won', 'lost', 'partial'])],
            'actual_gain' => 'nullable|numeric|min:0',
        ]);

        $coupon->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $this->format($coupon->fresh()),
            'message' => 'Résultat mis à jour.',
        ]);
    }

    // DELETE /user-coupons/{id}
    public function destroy(Request $request, int $id): JsonResponse
    {
        UserCoupon::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail()
            ->delete();

        return response()->json(['success' => true, 'message' => 'Coupon supprimé.']);
    }

    // GET /user-coupons/stats
    public function stats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $coupons = UserCoupon::where('user_id', $userId)->get();

        $total   = $coupons->count();
        $won     = $coupons->where('status', 'won')->count();
        $lost    = $coupons->where('status', 'lost')->count();
        $pending = $coupons->where('status', 'pending')->count();

        $totalStaked = $coupons->sum('stake');
        $totalGain   = $coupons->where('status', 'won')->sum('actual_gain');
        $roi         = $totalStaked > 0 ? round(($totalGain - $totalStaked) / $totalStaked * 100, 1) : null;

        return response()->json([
            'success' => true,
            'data'    => [
                'total'        => $total,
                'won'          => $won,
                'lost'         => $lost,
                'pending'      => $pending,
                'win_rate'     => $total > 0 ? round($won / ($total - $pending) * 100, 1) : null,
                'total_staked' => $totalStaked,
                'total_gain'   => $totalGain,
                'roi'          => $roi,
            ],
        ]);
    }

    private function format(UserCoupon $c): array
    {
        return [
            'id'             => $c->id,
            'name'           => $c->name,
            'picks'          => $c->picks,
            'picks_count'    => $c->picks_count,
            'total_odds'     => $c->total_odds,
            'stake'          => $c->stake,
            'status'         => $c->status,
            'actual_gain'    => $c->actual_gain,
            'potential_gain' => $c->potential_gain,
            'played_at'      => $c->played_at?->toDateString(),
            'created_at'     => $c->created_at?->toIso8601String(),
        ];
    }
}
