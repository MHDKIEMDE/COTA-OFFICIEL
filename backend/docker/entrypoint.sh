#!/bin/sh
# Ne pas quitter sur erreur — on gère nous-mêmes les cas critiques
set -e

echo "📁 Permissions storage..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "🎨 Sync des assets compilés vers le volume public..."
# Les assets sont dans l'image à /var/www/build-assets
# On les copie dans le volume public à chaque démarrage
if [ -d /var/www/build-assets ]; then
    mkdir -p /var/www/html/public/build
    cp -rf /var/www/build-assets/. /var/www/html/public/build/
    echo "   ✅ Assets synchronisés"
else
    echo "   ⚠️  /var/www/build-assets absent — assets non synchronisés"
fi

# Migrations et caches uniquement pour le service principal (php-fpm)
if [ "$1" = "php-fpm" ]; then
    echo "🗄️  Migrations..."
    php artisan migrate --force --no-interaction || echo "   ⚠️  Migrations échouées (continuons quand même)"

    echo "🔧 Nettoyage des caches..."
    php artisan config:clear  || true
    php artisan route:clear   || true
    php artisan view:clear    || true

    echo "⚡ Caches production..."
    php artisan config:cache  || echo "   ⚠️  config:cache échoué"
    php artisan route:cache   || echo "   ⚠️  route:cache échoué"
    php artisan view:cache    || echo "   ⚠️  view:cache échoué (templates vérifiés à la volée)"

    echo "🔗 Storage link..."
    php artisan storage:link --force 2>/dev/null || true
fi

echo "🚀 Démarrage : $@"
exec "$@"
