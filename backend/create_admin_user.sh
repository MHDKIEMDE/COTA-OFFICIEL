#!/bin/bash

# Script pour créer l'utilisateur admin MHDK
# Usage: ./create_admin_user.sh [password]

PASSWORD=${1:-password123}

echo "👤 Création de l'utilisateur admin..."
echo "   Nom: MHDK"
echo "   Email: mkiemde00@gmail.com"
echo ""

# Exécuter la commande artisan
php artisan admin:create --name=MHDK --email=mkiemde00@gmail.com --password="$PASSWORD"

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Utilisateur admin créé avec succès !"
    echo ""
    echo "📋 Informations de connexion :"
    echo "   Email: mkiemde00@gmail.com"
    echo "   Mot de passe: $PASSWORD"
    echo "   Panel Admin: http://localhost:8000/admin"
    echo ""
    echo "⚠️  Changez le mot de passe après la première connexion !"
else
    echo ""
    echo "❌ Erreur lors de la création de l'utilisateur"
    exit 1
fi
