<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('predictions') && !$this->hasIndex('predictions', 'predictions_match_date_is_published_index')) {
            Schema::table('predictions', function (Blueprint $table) {
                $table->index(['match_date', 'is_published'], 'predictions_match_date_is_published_index');
            });
        }

        if (Schema::hasTable('matches') && !$this->hasIndex('matches', 'matches_match_date_status_index')) {
            Schema::table('matches', function (Blueprint $table) {
                $table->index(['match_date', 'status'], 'matches_match_date_status_index');
            });
        }

        if (Schema::hasTable('notifications') && !$this->hasIndex('notifications', 'notifications_created_at_index')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index('created_at', 'notifications_created_at_index');
            });
        }

        if (Schema::hasTable('combined_bets') && !$this->hasIndex('combined_bets', 'combined_bets_date_index')) {
            Schema::table('combined_bets', function (Blueprint $table) {
                $table->index('date', 'combined_bets_date_index');
            });
        }

        if (Schema::hasTable('api_source_logs') && !$this->hasIndex('api_source_logs', 'api_source_logs_created_at_index')) {
            Schema::table('api_source_logs', function (Blueprint $table) {
                $table->index('created_at', 'api_source_logs_created_at_index');
            });
        }

        if (Schema::hasTable('user_coupons') && Schema::hasColumn('user_coupons', 'result') && !$this->hasIndex('user_coupons', 'user_coupons_result_created_at_index')) {
            Schema::table('user_coupons', function (Blueprint $table) {
                $table->index(['result', 'created_at'], 'user_coupons_result_created_at_index');
            });
        }

        if (Schema::hasTable('winning_coupons') && !$this->hasIndex('winning_coupons', 'winning_coupons_created_at_index')) {
            Schema::table('winning_coupons', function (Blueprint $table) {
                $table->index('created_at', 'winning_coupons_created_at_index');
            });
        }
    }

    public function down(): void
    {
        $map = [
            'predictions'     => 'predictions_match_date_is_published_index',
            'matches'         => 'matches_match_date_status_index',
            'notifications'   => 'notifications_created_at_index',
            'combined_bets'   => 'combined_bets_date_index',
            'api_source_logs' => 'api_source_logs_created_at_index',
            'user_coupons'    => 'user_coupons_result_created_at_index',
            'winning_coupons' => 'winning_coupons_created_at_index',
        ];

        foreach ($map as $table => $index) {
            if (Schema::hasTable($table) && $this->hasIndex($table, $index)) {
                Schema::table($table, fn (Blueprint $t) => $t->dropIndex($index));
            }
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        try {
            return count(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index])) > 0;
        } catch (\Throwable) {
            return false;
        }
    }
};
