# 🍺 Installation Redis sur macOS

## ⚡ Installation Rapide

### Option 1 : Script automatique
```bash
cd /Users/massakambp12/Desktop/Projet/Cotas/cota-backend
chmod +x install_redis_macos.sh
./install_redis_macos.sh
```

### Option 2 : Manuel

#### Étape 1 : Vérifier Homebrew
```bash
brew --version
```

Si Homebrew n'est pas installé :
```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

#### Étape 2 : Installer Redis
```bash
brew install redis
```

#### Étape 3 : Démarrer Redis
```bash
brew services start redis
```

#### Étape 4 : Vérifier
```bash
redis-cli ping
# Doit répondre: PONG
```

## ✅ Vérification

```bash
# Test de connexion
redis-cli ping
# → PONG ✅

# Vérifier le statut
brew services list | grep redis
# → redis started ✅
```

## 🔧 Commandes utiles

```bash
# Démarrer Redis
brew services start redis

# Arrêter Redis
brew services stop redis

# Redémarrer Redis
brew services restart redis

# Voir les logs
tail -f /usr/local/var/log/redis.log

# Accéder à Redis CLI
redis-cli
```

## 🚨 Problèmes courants

### "command not found: brew"
Installez Homebrew d'abord (voir ci-dessus).

### "Could not connect to Redis"
```bash
# Vérifier que Redis tourne
brew services list | grep redis

# Si pas démarré, démarrer
brew services start redis

# Ou démarrer manuellement
redis-server
```

### "Port 6379 already in use"
```bash
# Trouver le processus
lsof -i :6379

# Arrêter Redis
brew services stop redis

# Redémarrer
brew services start redis
```

## 🎯 Après installation

Une fois Redis installé, continuez avec :

```bash
# 1. Migrer vers Redis
php migrate_to_redis.php

# 2. Tester
php test_redis.php
```

---

**Temps d'installation** : 2-3 minutes
**Taille** : ~5 MB
