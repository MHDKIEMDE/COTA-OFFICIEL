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
