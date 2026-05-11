#!/bin/bash

# Script d'installation de Filament
# Usage: ./install_filament.sh

echo "📦 Installation de Filament Admin Panel..."

# Vérifier que composer est disponible
if ! command -v composer &> /dev/null; then
    echo "❌ Composer n'est pas installé ou n'est pas dans le PATH"
    echo "💡 Installez Composer depuis https://getcomposer.org/"
    exit 1
fi

# Installer Filament
echo "1️⃣ Installation de Filament..."
composer require filament/filament:"^3.2" -W

if [ $? -ne 0 ]; then
    echo "❌ Erreur lors de l'installation de Filament"
    exit 1
fi

# Installer le panel
echo "2️⃣ Configuration du panel..."
php artisan filament:install --panels

if [ $? -ne 0 ]; then
    echo "❌ Erreur lors de la configuration du panel"
    exit 1
fi

# Créer un utilisateur admin
echo "3️⃣ Création d'un utilisateur admin..."
echo "💡 Vous allez être invité à créer un utilisateur admin"
php artisan make:filament-user

echo "✅ Filament installé avec succès !"
echo "🌐 Accédez au panel admin : http://localhost:8000/admin"
