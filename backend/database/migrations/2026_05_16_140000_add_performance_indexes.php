<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── Table predictions ──────────────────────────────────────────────────
        Schema::table('predictions', function (Blueprint $table) {
            // Requête principale : /predictions/today filtre par date + is_published
            if (!$this->indexExists('predictions', 'idx_predictions_date_published')) {
                $table->index(['match_date', 'is_published'], 'idx_predictions_date_published');
            }
            // Coupon : tri par total_score
            if (!$this->indexExists('predictions', 'idx_predictions_total_score')) {
                $table->index('total_score', 'idx_predictions_total_score');
            }
            // Filtrage par compétition (carousel compétitions)
            if (!$this->indexExists('predictions', 'idx_predictions_competition_id')) {
                $table->index('competition_id', 'idx_predictions_competition_id');
            }
            // Résultats (won/lost) pour les stats
            if (!$this->indexExists('predictions', 'idx_predictions_status')) {
                $table->index('status', 'idx_predictions_status');
            }
        });

        // ── Table users ────────────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            // Login par téléphone (OTP + PIN)
            if (!$this->indexExists('users', 'idx_users_phone')) {
                $table->index('phone', 'idx_users_phone');
            }
            // Vérification premium (access_control)
            if (!$this->indexExists('users', 'idx_users_premium')) {
                $table->index(['is_premium', 'premium_expires_at'], 'idx_users_premium');
            }
            // Parrainage : lookup par code
            if (!$this->indexExists('users', 'idx_users_referral_code')) {
                $table->index('referral_code', 'idx_users_referral_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_predictions_date_published');
            $table->dropIndexIfExists('idx_predictions_total_score');
            $table->dropIndexIfExists('idx_predictions_competition_id');
            $table->dropIndexIfExists('idx_predictions_status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_users_phone');
            $table->dropIndexIfExists('idx_users_premium');
            $table->dropIndexIfExists('idx_users_referral_code');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list(\"{$table}\")");
            foreach ($indexes as $index) {
                if ($index->name === $indexName) return true;
            }
            return false;
        }

        // MySQL / PostgreSQL
        $indexes = DB::select(
            "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_NAME = ? AND INDEX_NAME = ?",
            [$table, $indexName]
        );
        return !empty($indexes);
    }
};
