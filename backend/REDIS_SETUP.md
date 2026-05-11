# 🚀 Configuration Redis pour COTA Backend

## 📋 Vue d'ensemble

Redis améliore significativement les performances du cache par rapport à la base de données :
- **Latence** : < 1ms vs 5-50ms (database)
- **Débit** : 100k+ ops/sec vs ~1k ops/sec
- **Mémoire** : Optimisée pour le cache
- **Expiration automatique** : TTL natif

## 🔧 Installation

### 1. Installation Redis sur le serveur

#### Ubuntu/Debian
```bash
sudo apt update
sudo apt install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

#### macOS (Homebrew)
```bash
brew install redis
brew services start redis
```

#### Docker
```bash
docker run -d --name redis -p 6379:6379 redis:7-alpine
```

### 2. Vérification de l'installation

```bash
redis-cli ping
# Devrait répondre: PONG
```

### 3. Configuration Laravel

#### Fichier `.env`

```env
# Cache Driver - Passer à Redis
CACHE_STORE=redis

# Redis Configuration
REDIS_CLIENT=phpredis  # ou 'predis' si extension PHP Redis non disponible
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Bases de données Redis (séparation cache/queue/sessions)
REDIS_DB=0              # Base par défaut
REDIS_CACHE_DB=1        # Cache (recommandé)
REDIS_QUEUE_DB=2        # Queues (recommandé)

# Sessions (optionnel mais recommandé)
SESSION_DRIVER=redis

# Queues (optionnel mais recommandé)
QUEUE_CONNECTION=redis
```

#### Extension PHP Redis (recommandé)

**Ubuntu/Debian:**
```bash
sudo apt install php-redis
sudo systemctl restart php8.3-fpm  # ou votre version PHP
```

**macOS:**
```bash
pecl install redis
```

**Vérification:**
```bash
php -m | grep redis
```

Si l'extension n'est pas disponible, utiliser `predis` (déjà installé via Composer).

### 4. Vider le cache Laravel

```bash
php artisan cache:clear
php artisan config:clear
php artisan optimize:clear
```

### 5. Test de connexion Redis

```bash
php artisan tinker
```

```php
Cache::store('redis')->put('test', 'Redis fonctionne!', 60);
Cache::store('redis')->get('test');
// Devrait retourner: "Redis fonctionne!"
```

## 📊 Optimisations Redis

### Configuration Redis (`/etc/redis/redis.conf`)

```conf
# Mémoire maximale (ajuster selon votre serveur)
maxmemory 256mb

# Politique d'éviction (LRU recommandé)
maxmemory-policy allkeys-lru

# Persistance (optionnel pour cache)
save ""  # Désactiver la persistance pour cache uniquement

# Performance
tcp-backlog 511
timeout 0
tcp-keepalive 300
```

### Commandes utiles Redis

```bash
# Voir toutes les clés du cache COTA
redis-cli KEYS "cota-cache-*"

# Voir la taille de la mémoire utilisée
redis-cli INFO memory

# Vider le cache
redis-cli FLUSHDB

# Monitorer les commandes en temps réel
redis-cli MONITOR
```

## 🎯 Avantages pour COTA

### 1. Performance des appels API

**Avant (Database cache):**
- Lecture cache : ~10-20ms
- Écriture cache : ~15-30ms
- Limite : ~1000 req/sec

**Après (Redis cache):**
- Lecture cache : < 1ms
- Écriture cache : < 1ms
- Limite : 100k+ req/sec

### 2. Temps de chargement réduit

| Scénario | Database Cache | Redis Cache | Gain |
|----------|---------------|-------------|------|
| Premier chargement | 2-4s | 2-4s | - |
| Avec cache | 1-2s | **0.3-0.8s** | **60-70%** |
| Refresh live | 200-500ms | **50-150ms** | **70-75%** |
| Recherche | 500ms-1s | **100-300ms** | **70-80%** |

### 3. Gestion du cache intelligent

Redis permet :
- **TTL automatique** : Expiration native
- **LRU eviction** : Suppression automatique des anciennes clés
- **Atomic operations** : Opérations thread-safe
- **Pub/Sub** : Pour cache distribué (futur)

## 🔍 Monitoring Redis

### Commandes de monitoring

```bash
# Statistiques générales
redis-cli INFO stats

# Mémoire utilisée
redis-cli INFO memory | grep used_memory_human

# Nombre de clés
redis-cli DBSIZE

# Hit rate (si configuré)
redis-cli INFO stats | grep keyspace_hits
```

### Intégration Laravel

Créer un artisan command pour monitorer :

```bash
php artisan make:command RedisStats
```

## ⚠️ Points d'attention

### 1. Mémoire

Redis stocke tout en mémoire. Surveiller :
- `maxmemory` : Limite configurée
- `used_memory` : Mémoire actuellement utilisée
- `evicted_keys` : Nombre de clés évincées

### 2. Persistance

Pour un cache uniquement, la persistance n'est pas nécessaire :
```conf
save ""  # Désactiver dans redis.conf
```

### 3. Sécurité (Production)

```conf
# Dans redis.conf
bind 127.0.0.1  # Écouter uniquement localhost
requirepass votre_mot_de_passe_fort
```

Puis dans `.env`:
```env
REDIS_PASSWORD=votre_mot_de_passe_fort
```

## 🚀 Migration depuis Database Cache

### 1. Sauvegarder les données importantes

```bash
# Exporter les clés importantes (si nécessaire)
php artisan tinker
```

```php
// Sauvegarder certaines clés
$keys = ['important_key_1', 'important_key_2'];
foreach ($keys as $key) {
    $value = Cache::get($key);
    // Sauvegarder dans fichier ou DB
}
```

### 2. Basculer vers Redis

```env
CACHE_STORE=redis
```

### 3. Vérifier le fonctionnement

```bash
php artisan cache:clear
php artisan config:clear
php artisan optimize
```

### 4. Tester les endpoints

```bash
curl http://localhost:8000/api/predictions/today
# Devrait être plus rapide au 2ème appel
```

## 📈 Métriques attendues

Après migration vers Redis :

- **Temps de réponse API** : -60 à -80%
- **Charge serveur** : -40 à -60%
- **Débit** : +1000% (100k+ req/sec)
- **Latence cache** : < 1ms (vs 10-50ms)

## 🔄 Rollback (si problème)

Si besoin de revenir à database cache :

```env
CACHE_STORE=database
```

```bash
php artisan cache:clear
php artisan config:clear
```

---

**Dernière mise à jour** : Configuration optimisée pour COTA
**Version Redis recommandée** : 7.0+
