# Skill — Laravel Development (COTA Backend)

## Contexte

Ce skill s'applique au projet Laravel 12 `backend/` de l'application COTA.

## Conventions du projet

- **Auth** : Laravel Sanctum (tokens stateless)
- **Cache / Queue** : Redis via Predis
- **HTTP client externe** : Guzzle (via `FootballApiService`)
- **Format de réponse** : JSON avec clés snake_case
- **Dates** : Carbon, timezone `Africa/Ouagadougou`

## Patterns à respecter

### Ajouter un endpoint

1. `php artisan make:controller Api/XxxController`
2. Ajouter la route dans `routes/api.php` (groupe public ou `auth:sanctum`)
3. Valider avec `php artisan make:request StoreXxxRequest`
4. Retourner `response()->json($data, $statusCode)`

### Ajouter un critère de prédiction

1. Ajouter la constante de poids dans `PredictionAlgorithmService::WEIGHTS`
2. Créer `calculateXxxScore(): float` (retourne 0 à max_weight)
3. Inclure dans `generatePrediction()`
4. Mettre à jour `generateAnalysis()`
5. Tester avec `php artisan prediction:test`

### Job / Scheduler

```bash
php artisan make:job XxxJob
# Enregistrer dans routes/console.php
Schedule::job(new XxxJob())->daily();
```

## Quota API-Football (plan gratuit)

- 100 req/jour, 30 req/min
- Toujours utiliser le cache avant d'appeler l'API
- Vérifier `$footballApi->getUsageStats()` avant les opérations en masse

## Commandes utiles

```bash
php artisan prediction:test          # tester l'algorithme
php artisan prediction:coupon        # générer le coupon IA du jour
php artisan football:fetch-today     # synchroniser les matchs
php artisan schedule:work            # lancer le scheduler en dev
php artisan queue:work               # lancer le worker Redis
./vendor/bin/pint                    # formater le code
```
