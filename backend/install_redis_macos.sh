#!/bin/bash

# Script d'installation Redis pour macOS
# Usage: ./install_redis_macos.sh

echo "🍺 Installation Redis pour macOS"
echo "================================="
echo ""

# Vérifier si Homebrew est installé
if ! command -v brew &> /dev/null; then
    echo "❌ Homebrew n'est pas installé"
    echo ""
    echo "💡 Installez Homebrew d'abord:"
    echo "   /bin/bash -c \"\$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)\""
    echo ""
    exit 1
fi

echo "✅ Homebrew détecté"
echo ""

# Installer Redis
echo "1. Installation de Redis..."
brew install redis

if [ $? -eq 0 ]; then
    echo "   ✅ Redis installé avec succès"
else
    echo "   ❌ Erreur lors de l'installation"
    exit 1
fi
echo ""

# Démarrer Redis
echo "2. Démarrage de Redis..."
brew services start redis

if [ $? -eq 0 ]; then
    echo "   ✅ Redis démarré"
else
    echo "   ⚠️  Erreur lors du démarrage, essayez manuellement:"
    echo "      redis-server"
    exit 1
fi
echo ""

# Attendre un peu pour que Redis démarre
sleep 2

# Tester la connexion
echo "3. Test de connexion..."
if redis-cli ping &> /dev/null; then
    echo "   ✅ Redis fonctionne! (PONG)"
else
    echo "   ❌ Redis ne répond pas"
    echo "   💡 Essayez: redis-server"
    exit 1
fi
echo ""

echo "================================="
echo "✅ Redis est installé et fonctionnel!"
echo ""
echo "📝 Commandes utiles:"
echo "   - Démarrer: brew services start redis"
echo "   - Arrêter: brew services stop redis"
echo "   - Redémarrer: brew services restart redis"
echo "   - Tester: redis-cli ping"
echo ""
