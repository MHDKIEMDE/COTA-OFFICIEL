<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Influencer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class InfluencerController extends Controller
{
    /**
     * GET /r/{slug}
     * Redirige vers l'app store / landing page en trackant le clic.
     */
    public function redirect(Request $request, string $slug): RedirectResponse
    {
        $influencer = Influencer::where('slug', $slug)->where('is_active', true)->first();

        if (!$influencer) {
            return redirect(config('app.frontend_url', '/'));
        }

        // Anti click fraud — 1 clic unique par IP par influenceur par 24h
        $cacheKey = "influencer_click.{$influencer->id}." . md5($request->ip());
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            Log::debug("Influencer click ignoré (doublon IP): {$slug}", ['ip' => $request->ip()]);
        } else {
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addHours(24));

            $ua     = $request->userAgent() ?? '';
            $device = str_contains(strtolower($ua), 'mobile') ? 'mobile' : 'desktop';

            $influencer->recordClick(
                ip:        $request->ip(),
                userAgent: $ua,
                referrer:  $request->header('Referer'),
                device:    $device,
            );
        }

        Log::info("Influencer click: {$slug}", ['ip' => $request->ip(), 'device' => $device]);

        // Cookie 30j pour attribuer l'inscription plus tard
        $destination = config('app.frontend_url', 'https://cotafoot.com');

        return redirect($destination)
            ->withCookie(Cookie::make('inf_ref', $slug, 60 * 24 * 30));
    }

    /**
     * POST /api/influencer/conversion
     * Appelé à l'inscription d'un utilisateur pour attribuer la conversion.
     * Lit le cookie inf_ref ou le paramètre ref.
     */
    public function recordConversion(Request $request): JsonResponse
    {
        $slug = $request->cookie('inf_ref') ?? $request->input('ref');

        if (!$slug) {
            return response()->json(['success' => false, 'message' => 'No referral']);
        }

        $influencer = Influencer::where('slug', $slug)->where('is_active', true)->first();

        if (!$influencer) {
            return response()->json(['success' => false, 'message' => 'Influencer not found']);
        }

        $user = $request->user();
        $influencer->recordConversion($user->id, 'registration');

        // Vérifier si seuil atteint → récompense automatique
        $rewarded = $influencer->checkAndReward();

        Log::info("Influencer conversion: {$slug}", [
            'user_id'  => $user->id,
            'rewarded' => $rewarded,
        ]);

        return response()->json(['success' => true, 'rewarded' => $rewarded]);
    }

    // ── ADMIN ─────────────────────────────────────────────────────────────────

    /** GET /api/admin/influencers */
    public function index(): JsonResponse
    {
        $influencers = Influencer::withCount(['clicks', 'conversions'])
            ->orderByDesc('total_registrations')
            ->get()
            ->map(fn($i) => $this->format($i));

        return response()->json(['success' => true, 'data' => $influencers]);
    }

    /** POST /api/admin/influencers */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'              => 'required|string|max:100',
            'slug'              => 'required|string|max:50|unique:influencers,slug|alpha_dash',
            'email'             => 'nullable|email',
            'phone'             => 'nullable|string',
            'platform'          => 'nullable|string',
            'user_id'           => 'nullable|exists:users,id',
            'reward_type'       => 'in:premium_days,cash,both',
            'reward_threshold'  => 'integer|min:1',
            'reward_value'      => 'integer|min:1',
            'notes'             => 'nullable|string',
        ]);

        $influencer = Influencer::create($data);

        return response()->json([
            'success'      => true,
            'influencer'   => $this->format($influencer),
            'tracking_url' => $influencer->trackingUrl(),
        ], 201);
    }

    /** GET /api/admin/influencers/{id}/stats */
    public function stats(int $id): JsonResponse
    {
        $influencer = Influencer::findOrFail($id);

        $clicksByDay = $influencer->clicks()
            ->selectRaw('DATE(clicked_at) as date, COUNT(*) as count')
            ->where('clicked_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $conversionsByType = $influencer->conversions()
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        return response()->json([
            'success'            => true,
            'influencer'         => $this->format($influencer),
            'tracking_url'       => $influencer->trackingUrl(),
            'clicks_last_30d'    => $clicksByDay,
            'conversions_by_type'=> $conversionsByType,
            'conversion_rate'    => $influencer->total_clicks > 0
                ? round($influencer->total_registrations / $influencer->total_clicks * 100, 1)
                : 0,
        ]);
    }

    /** PATCH /api/admin/influencers/{id}/toggle */
    public function toggle(int $id): JsonResponse
    {
        $influencer = Influencer::findOrFail($id);
        $influencer->update(['is_active' => !$influencer->is_active]);

        return response()->json(['success' => true, 'is_active' => $influencer->is_active]);
    }

    private function format(Influencer $i): array
    {
        return [
            'id'                  => $i->id,
            'name'                => $i->name,
            'slug'                => $i->slug,
            'platform'            => $i->platform,
            'tracking_url'        => $i->trackingUrl(),
            'total_clicks'        => $i->total_clicks,
            'total_registrations' => $i->total_registrations,
            'total_subscriptions' => $i->total_subscriptions,
            'reward_threshold'    => $i->reward_threshold,
            'reward_value'        => $i->reward_value,
            'reward_type'         => $i->reward_type,
            'total_rewards_given' => $i->total_rewards_given,
            'last_rewarded_at'    => $i->last_rewarded_at,
            'is_active'           => $i->is_active,
        ];
    }
}
