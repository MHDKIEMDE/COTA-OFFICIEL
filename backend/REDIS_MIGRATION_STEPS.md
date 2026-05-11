# 🚀 Migration Redis - Étapes à Exécuter

## ⚡ Commandes à Exécuter dans le Terminal

### 1. Vérifier que Redis est installé et démarré

```bash
redis-cli ping
# Doit répondre: PONG
```

Si pas installé:
```bash
# macOS
brew install redis
brew services start redis

# Ubuntu/Debian
sudo apt install redis-server
sudo systemctl start redis-server
```

### 2. Exécuter le script de migration

```bash
cd /Users/massakambp12/Desktop/Projet/Cotas/cota-backend
php migrate_to_redis.php
```

### 3. OU Modifier manuellement le .env

Ouvrir `/Users/massakambp12/Desktop/Projet/Cotas/cota-backend/.env` et ajouter/modifier:

```env
CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_CACHE_DB=1
```

### 4. Vider les caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 5. Optimiser

```bash
php artisan config:cache
php artisan route:cache
```

### 6. Tester

```bash
php artisan tinker
```

Dans tinker:
```php
Cache::put('test', 'ok', 60);
Cache::get('test');  // Doit retourner 'ok'
exit
```

### 7. Tester l'API

```bash
# Premier appel (peut être lent)
curl http://localhost:8000/api/predictions/today

# Deuxième appel (devrait être instantané avec Redis)
curl http://localhost:8000/api/predictions/today
```

## ✅ Vérification

Le cache driver devrait être `redis`:
```bash
php artisan tinker
config('cache.default');  // Doit retourner 'redis'
```

## 🔄 Rollback (si problème)

```bash
# Restaurer le backup
cp .env.backup.* .env

# Vider les caches
php artisan config:clear
php artisan cache:clear
```

---

**Temps estimé**: 2-3 minutes
**Gain de performance**: 60-80% ⚡
