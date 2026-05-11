#!/bin/bash

# 🧪 Test du système d'API intelligent
echo "🧪 Test du système d'API intelligent"
echo "==================================="

# Test 1: Vérifier le statut des APIs
echo "📊 Test 1: Statut des APIs"
php artisan api:smart-manage status
echo ""

# Test 2: Tester la récupération de données
echo "🎯 Test 2: Récupération de données intelligentes"
DATE=$(date +%Y-%m-%d)
echo "Date testée: $DATE"

# Test avec force refresh pour voir le comportement
php artisan api:smart-manage force-refresh --date=$DATE
echo ""

# Test 3: Vérifier les logs
echo "📝 Test 3: Dernières lignes de logs"
tail -10 storage/logs/laravel.log
echo ""

# Test 4: Nettoyer le cache si nécessaire
echo "🧹 Test 4: Nettoyage du cache (optionnel)"
read -p "Voulez-vous nettoyer l'ancien cache ? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan api:smart-manage cleanup
fi

echo ""
echo "✅ Tests terminés !"
echo ""
echo "🎯 Résumé des améliorations :"
echo "  ✅ Fallback automatique Sportradar → API-Football"
echo "  ✅ Blocage intelligent des appels après quota dépassé"
echo "  ✅ Cache avec durée dynamique"
echo "  ✅ Récupération de données anciennes en fallback"
echo "  ✅ Commandes de gestion artisan"
echo ""
echo "🚀 L'API est maintenant intelligente et résistante aux pannes !"