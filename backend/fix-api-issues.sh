#!/bin/bash

# 🚨 Script de correction rapide des problèmes API
# À exécuter dans le répertoire du backend Laravel

echo "🔧 Correction des problèmes API Football..."

# 1. Vider le cache des quotas
echo "🗑️ Suppression du cache quota Sportradar..."
php artisan tinker --execute="Cache::forget('sportradar_quota_exceeded'); echo '✅ Cache quota supprimé';"

# 2. Nettoyer les caches
echo "🧹 Nettoyage des caches..."
php artisan cache:clear
php artisan config:clear

# 3. Vérifier les configurations API
echo "⚙️ Vérification des configurations..."
php artisan tinker --execute="
echo '📊 Configuration Sportradar:';
echo 'API Key présente: ' . (!empty(config('services.sportradar.api_key')) ? '✅' : '❌');
echo 'Base URL: ' . config('services.sportradar.base_url', 'non défini');
echo '';
echo '📊 Configuration API-Football:';
echo 'API Key présente: ' . (!empty(config('football-api.api_key')) ? '✅' : '❌');
echo 'Base URL: ' . config('football-api.base_url', 'non défini');
"

# 4. Tester la connectivité des APIs
echo "🌐 Test de connectivité des APIs..."
php artisan tinker --execute="
try {
    \$client = new \GuzzleHttp\Client(['timeout' => 10]);
    \$response = \$client->get('https://api.sportradar.com/soccer/trial/v4/en/schedules/2026-01-15/schedules.json?api_key=demo');
    echo '✅ Sportradar: Accessible (mais quota trial limité)';
} catch (Exception \$e) {
    echo '⚠️ Sportradar: ' . \$e->getMessage();
}

try {
    \$footballApi = app(\App\Services\FootballApiService::class);
    \$test = \$footballApi->getFixtures(['live' => 'all']);
    echo '✅ API-Football: ' . count(\$test) . ' matchs trouvés';
} catch (Exception \$e) {
    echo '❌ API-Football: ' . \$e->getMessage();
}
"

# 5. Créer des données de test
echo "📝 Création de données de test..."
php artisan tinker --execute="
\DB::table('predictions')->where('source', 'test')->delete();

\$testPredictions = [
    [
        'match_id' => 'test_' . rand(1000, 9999),
        'home_team' => 'PSG',
        'away_team' => 'Marseille',
        'competition' => 'Ligue 1',
        'match_date' => now()->addDays(rand(1, 3)),
        'bet_type' => '1X2',
        'prediction' => '1',
        'odds' => '1.85',
        'confidence_stars' => 4,
        'is_premium' => false,
        'is_published' => true,
        'source' => 'test',
        'analysis_details' => json_encode(['reasoning' => 'Test prediction']),
        'published_at' => now(),
    ],
    [
        'match_id' => 'test_' . rand(1000, 9999),
        'home_team' => 'Real Madrid',
        'away_team' => 'Barcelona',
        'competition' => 'La Liga',
        'match_date' => now()->addDays(rand(1, 3)),
        'bet_type' => '1X2',
        'prediction' => 'X',
        'odds' => '3.20',
        'confidence_stars' => 3,
        'is_premium' => true,
        'is_published' => true,
        'source' => 'test',
        'analysis_details' => json_encode(['reasoning' => 'Test prediction premium']),
        'published_at' => now(),
    ]
];

foreach (\$testPredictions as \$pred) {
    \DB::table('predictions')->insert(\$pred);
}

echo '✅ ' . count(\$testPredictions) . ' prédictions de test créées';
"

# 6. Tester l'API
echo "🧪 Test de l'API..."
curl -s -o /dev/null -w "🌐 API Response: %{http_code}\n" http://localhost:8000/api/predictions/today

echo ""
echo "🎉 Corrections terminées !"
echo ""
echo "📋 Prochaines étapes :"
echo "1. Redémarrer le serveur: php artisan serve"
echo "2. Tester l'API: http://localhost:8000/api/predictions/today"
echo "3. Monitorer les logs: tail -f storage/logs/laravel.log"
echo "4. Si problèmes persistent, vérifier les clés API dans .env"
echo ""
echo "🔑 Clés API nécessaires :"
echo "   SPORTRADAR_API_KEY=votre_clé_sportradar"
echo "   API_FOOTBALL_KEY=votre_clé_api_football"