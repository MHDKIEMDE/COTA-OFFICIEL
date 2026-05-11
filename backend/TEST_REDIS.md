# 🧪 Test Redis - Guide Rapide

## 📦 Étape 1 : Publier les dépendances

```bash
cd /Users/massakambp12/Desktop/Projet/Cotas/cota-backend

# Installer/mettre à jour les dépendances
composer install

# Vérifier que predis est installé
composer show predis/predis
```

✅ **Predis est déjà dans `composer.json`** (ligne 17), donc `composer install` l'installera automatiquement.

## 🚀 Étape 2 : Installer et démarrer Redis

### macOS
```bash
brew install redis
brew services start redis
```

### Ubuntu/Debian
```bash
sudo apt install redis-server
sudo systemctl start redis-server
```

### Vérifier
```bash
redis-cli ping
# Doit répondre: PONG
```

## 🔧 Étape 3 : Configurer Redis dans Laravel

### Option A : Script automatique
```bash
php migrate_to_redis.php
```

### Option B : Manuel
Modifier `.env` :
```env
CACHE_STORE=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_CACHE_DB=1
```

Puis :
```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

## 🧪 Étape 4 : Exécuter les tests

### Test complet
```bash
php test_redis.php
```

### Test rapide
```bash
php artisan tinker
```

Dans tinker :
```php
Cache::put('test', 'ok', 60);
Cache::get('test');  // Doit retourner 'ok'
exit
```

## 🎯 Étape 5 : Test avec l'API

```bash
# Démarrer le serveur (si pas déjà démarré)
php artisan serve

# Dans un autre terminal, tester l'API
curl http://localhost:8000/api/predictions/today

# Le 2ème appel devrait être beaucoup plus rapide (cache Redis)
curl http://localhost:8000/api/predictions/today
```

## ✅ Checklist de vérification

- [ ] Redis installé et démarré (`redis-cli ping` → PONG)
- [ ] Dépendances installées (`composer install`)
- [ ] `.env` configuré avec `CACHE_STORE=redis`
- [ ] Caches vidés (`php artisan config:clear && php artisan cache:clear`)
- [ ] Test Redis réussi (`php test_redis.php`)
- [ ] API répond rapidement au 2ème appel

## 📊 Résultats attendus

Après migration vers Redis :

| Métrique | Avant | Après |
|----------|-------|-------|
| Latence cache | 10-50ms | < 1ms ⚡ |
| Temps réponse API | 1-2s | 0.3-0.8s ⚡ |
| Débit | ~1k req/sec | 100k+ req/sec ⚡ |

---

**Temps total** : 5-10 minutes
**Gain de performance** : 60-80% ⚡
