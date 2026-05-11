#!/bin/bash

# Script pour exécuter les migrations
# Usage: ./run_migrations.sh

echo "🗄️  Exécution des migrations..."

# Vérifier que artisan est disponible
if [ ! -f "artisan" ]; then
    echo "❌ Fichier artisan non trouvé. Êtes-vous dans le répertoire cota-backend ?"
    exit 1
fi

# Exécuter les migrations
php artisan migrate

if [ $? -ne 0 ]; then
    echo "❌ Erreur lors de l'exécution des migrations"
    exit 1
fi

echo "✅ Migrations exécutées avec succès !"
