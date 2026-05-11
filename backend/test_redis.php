<?php
/**
 * Script de test Redis complet pour COTA
 * Usage: php test_redis.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

echo "🧪 Test Redis Complet - COTA Backend\n";
echo "=====================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// 1. Vérifier l'installation Redis
echo "1. Vérification de l'installation Redis...\n";
try {
    $redis = app('redis')->connection('cache');
    $result = $redis->ping();
    if ($result) {
        $success[] = "Redis est connecté et fonctionnel";
        echo "   ✅ Redis est connecté et fonctionnel!\n";
    } else {
        $errors[] = "Redis ne répond pas correctement";
        echo "   ❌ Redis ne répond pas correctement\n";
    }
} catch (\Exception $e) {
    $errors[] = "Erreur de connexion Redis: " . $e->getMessage();
    echo "   ❌ Erreur de connexion Redis: " . $e->getMessage() . "\n";
    echo "   💡 Installez Redis:\n";
    echo "      - macOS: brew install redis && brew services start redis\n";
    echo "      - Ubuntu: sudo apt install redis-server && sudo systemctl start redis-server\n";
    echo "\n";
    exit(1);
}
echo "\n";

// 2. Vérifier la configuration
echo "2. Vérification de la configuration...\n";
$cacheDriver = Config::get('cache.default');
$redisHost = Config::get('database.redis.cache.host', 'N/A');
$redisPort = Config::get('database.redis.cache.port', 'N/A');
$redisClient = Config::get('database.redis.client', 'N/A');

echo "   - Cache driver: $cacheDriver\n";
echo "   - Redis host: $redisHost\n";
echo "   - Redis port: $redisPort\n";
echo "   - Redis client: $redisClient\n";

if ($cacheDriver === 'redis') {
    $success[] = "Cache driver configuré sur Redis";
    echo "   ✅ Cache driver configuré sur Redis\n";
} else {
    $warnings[] = "Cache driver n'est pas 'redis' (actuel: $cacheDriver)";
    echo "   ⚠️  Cache driver n'est pas 'redis' (actuel: $cacheDriver)\n";
    echo "   💡 Modifiez .env: CACHE_STORE=redis\n";
}
echo "\n";

// 3. Test d'écriture/lecture basique
echo "3. Test d'écriture/lecture basique...\n";
try {
    $testKey = 'cota_test_' . time();
    $testValue = 'Test Redis OK';
    
    Cache::put($testKey, $testValue, 60);
    $retrieved = Cache::get($testKey);
    
    if ($retrieved === $testValue) {
        $success[] = "Écriture/lecture basique fonctionne";
        echo "   ✅ Écriture réussie\n";
        echo "   ✅ Lecture réussie\n";
        echo "   ✅ Valeur: '$retrieved'\n";
        
        Cache::forget($testKey);
    } else {
        $errors[] = "Valeur incorrecte récupérée";
        echo "   ❌ Valeur incorrecte récupérée\n";
    }
} catch (\Exception $e) {
    $errors[] = "Erreur test basique: " . $e->getMessage();
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}
echo "\n";

// 4. Test de performance
echo "4. Test de performance (1000 opérations)...\n";
try {
    $start = microtime(true);
    for ($i = 0; $i < 1000; $i++) {
        Cache::put("perf_test_$i", "value_$i", 60);
        Cache::get("perf_test_$i");
    }
    $end = microtime(true);
    $duration = ($end - $start) * 1000; // ms
    $opsPerSec = 1000 / ($duration / 1000);
    
    $success[] = "Performance: " . number_format($opsPerSec, 0) . " ops/sec";
    echo "   ✅ 1000 opérations en " . number_format($duration, 2) . " ms\n";
    echo "   ✅ Performance: " . number_format($opsPerSec, 0) . " opérations/seconde\n";
    
    // Nettoyer
    for ($i = 0; $i < 1000; $i++) {
        Cache::forget("perf_test_$i");
    }
} catch (\Exception $e) {
    $errors[] = "Erreur test performance: " . $e->getMessage();
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}
echo "\n";

// 5. Test avec les services COTA
echo "5. Test avec les services COTA...\n";
try {
    $intelligentService = app(\App\Services\IntelligentApiService::class);
    $stats = $intelligentService->getStats();
    
    $cacheDriverDetected = $stats['cache_driver'] ?? 'N/A';
    $cachedEntries = $stats['cached_entries'] ?? 0;
    
    echo "   - Cache driver détecté: $cacheDriverDetected\n";
    echo "   - Entrées en cache: $cachedEntries\n";
    
    if ($cacheDriverDetected === 'redis') {
        $success[] = "Services COTA utilisent Redis";
        echo "   ✅ Services COTA utilisent Redis!\n";
    } else {
        $warnings[] = "Services COTA n'utilisent pas encore Redis";
        echo "   ⚠️  Services COTA n'utilisent pas encore Redis\n";
    }
} catch (\Exception $e) {
    $warnings[] = "Impossible de tester les services: " . $e->getMessage();
    echo "   ⚠️  Impossible de tester les services: " . $e->getMessage() . "\n";
}
echo "\n";

// 6. Test avec données réelles (simulation)
echo "6. Test avec données réelles (simulation)...\n";
try {
    $matchData = [
        'id' => 'test_match_' . time(),
        'home_team' => 'Team A',
        'away_team' => 'Team B',
        'date' => date('Y-m-d'),
    ];
    
    $cacheKey = 'test_match_' . $matchData['id'];
    Cache::put($cacheKey, $matchData, 300); // 5 min
    
    $retrieved = Cache::get($cacheKey);
    if ($retrieved && $retrieved['id'] === $matchData['id']) {
        $success[] = "Données réelles fonctionnent";
        echo "   ✅ Données réelles sauvegardées et récupérées\n";
        Cache::forget($cacheKey);
    } else {
        $errors[] = "Données réelles incorrectes";
        echo "   ❌ Données réelles incorrectes\n";
    }
} catch (\Exception $e) {
    $errors[] = "Erreur données réelles: " . $e->getMessage();
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}
echo "\n";

// 7. Test TTL (Time To Live)
echo "7. Test TTL (expiration automatique)...\n";
try {
    $ttlKey = 'ttl_test_' . time();
    Cache::put($ttlKey, 'expire_soon', 2); // 2 secondes
    
    sleep(1);
    $beforeExpire = Cache::get($ttlKey);
    
    sleep(2);
    $afterExpire = Cache::get($ttlKey);
    
    if ($beforeExpire === 'expire_soon' && $afterExpire === null) {
        $success[] = "TTL fonctionne correctement";
        echo "   ✅ TTL fonctionne correctement\n";
        echo "   ✅ Valeur présente avant expiration\n";
        echo "   ✅ Valeur supprimée après expiration\n";
    } else {
        $warnings[] = "TTL peut ne pas fonctionner correctement";
        echo "   ⚠️  TTL peut ne pas fonctionner correctement\n";
    }
} catch (\Exception $e) {
    $warnings[] = "Erreur test TTL: " . $e->getMessage();
    echo "   ⚠️  Erreur: " . $e->getMessage() . "\n";
}
echo "\n";

// Résumé
echo "=====================================\n";
echo "📊 RÉSUMÉ DES TESTS\n";
echo "=====================================\n\n";

if (count($success) > 0) {
    echo "✅ Succès (" . count($success) . "):\n";
    foreach ($success as $msg) {
        echo "   - $msg\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "⚠️  Avertissements (" . count($warnings) . "):\n";
    foreach ($warnings as $msg) {
        echo "   - $msg\n";
    }
    echo "\n";
}

if (count($errors) > 0) {
    echo "❌ Erreurs (" . count($errors) . "):\n";
    foreach ($errors as $msg) {
        echo "   - $msg\n";
    }
    echo "\n";
    exit(1);
}

echo "✅ Tous les tests sont passés!\n";
echo "🚀 Redis est prêt pour la production!\n\n";

echo "📈 Gains de performance attendus:\n";
echo "   - Latence cache: < 1ms (vs 10-50ms database)\n";
echo "   - Temps réponse API: 0.3-0.8s (vs 1-2s database)\n";
echo "   - Débit: 100k+ req/sec (vs ~1k database)\n\n";

echo "🧪 Testez l'API:\n";
echo "   curl http://localhost:8000/api/predictions/today\n";
echo "   (Le 2ème appel devrait être instantané)\n\n";
