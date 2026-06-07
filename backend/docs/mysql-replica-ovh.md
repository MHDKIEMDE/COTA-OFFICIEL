# MySQL Read Replica — OVH VPS

## Architecture cible

```
Mobile Flutter
      │
      ▼
  Laravel API (VPS Master)
      │
      ├── INSERT / UPDATE / DELETE ──► MySQL Master  (VPS-1 : même machine ou IP dédiée)
      └── SELECT ──────────────────►  MySQL Replica (VPS-2 : second VPS OVH)
```

Laravel route automatiquement grâce à `config/database.php` :
- `DB_HOST` → master (écritures)
- `DB_READ_HOST` → replica (lectures, ~80% des requêtes)

---

## Étape 1 — Configurer le Master (VPS-1)

### 1.1 Activer le binary log

Éditer `/etc/mysql/mysql.conf.d/mysqld.cnf` (Ubuntu) ou `/etc/my.cnf` (CentOS) :

```ini
[mysqld]
server-id          = 1
log_bin            = /var/log/mysql/mysql-bin.log
binlog_expire_logs_seconds = 604800   # 7 jours
max_binlog_size    = 100M
binlog_format      = ROW
bind-address       = 0.0.0.0          # écouter sur toutes les interfaces
```

Redémarrer MySQL :
```bash
sudo systemctl restart mysql
```

### 1.2 Créer l'utilisateur de réplication

```sql
-- Sur le master
CREATE USER 'replicator'@'REPLICA_IP' IDENTIFIED BY 'MOT_DE_PASSE_FORT';
GRANT REPLICATION SLAVE ON *.* TO 'replicator'@'REPLICA_IP';
FLUSH PRIVILEGES;
```

### 1.3 Créer l'utilisateur lecture seule pour Laravel

```sql
CREATE USER 'cota_reader'@'REPLICA_IP' IDENTIFIED BY 'MOT_DE_PASSE_READER';
GRANT SELECT ON cota.* TO 'cota_reader'@'REPLICA_IP';
FLUSH PRIVILEGES;
```

### 1.4 Snapshot initial de la DB

```bash
# Verrouiller les tables et noter la position du binlog
mysql -u root -p -e "FLUSH TABLES WITH READ LOCK; SHOW MASTER STATUS\G"
# Noter : File et Position

# Dans un autre terminal — exporter la DB
mysqldump -u root -p --single-transaction --master-data=2 cota > /tmp/cota_snapshot.sql

# Déverrouiller
mysql -u root -p -e "UNLOCK TABLES;"

# Copier le dump sur le replica
scp /tmp/cota_snapshot.sql user@REPLICA_IP:/tmp/
```

---

## Étape 2 — Configurer le Replica (VPS-2)

### 2.1 Configurer MySQL

Éditer `/etc/mysql/mysql.conf.d/mysqld.cnf` :

```ini
[mysqld]
server-id          = 2
relay_log          = /var/log/mysql/mysql-relay-bin.log
log_bin            = /var/log/mysql/mysql-bin.log
read_only          = 1          # Empêche les écritures directes sur le replica
super_read_only    = 1
```

Redémarrer MySQL :
```bash
sudo systemctl restart mysql
```

### 2.2 Importer le snapshot

```bash
mysql -u root -p -e "CREATE DATABASE cota CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p cota < /tmp/cota_snapshot.sql
```

### 2.3 Démarrer la réplication

```sql
-- Récupérer File et Position depuis le dump (ligne CHANGE MASTER en haut du .sql)
-- ou utiliser les valeurs notées à l'étape 1.4

CHANGE MASTER TO
    MASTER_HOST     = 'MASTER_IP',
    MASTER_USER     = 'replicator',
    MASTER_PASSWORD = 'MOT_DE_PASSE_FORT',
    MASTER_LOG_FILE = 'mysql-bin.000001',   -- valeur notée à l'étape 1.4
    MASTER_LOG_POS  = 12345;                -- valeur notée à l'étape 1.4

START SLAVE;
```

### 2.4 Vérifier la réplication

```sql
SHOW SLAVE STATUS\G
```

