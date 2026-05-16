<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            // Récupérer le DDL original
            $original = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name='predictions'")[0]->sql;

            // Remplacer uniquement la contrainte bet_type
            $modified = str_replace(
                '"bet_type" varchar check ("bet_type" in (\'1X2\', \'Over/Under\', \'BTTS\', \'Double Chance\', \'HT/FT\')) not null',
                '"bet_type" varchar not null',
                $original
            );

            // Renommer l'ancienne table
            DB::statement('ALTER TABLE predictions RENAME TO predictions_old');

            // Créer la nouvelle table (sans la contrainte CHECK)
            $newCreate = str_replace('CREATE TABLE "predictions"', 'CREATE TABLE "predictions"', $modified);
            DB::statement($newCreate);

            // Copier toutes les données
            DB::statement('INSERT INTO predictions SELECT * FROM predictions_old');

            // Supprimer l'ancienne table
            DB::statement('DROP TABLE predictions_old');

            // Recréer les index si nécessaire
            DB::statement('CREATE INDEX IF NOT EXISTS predictions_match_date_index ON predictions (match_date)');

            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            // MySQL / PostgreSQL
            DB::statement("ALTER TABLE predictions MODIFY bet_type VARCHAR(50) NOT NULL DEFAULT '1X2'");
        }
    }

    public function down(): void
    {
        // Pas de rollback : impossible de remettre un ENUM avec des valeurs hors-liste
    }
};
