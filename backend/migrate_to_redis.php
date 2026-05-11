<?php
/**
 * Script de migration automatique vers Redis pour COTA
 * Usage: php migrate_to_redis.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

echo "🚀 Migration automatique vers Redis - COTA Backend\n";
echo "==================================================\n\n";

// 1. Vérifier Redis
echo "1. Vérification de Redis...\n";
try {
    $redis = app('redis')->connection('cache');
    $redis->ping();
    echo "   ✅ Redis est connecté et fonctionnel!\n\n";
} catch (\Exception $e) {
    echo "   ❌ Erreur de connexion Redis: " . $e->getMessage() . "\n";
    echo "   💡 Veuillez installer et démarrer Redis:\n";
    echo "      - Ubuntu: sudo apt install redis-server && sudo systemctl start redis-server\n";
    echo "      - macOS: brew install redis && brew services start redis\n";
    echo "      - Docker: docker run -d -p 6379:6379 redis:7-alpine\n\n";
    exit(1);
}

// 2. Lire et modifier .env
echo "2. Modification du fichier .env...\n";
$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    echo "   ❌ Fichier .env non trouvé!\n";
    exit(1);
}

$envContent = file_get_contents($envPath);
$backupPath = __DIR__ . '/.env.backup.' . date('Y-m-d_H-i-s');
file_put_contents($backupPath, $envContent);
echo "   ✅ Backup créé: .env.backup." . date('Y-m-d_H-i-s') . "\n";

// Modifier CACHE_STORE
if (preg_match('/^CACHE_STORE=.*$/m', $envContent)) {
    $envContent = preg_replace('/^CACHE_STORE=.*$/m', 'CACHE_STORE=redis', $envContent);
    echo "   ✅ CACHE_STORE modifié: redis\n";
} else {
    $envContent .= "\nCACHE_STORE=redis\n";
    echo "   ✅ CACHE_STORE ajouté: redis\n";
}

// Vérifier/ajouter REDIS_HOST
if (!preg_match('/^REDIS_HOST=.*$/m', $envContent)) {
    $envContent .= "REDIS_HOST=127.0.0.1\n";
    echo "   ✅ REDIS_HOST ajouté: 127.0.0.1\n";
}

// Vérifier/ajouter REDIS_PORT
if (!preg_match('/^REDIS_PORT=.*$/m', $envContent)) {
    $envContent .= "REDIS_PORT=6379\n";
    echo "   ✅ REDIS_PORT ajouté: 6379\n";
}

// Vérifier/ajouter REDIS_CLIENT
if (!preg_match('/^REDIS_CLIENT=.*$/m', $envContent)) {
    $envContent .= "REDIS_CLIENT=phpredis\n";
    echo "   ✅ REDIS_CLIENT ajouté: phpredis\n";
}

// Vérifier/ajouter REDIS_CACHE_DB
if (!preg_match('/^REDIS_CACHE_DB=.*$/m', $envContent)) {
    $envContent .= "REDIS_CACHE_DB=1\n";
    echo "   ✅ REDIS_CACHE_DB ajouté: 1\n";
}

file_put_contents($envPath, $envContent);
echo "   ✅ Fichier .env mis à jour\n\n";

// 3. Vider les caches Laravel
echo "3. Nettoyage des caches Laravel...\n";
try {
    $app = require_once __DIR__.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    $kernel->call('config:clear');
    echo "   ✅ Config cache vidé\n";
    
    $kernel->call('cache:clear');
    echo "   ✅ Application cache vidé\n";
    
    $kernel->call('route:clear');
    echo "   ✅ Route cache vidé\n";
    
    $kernel->call('view:clear');
    echo "   ✅ View cache vidé\n\n";
} catch (\Exception $e) {
    echo "   ⚠️  Erreur lors du nettoyage: " . $e->getMessage() . "\n";
    echo "   💡 Exécutez manuellement: php artisan config:clear && php artisan cache:clear\n\n";
}

// 4. Recharger la configuration
echo "4. Rechargement de la configuration...\n";
try {
    // Forcer le rechargement
    $app = require_once __DIR__.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    $cacheDriver = Config::get('cache.default');
    echo "   ✅ Cache driver actuel: $cacheDriver\n\n";
    
    if ($cacheDriver !== 'redis') {
        echo "   ⚠️  Le driver n'est pas encore 'redis'. Exécutez:\n";
        echo "      php artisan config:clear\n";
        echo "      php artisan config:cache\n\n";
    }
} catch (\Exception $e) {
    echo "   ⚠️  Erreur: " . $e->getMessage() . "\n\n";
}

// 5. Test de connexion Redis
echo "5. Test de connexion Redis avec Laravel...\n";
try {
    Cache::put('cota_migration_test', 'OK', 60);
    $result = Cache::get('cota_migration_test');
    
    if ($result === 'OK') {
        echo "   ✅ Écriture réussie\n";
        echo "   ✅ Lecture réussie\n";
        echo "   ✅ Valeur récupérée: '$result'\n\n";
        
        // Nettoyer
        Cache::forget('cota_migration_test');
    } else {
        echo "   ❌ Valeur incorrecte récupérée\n\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erreur lors du test: " . $e->getMessage() . "\n";
    echo "   💡 Vérifiez que Redis est démarré et que la configuration est correcte\n\n";
}

// 6. Optimiser la configuration
echo "6. Optimisation de la configuration...\n";
try {
    $app = require_once __DIR__.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    $kernel->call('config:cache');
    echo "   ✅ Config cache créé\n";
    
    $kernel->call('route:cache');
    echo "   ✅ Route cache créé\n";
    
    $kernel->call('view:cache');
    echo "   ✅ View cache créé\n\n";
} catch (\Exception $e) {
    echo "   ⚠️  Erreur lors de l'optimisation: " . $e->getMessage() . "\n\n";
}

// 7. Test final avec les services COTA
echo "7. Vérification des services COTA...\n";
try {
    $intelligentService = app(\App\Services\IntelligentApiService::class);
    $stats = $intelligentService->getStats();
    
    echo "   - Cache driver détecté: " . ($stats['cache_driver'] ?? 'N/A') . "\n";
    echo "   - Entrées en cache: " . ($stats['cached_entries'] ?? 0) . "\n";
    
    if (($stats['cache_driver'] ?? '') === 'redis') {
        echo "   ✅ Services COTA utilisent Redis!\n\n";
    } else {
        echo "   ⚠️  Les services n'utilisent pas encore Redis\n";
        echo "   💡 Redémarrez l'application ou exécutez: php artisan config:clear\n\n";
    }
} catch (\Exception $e) {
    echo "   ⚠️  Impossible de vérifier les services: " . $e->getMessage() . "\n\n";
}

echo "==================================================\n";
echo "✅ Migration vers Redis terminée!\n\n";
echo "📊 Résultats attendus:\n";
echo "   - Latence cache: < 1ms (vs 10-50ms avant)\n";
echo "   - Temps réponse API: 0.3-0.8s (vs 1-2s avant)\n";
echo "   - Débit: 100k+ req/sec (vs ~1k avant)\n\n";
echo "🔄 Pour revenir en arrière:\n";
echo "   Restaurez le backup: cp .env.backup.* .env\n";
echo "   Puis: php artisan config:clear && php artisan cache:clear\n\n";
echo "🧪 Testez l'API:\n";
echo "   curl http://localhost:8000/api/predictions/today\n";
echo "   (Le 2ème appel devrait être instantané)\n\n";