Vérifier :
- `Slave_IO_Running: Yes`
- `Slave_SQL_Running: Yes`
- `Seconds_Behind_Master: 0`  (lag en secondes)

---

## Étape 3 — Configurer Laravel (`.env` prod)

```env
DB_CONNECTION=mysql
DB_HOST=MASTER_IP
DB_PORT=3306
DB_DATABASE=cota
DB_USERNAME=cota_user
DB_PASSWORD=MOT_DE_PASSE_USER

# Read Replica
DB_READ_HOST=REPLICA_IP
DB_READ_PORT=3306
DB_READ_USER=cota_reader
DB_READ_PASS=MOT_DE_PASSE_READER
```

`sticky=true` est déjà configuré dans `config/database.php` : après un INSERT,
les SELECT suivants dans la même requête iront sur le master (évite le lag replica).

---

## Étape 4 — Ouvrir le firewall OVH

Dans le Manager OVH → Firewall ou `ufw` sur le VPS :

```bash
# Sur le Master : autoriser le replica à se connecter sur le port MySQL
sudo ufw allow from REPLICA_IP to any port 3306

# Sur le Replica : autoriser Laravel (si Laravel est sur une 3e machine)
sudo ufw allow from APP_IP to any port 3306
```

---

## Vérification finale

```bash
# Test depuis le VPS app
mysql -h REPLICA_IP -u cota_reader -p cota -e "SELECT COUNT(*) FROM predictions;"

# Test Laravel
php artisan tinker
DB::select('SELECT COUNT(*) as c FROM predictions');   # → replica
DB::statement('UPDATE users SET updated_at = NOW() WHERE id = 1');  # → master
```

---

## Monitoring réplication

Ajouter dans le scheduler (`routes/console.php`) pour alerter si le lag dépasse 30s :

```php
Schedule::call(function () {
    $status = DB::select("SHOW SLAVE STATUS");
    $lag = $status[0]->Seconds_Behind_Master ?? null;
    if ($lag === null || $lag > 30) {
        Log::error("MySQL replica lag critique", ['lag' => $lag]);
        // Optionnel : envoyer alerte Sentry
        \Sentry\captureMessage("MySQL replica lag: {$lag}s");
    }
})->everyFiveMinutes()->name('check-replica-lag');
```
  ⎿  (timeout 2m)

⏺ Le job de génération tourne en arrière-plan. Je vais attendre sa complétion.

  Read 1 file (ctrl+o to expand)

