<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyReferralCodeRequest;
use App\Http\Resources\ReferralItemResource;
use App\Http\Resources\ReferralStatsResource;
use App\Services\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReferralController extends Controller
{
    public function __construct(private readonly ReferralService $referralService) {}

    /** GET /api/referrals/stats */
    public function getReferralStats(Request $request): ReferralStatsResource
    {
        $stats = $this->referralService->getStats($request->user());

        return new ReferralStatsResource($stats);
    }

    /** GET /api/referrals/my-code */
    public function getMyReferralCode(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data'    => [
                'referral_code' => $user->referral_code,
                'share_link'    => config('app.url') . '/referral?code=' . $user->referral_code,
            ],
        ]);
    }

    /** GET /api/referrals/list */
    public function listReferrals(Request $request): JsonResponse
    {
        $result = $this->referralService->listReferrals(
            $request->user(),
            (int) $request->query('page', 1)
        );

        return response()->json([
            'success' => true,
            'data'    => ReferralItemResource::collection($result['data'])->resolve(),
            'meta'    => $result['meta'],
        ]);
    }

    /** POST /api/referrals/apply */
    public function applyReferralCode(ApplyReferralCodeRequest $request): JsonResponse
    {
        try {
            $this->referralService->apply($request->user(), $request->validated('referral_code'));
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['success' => false, 'message' => 'Code de parrainage invalide.'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Code de parrainage appliqué avec succès !']);
    }
}
