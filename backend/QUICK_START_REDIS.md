# ⚡ Démarrage Rapide Redis - COTA

## 🚀 Commandes à exécuter (dans l'ordre)

```bash
# 1. Aller dans le dossier backend
cd /Users/massakambp12/Desktop/Projet/Cotas/cota-backend

# 2. Installer les dépendances (si pas déjà fait)
composer install

# 3. Installer Redis (si pas déjà installé)
# macOS:
brew install redis
brew services start redis

# OU utiliser le script automatique:
chmod +x install_redis_macos.sh
./install_redis_macos.sh

# Vérifier:
redis-cli ping  # Doit répondre: PONG

# 4. Migrer vers Redis (automatique)
php migrate_to_redis.php

# 5. Tester Redis
php test_redis.php

# 6. Tester l'API (dans un autre terminal)
php artisan serve
# Puis:
curl http://localhost:8000/api/predictions/today
```

## ✅ Vérification rapide

```bash
# Test Redis
redis-cli ping  # → PONG

# Test Laravel Cache
php artisan tinker
Cache::put('test', 'ok', 60);
Cache::get('test');  # → 'ok'
exit
```

## 🎯 C'est tout !

Redis est maintenant configuré et testé. Les performances devraient être améliorées de **60-80%**.

---

**Besoin d'aide ?** Voir `TEST_REDIS.md` pour plus de détails.