⏺ Bash(cd "/Users/massakambp12/Desktop/Massaka SAS/COTA/backend" && php artisan tinker --execute="
      \$count = App\Models\Prediction::whereDate('match_date', now()->toDa…)
  ⎿  Prédictions publiées aujourd'hui: 181
     Jobs échoués: 0

⏺ 181 prédictions publiées aujourd'hui, 0 job échoué. Le bug est résolu.

  ---
  Résumé du problème :
  
  L'API-Football renvoyait fixtures.wins.away sous forme de tableau ({"home": 3, "away": 5, "total": 8}) au lieu d'un entier simple. Le code faisait (int) 
  $stats['fixtures']['wins']['away'] qui lançait un TypeError: Unsupported operand types: array * int.

  Correction appliquée (PredictionAlgorithmService.php) :
  - Ajout d'une méthode safeInt() qui détecte si la valeur est un tableau et extrait total ou home/away selon le contexte
  - Les méthodes extractHomeWinRatio() et extractAwayWinRatio() utilisent maintenant safeInt() au lieu de (int)

  Actions manuelles effectuées :
  1. FetchMatchesJob → 826 matchs récupérés
  2. GenerateAllPredictionsJob → 181 prédictions publiées pour aujourd'hui
  
  Les prédictions devraient maintenant apparaître dans l'app mobile.

✻ Sautéed for 7m 22s · 2 shells still running

❯ On devais avoir des historique de coupon pour hier aussi explique moi la signification de contredie                                                                                   

⏺ Je vais d'abord vérifier l'état des coupons en base.

⏺ Bash(cd "/Users/massakambp12/Desktop/Massaka SAS/COTA/backend" && php artisan tinker --execute="
      \$dates = \App\Models\CombinedBet::selectRaw('DATE(date) as d, count…)
  ⎿     Error  Class "App\Models\CombinedBet" not found.

⏺ Bash(cd "/Users/massakambp12/Desktop/Massaka SAS/COTA/backend" && php artisan tinker --execute="
      \$tables = \DB::select('SHOW TABLES');…)
  ⎿  combined_bets
     user_coupons
     winning_coupons

⏺ Bash(cd "/Users/massakambp12/Desktop/Massaka SAS/COTA/backend" && php artisan tinker --execute="
      // Coupons combinés…)
  ⎿  === combined_bets ===
     === user_coupons ===

⏺ Bash(cd "/Users/massakambp12/Desktop/Massaka SAS/COTA/backend" && php artisan tinker --execute="
      // Voir la structure de combined_bets…)
  ⎿  id (bigint(20) unsigned)
     type (enum('daily','welcome'))
     date (date)
     … +13 lines (ctrl+o to expand)

⏺ Les tables existent mais sont complètement vides — aucun coupon n'a jamais été généré.

  ---
  Sur ta question : que signifie "contredie" ?
  
  Tu veux dire "contredire" — voilà l'explication :

  ---
  Contradiction dans la logique actuelle
  
  Le problème c'est une incohérence entre ce que tu vois côté mobile et ce qui existe vraiment en base :

  ┌──────────────────────────────────┬──────────────────────────────────────────────────┐
  │   Ce que l'app mobile affiche    │              Ce qui existe en base               │
  ├──────────────────────────────────┼──────────────────────────────────────────────────┤
  │ Onglet "Coupons" avec historique │ Table combined_bets vide                         │
  ├──────────────────────────────────┼──────────────────────────────────────────────────┤
  │ Prédictions d'hier visibles      │ 1317 prédictions en base, mais combined_bets = 0 │
  └──────────────────────────────────┴──────────────────────────────────────────────────┘

  La contradiction : l'app mobile a un écran "Mes Coupons" qui attend des données de combined_bets, mais le job GenerateAllPredictionsJob génère des prédictions individuelles sans 
  jamais créer de coupon combiné dans cette table.

  Deux systèmes parallèles qui ne se parlent pas :
  1. predictions — bien remplie (1317 lignes)
  2. combined_bets — jamais alimentée 
  
  Pourquoi il n'y a pas de coupons pour hier ? Parce que la logique de création du coupon (sélectionner les 4–5 meilleures prédictions et les sauvegarder dans combined_bets) n'a jamais
   été branchée au job de génération.

  ---
  Veux-tu que je connecte la génération du coupon au job existant pour que chaque jour, après avoir créé les prédictions, il génère automatiquement le coupon combiné dans combined_bets
   ?

✻ Brewed for 31s · 2 shells still running

❯ Lidee c;esr aussi d'avoir des lecon sur la manier hier on na combiener de sorte a ameliore demain donc si possible une section d'historique de coupon pour me permettre de savoire si 
   le pick d'hier est valider ou pas et pourquoi avec une expliquation et uine lecon a retenir                                                                                          
  
⏺ Bonne idée. Laisse-moi d'abord lire ce qui existe avant de proposer quoi que ce soit.

⏺ Bash(cd "/Users/massakambp12/Desktop/Massaka SAS/COTA/backend" && php artisan tinker --execute="
      // Voir la structure des prédictions pour comprendre ce qu'on a…)
  ⎿  prediction (varchar(255)) default=
     odds (decimal(5,2)) default=
     confidence_stars (int(11)) default=1
     … +27 lines (ctrl+o to expand)

⏺ Bash(cd "/Users/massakambp12/Desktop/Massaka SAS/COTA/backend" && php artisan tinker --execute="
      // Voir un exemple de prédiction terminée (résultat connu)…)
  ⎿     Illuminate\Database\QueryException  SQLSTATE[42S22]: Column not found: 1054 Unknown column 'result' in 'where clause' (Connection: mysql, Socket: /Applications/MAMP/tmp/my
     sql/mysql.sock, Database: COTA_DB, SQL: select * from `predictions` where `result` is not null and `result` != pending order by `created_at` desc limit 1).

⏺ Bash(cd "/Users/massakambp12/Desktop/Massaka SAS/COTA/backend" && php artisan tinker --execute="
      // Prédictions avec statut won/lost (résultats connus)…)
  ⎿  Won: 0 | Lost: 0 | Pending: 1734

⏺ Bash(cd "/Users/massakambp12/Desktop/Massaka SAS/COTA/backend" && php artisan tinker --execute="
      // Voir les matchs terminés avec leurs scores…)
  ⎿  Matchs terminés: 209
     Prédictions pending sur match terminé: 0

⏺ Bash(cd "/Users/massakambp12/Desktop/Massaka SAS/COTA/backend" && php artisan tinker --execute="
      // La relation entre predictions et matches…)
  ⎿  Colonnes clé: {"id":4457,"match_id":"1539926","match_date":"2026-05-24T15:00:00.000000Z","home_team":"Grafi\u010dar","away_team":"FK Trayal","prediction":"1","bet_type":"1X2"
     ,"odds":"2.17","status":"pending","confidence_score":null,"total_score":"54.30"}
     Avec match_id: 1744 | Sans match_id: 0

⏺ Bash(cd "/Users/massakambp12/Desktop/Massaka SAS/COTA/backend" && php artisan tinker --execute="
      // Prédictions d'hier avec leurs matchs…)
  ⎿  Prédictions d'hier (2026-05-23): 1021
     {
         "id": 3009,
     … +11 lines (ctrl+o to expand)

⏺ Bash(cd "/Users/massakambp12/Desktop/Massaka SAS/COTA/backend" && php artisan tinker --execute="
      // Le match_id dans predictions est une string API externe, pas l'id…)
  ⎿     Illuminate\Database\QueryException  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'cota_db.football_matches' doesn't exist (Connection: mysql, Socket: /Applica
     tions/MAMP/tmp/mysql/mysql.sock, Database: COTA_DB, SQL: DESCRIBE football_matches).

  Searched for 1 pattern, read 1 file (ctrl+o to expand)

⏺ Bash(cd "/Users/massakambp12/Desktop/Massaka SAS/COTA/backend" && php artisan tinker --execute="
      // Trouver la vraie table des matchs…)
  ⎿  matches

⏺ J'ai maintenant tout le contexte. Voici la situation complète et le plan :

  ---
  Diagnostic complet
  
  Problème 1 — Les scores ne remontent jamais dans predictions
  Le job UpdatePredictionResultsJob cherche home_score et away_score directement dans la table predictions, mais ces colonnes restent NULL car personne ne les remplit après le match.
  Le match_id dans predictions est l'ID externe API ("1511590") mais la table matches utilise un id local — la jointure ne se fait jamais.

  Problème 2 — combined_bets est vide
  Aucun coupon n'a jamais été sauvegardé en base, donc l'historique est inexistant.

  ---
  Ce que je vais construire

  Backend :
  1. Corriger UpdatePredictionResultsJob — joindre predictions → matches via api_match_id pour récupérer les vrais scores et marquer won/lost
  2. Brancher la création du coupon dans GenerateAllPredictionsJob — sauvegarder dans combined_bets après chaque génération
  3. Ajouter colonne lesson + failure_reason dans combined_bets — une leçon auto-générée basée sur quels critères ont trahi

  Mobile — section "Historique Coupons" :
  4. Écran avec liste des coupons passés : date, statut (✅/❌/⏳), cote totale
  5. Détail coupon : chaque pick avec son résultat individuel + explication de l'algo (quel critère a dominé)
  6. Leçon du jour : texte court généré à partir des picks perdus — "Le critère H2H avait surestimé X"

  Veux-tu que je commence ?

⏺ Bash(cd "/Users/massakambp12/Desktop/Massaka SAS/COTA/backend" && php artisan tinker --execute="
      // Vérifier la structure de la table matches…)
  ⎿  away_team (varchar(255))
     competition (varchar(255))
     country (varchar(255))
     … +22 lines (ctrl+o to expand)

⏺ Bash(cd "/Users/massakambp12/Desktop/Massaka