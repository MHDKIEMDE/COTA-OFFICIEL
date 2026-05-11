# 🔄 Guide de Migration vers Redis - COTA

## ✅ Checklist de Migration

### Étape 1 : Vérification préalable

- [ ] Redis installé et fonctionnel (`redis-cli ping`)
- [ ] Extension PHP Redis ou Predis disponible
- [ ] Backup de la base de données (au cas où)
- [ ] Accès au fichier `.env`

### Étape 2 : Configuration

1. **Modifier `.env`** :
```env
CACHE_STORE=redis
REDIS_CLIENT=phpredis  # ou 'predis'
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_CACHE_DB=1
```

2. **Vérifier la configuration Redis** :
```bash
php artisan tinker
```
```php
config('cache.default');  // Doit retourner 'redis'
config('database.redis.cache.host');  // Doit retourner '127.0.0.1'
```

### Étape 3 : Test de connexion

```bash
php artisan cache:clear
php artisan tinker
```

```php
// Test écriture
Cache::put('test_redis', 'OK', 60);

// Test lecture
Cache::get('test_redis');  // Doit retourner 'OK'

// Test avec le service intelligent
$service = app(\App\Services\IntelligentApiService::class);
$stats = $service->getStats();
// Vérifier que 'cache_driver' = 'redis'
```

### Étape 4 : Vérification des services

Les services suivants utilisent déjà Redis si disponible :
- ✅ `IntelligentApiService` - Détection automatique
- ✅ `FootballDataService` - Détection automatique
- ✅ `SmartApiManager` - Détection automatique

### Étape 5 : Optimisation

```bash
# Vider les anciens caches
php artisan cache:clear

# Optimiser la configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Étape 6 : Monitoring

```bash
# Vérifier les clés Redis
redis-cli KEYS "cota-cache-*"

# Vérifier la mémoire
redis-cli INFO memory

# Monitorer en temps réel
redis-cli MONITOR
```

## 🎯 Résultats attendus

### Avant (Database Cache)
```
Temps de réponse API (avec cache) : 1-2 secondes
Latence cache : 10-50ms
Débit max : ~1000 req/sec
```

### Après (Redis Cache)
```
Temps de réponse API (avec cache) : 0.3-0.8 secondes ⚡
Latence cache : < 1ms ⚡
Débit max : 100k+ req/sec ⚡
```

## ⚠️ Problèmes courants

### 1. "Connection refused"
```bash
# Vérifier que Redis tourne
sudo systemctl status redis-server
# ou
redis-cli ping
```

### 2. "Class 'Redis' not found"
```bash
# Installer l'extension PHP Redis
sudo apt install php-redis
# ou utiliser Predis
REDIS_CLIENT=predis
```

### 3. "Permission denied"
```bash
# Vérifier les permissions Redis
sudo chown redis:redis /var/lib/redis
```

## 📊 Commandes de vérification

```bash
# 1. Test Redis
redis-cli ping

# 2. Test Laravel
php artisan cache:clear
php artisan tinker
Cache::put('test', 'ok', 60);
Cache::get('test');

# 3. Test API
curl http://localhost:8000/api/predictions/today
# 2ème appel devrait être instantané

# 4. Vérifier les stats
php artisan smart-api:status
# Cache driver devrait être 'redis'
```

## 🔄 Rollback

Si problème, revenir à database cache :

```env
CACHE_STORE=database
```

```bash
php artisan cache:clear
php artisan config:clear
```

---

**Temps estimé de migration** : 5-10 minutes
**Risque** : Faible (rollback facile)
**Gain de performance** : 60-80%
