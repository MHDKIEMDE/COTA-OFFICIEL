# 🚀 Guide de Migration Rapide vers Redis

## ⚡ Migration en 3 étapes

### Étape 1 : Installer Redis (si pas déjà fait)

**Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install redis-server
sudo systemctl start redis-server
sudo systemctl enable redis-server
```

**macOS:**
```bash
brew install redis
brew services start redis
```

**Vérifier:**
```bash
redis-cli ping
# Doit répondre: PONG
```

### Étape 2 : Modifier `.env`

Ouvrir `.env` et modifier/ajouter :

```env
# Cache - Passer à Redis
CACHE_STORE=redis

# Redis Configuration
REDIS_CLIENT=phpredis  # ou 'predis' si extension PHP Redis non disponible
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CACHE_DB=1
```

### Étape 3 : Appliquer les changements

```bash
cd /Users/massakambp12/Desktop/Projet/Cotas/cota-backend

# Vider les caches
php artisan config:clear
php artisan cache:clear

# Optimiser
php artisan config:cache
```

### Étape 4 : Tester

```bash
# Test rapide
php artisan tinker
```

Dans tinker :
```php
Cache::put('test_redis', 'OK', 60);
Cache::get('test_redis');  // Doit retourner 'OK'
```

## ✅ Vérification

### Test 1 : Connexion Redis
```bash
redis-cli ping
# PONG ✅
```

### Test 2 : Cache Laravel
```bash
php artisan tinker
Cache::put('test', 'ok', 60);
Cache::get('test');  // 'ok' ✅
```

### Test 3 : API
```bash
# Premier appel (peut être lent)
curl http://localhost:8000/api/predictions/today

# Deuxième appel (devrait être instantané avec Redis)
curl http://localhost:8000/api/predictions/today
```

## 📊 Résultats attendus

| Métrique | Avant (Database) | Après (Redis) |
|----------|------------------|---------------|
| Latence cache | 10-50ms | < 1ms ⚡ |
| Temps réponse API | 1-2s | 0.3-0.8s ⚡ |
| Débit | ~1k req/sec | 100k+ req/sec ⚡ |

## 🔄 Rollback (si problème)

Si besoin de revenir en arrière :

```env
CACHE_STORE=database
```

```bash
php artisan config:clear
php artisan cache:clear
```

## 🆘 Problèmes courants

### "Connection refused"
```bash
# Démarrer Redis
sudo systemctl start redis-server  # Ubuntu
brew services start redis          # macOS
```

### "Class 'Redis' not found"
```bash
# Installer extension PHP Redis
sudo apt install php-redis  # Ubuntu
pecl install redis          # macOS

# OU utiliser Predis (déjà installé)
# Dans .env: REDIS_CLIENT=predis
```

### "Permission denied"
```bash
# Vérifier les permissions
sudo chown redis:redis /var/lib/redis
```

---

**Temps estimé** : 5 minutes
**Risque** : Faible (rollback facile)
**Gain** : 60-80% de performance ⚡
