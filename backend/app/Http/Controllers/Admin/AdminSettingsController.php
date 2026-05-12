<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use App\Services\Payment\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    // ══════════════════════════════════════════════════════════════════
    // VUE WEB
    // ══════════════════════════════════════════════════════════════════

    /** GET /admin/settings */
    public function index(PaymentGatewayService $gateway)
    {
        $paymentData = [
            'active_provider'   => AppConfig::get('payment.active_provider', ''),
            'currency'          => AppConfig::get('payment.currency', 'XOF'),
            'webhook_secret'    => AppConfig::get('payment.webhook_secret', ''),
            'providers'         => $this->maskProviderSecrets(AppConfig::get('payment.providers', [])),
            'available_drivers' => $gateway->availableDrivers(),
        ];

        $apiKeys = [
            'football_api_key'    => AppConfig::get('api.football_api_key', ''),
            'openweather_key'     => AppConfig::get('api.openweather_key', ''),
            'termii_key'          => AppConfig::get('api.termii_key', ''),
            'termii_sender_id'    => AppConfig::get('api.termii_sender_id', 'COTA'),
            'facebook_app_id'     => AppConfig::get('api.facebook_app_id', ''),
            'facebook_app_secret' => $this->mask(AppConfig::get('api.facebook_app_secret', '')),
        ];

        $appConfig = [
            'prediction_publish_hours' => AppConfig::get('app.prediction_publish_hours', [8, 20]),
            'premium_plans'            => AppConfig::get('app.premium_plans', []),
        ];

        $bookmakers = AppConfig::get('bookmakers.list', []);

        return view('admin.settings.index', compact('paymentData', 'apiKeys', 'appConfig', 'bookmakers'));
    }

    // ══════════════════════════════════════════════════════════════════
    // PAIEMENT
    // ══════════════════════════════════════════════════════════════════

    /** GET /admin/settings/payment */
    public function getPayment(PaymentGatewayService $gateway): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'active_provider'   => AppConfig::get('payment.active_provider', ''),
                'currency'          => AppConfig::get('payment.currency', 'XOF'),
                'webhook_secret'    => AppConfig::get('payment.webhook_secret', ''),
                'providers'         => $this->maskProviderSecrets(
                    AppConfig::get('payment.providers', [])
                ),
                'available_drivers' => $gateway->availableDrivers(),
            ],
        ]);
    }

    /** PUT /admin/settings/payment */
    public function updatePayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'active_provider'          => 'nullable|string|max:50',
            'currency'                 => 'nullable|string|max:10',
            'webhook_secret'           => 'nullable|string|max:255',
            'providers'                => 'nullable|array',
            'providers.*.slug'         => 'required_with:providers|string|max:50',
            'providers.*.label'        => 'required_with:providers|string|max:100',
            'providers.*.api_key'      => 'nullable|string|max:500',
            'providers.*.api_secret'   => 'nullable|string|max:500',
            'providers.*.env'          => 'nullable|in:test,live',
            'providers.*.extra'        => 'nullable|array',
        ]);

        if (array_key_exists('active_provider', $validated)) {
            AppConfig::set('payment.active_provider', $validated['active_provider'] ?? '', 'string');
        }
        if (array_key_exists('currency', $validated)) {
            AppConfig::set('payment.currency', $validated['currency'] ?? 'XOF', 'string');
        }
        if (array_key_exists('webhook_secret', $validated)) {
            AppConfig::set('payment.webhook_secret', $validated['webhook_secret'] ?? '', 'string');
        }
        if (array_key_exists('providers', $validated)) {
            // Fusionner avec les providers existants pour ne pas écraser les secrets non envoyés
            $existing  = AppConfig::get('payment.providers', []);
            $existingBySlug = collect($existing)->keyBy('slug')->all();

            $merged = collect($validated['providers'])->map(function (array $p) use ($existingBySlug) {
                $slug = $p['slug'];
                $prev = $existingBySlug[$slug] ?? [];

                return [
                    'slug'       => $slug,
                    'label'      => $p['label'],
                    'api_key'    => $p['api_key']    ?? $prev['api_key']    ?? '',
                    'api_secret' => $p['api_secret'] ?? $prev['api_secret'] ?? '',
                    'env'        => $p['env']        ?? $prev['env']        ?? 'test',
                    'extra'      => $p['extra']      ?? $prev['extra']      ?? [],
                ];
            })->values()->all();

            AppConfig::set('payment.providers', $merged, 'json');
        }

        return response()->json(['success' => true, 'message' => 'Configuration paiement mise à jour.']);
    }

    // ══════════════════════════════════════════════════════════════════
    // CLÉS API
    // ══════════════════════════════════════════════════════════════════

    /** GET /admin/settings/api-keys */
    public function getApiKeys(): JsonResponse
    {
        $keys = [
            'football_api_key'  => AppConfig::get('api.football_api_key', ''),
            'openweather_key'   => AppConfig::get('api.openweather_key', ''),
            'termii_key'        => AppConfig::get('api.termii_key', ''),
            'termii_sender_id'  => AppConfig::get('api.termii_sender_id', 'COTA'),
            'facebook_app_id'   => AppConfig::get('api.facebook_app_id', ''),
            'facebook_app_secret' => $this->mask(AppConfig::get('api.facebook_app_secret', '')),
        ];

        return response()->json(['success' => true, 'data' => $keys]);
    }

    /** PUT /admin/settings/api-keys */
    public function updateApiKeys(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'football_api_key'     => 'nullable|string|max:255',
            'openweather_key'      => 'nullable|string|max:255',
            'termii_key'           => 'nullable|string|max:255',
            'termii_sender_id'     => 'nullable|string|max:50',
            'facebook_app_id'      => 'nullable|string|max:255',
            'facebook_app_secret'  => 'nullable|string|max:255',
        ]);

        $map = [
            'football_api_key'    => 'api.football_api_key',
            'openweather_key'     => 'api.openweather_key',
            'termii_key'          => 'api.termii_key',
            'termii_sender_id'    => 'api.termii_sender_id',
            'facebook_app_id'     => 'api.facebook_app_id',
            'facebook_app_secret' => 'api.facebook_app_secret',
        ];

        foreach ($map as $field => $configKey) {
            if (array_key_exists($field, $validated) && $validated[$field] !== null) {
                AppConfig::set($configKey, $validated[$field], 'string');
            }
        }

        return response()->json(['success' => true, 'message' => 'Clés API mises à jour.']);
    }

    // ══════════════════════════════════════════════════════════════════
    // BOOKMAKERS
    // ══════════════════════════════════════════════════════════════════

    /** GET /admin/settings/bookmakers */
    public function getBookmakers(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => AppConfig::get('bookmakers.list', []),
        ]);
    }

    /** PUT /admin/settings/bookmakers */
    public function updateBookmakers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bookmakers'                 => 'required|array',
            'bookmakers.*.id'            => 'required|string|max:50',
            'bookmakers.*.name'          => 'required|string|max:100',
            'bookmakers.*.url'           => 'required|url|max:500',
            'bookmakers.*.tracking_id'   => 'nullable|string|max:200',
            'bookmakers.*.logo_emoji'    => 'nullable|string|max:10',
            'bookmakers.*.color'         => 'nullable|string|max:7',
            'bookmakers.*.is_active'     => 'boolean',
        ]);

        AppConfig::set('bookmakers.list', $validated['bookmakers'], 'json');

        return response()->json(['success' => true, 'message' => 'Bookmakers mis à jour.']);
    }

    // ══════════════════════════════════════════════════════════════════
    // APP (heures de publication, plans premium…)
    // ══════════════════════════════════════════════════════════════════

    /** GET /admin/settings/app */
    public function getApp(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'prediction_publish_hours' => AppConfig::get('app.prediction_publish_hours', [8, 20]),
                'premium_plans'            => AppConfig::get('app.premium_plans', []),
            ],
        ]);
    }

    /** PUT /admin/settings/app */
    public function updateApp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prediction_publish_hours'         => 'nullable|array',
            'prediction_publish_hours.*'       => 'integer|min:0|max:23',
            'premium_plans'                    => 'nullable|array',
            'premium_plans.*.label'            => 'required_with:premium_plans|string|max:50',
            'premium_plans.*.price'            => 'required_with:premium_plans|integer|min:0',
            'premium_plans.*.days'             => 'required_with:premium_plans|integer|min:1',
        ]);

        if (array_key_exists('prediction_publish_hours', $validated)) {
            AppConfig::set('app.prediction_publish_hours', $validated['prediction_publish_hours'], 'json');
        }
        if (array_key_exists('premium_plans', $validated)) {
            AppConfig::set('app.premium_plans', $validated['premium_plans'], 'json');
        }

        return response()->json(['success' => true, 'message' => 'Configuration app mise à jour.']);
    }

    // ══════════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════════

    private function mask(string $value): string
    {
        if (strlen($value) <= 8) return str_repeat('*', strlen($value));
        return substr($value, 0, 4) . str_repeat('*', strlen($value) - 8) . substr($value, -4);
    }

    private function maskProviderSecrets(array $providers): array
    {
        return array_map(function (array $p) {
            $p['api_secret'] = $this->mask($p['api_secret'] ?? '');
            return $p;
        }, $providers);
    }
}
