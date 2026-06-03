<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tier explicite pour la logique de publication (complète is_premium boolean)
            $table->enum('tier', ['free', 'premium'])->default('free')->after('is_premium');
            $table->index('tier', 'idx_users_tier');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_tier');
            $table->dropColumn('tier');
        });
    }
};
