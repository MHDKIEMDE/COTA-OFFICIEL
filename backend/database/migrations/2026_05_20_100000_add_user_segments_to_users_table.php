<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Colonnes de segmentation utilisateur.
 *
 * bookmaker_slug  : bookmaker déclaré à l'onboarding (ex: "1xbet", "__none__")
 * parieur_profil  : style de parieur (ex: "daily", "weekend", "big_games")
 * detected_region : région détectée par IP à l'inscription (ex: "west_africa")
 *
 * Ces colonnes sont indexées pour permettre des GROUP BY rapides en admin
 * (combien d'utilisateurs par bookmaker, par profil, par région).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bookmaker_slug', 50)->nullable()->after('preferences');
            $table->string('parieur_profil', 30)->nullable()->after('bookmaker_slug');
            $table->string('detected_region', 30)->nullable()->after('parieur_profil');

            $table->index('bookmaker_slug',  'idx_users_bookmaker');
            $table->index('parieur_profil',  'idx_users_profil');
            $table->index('detected_region', 'idx_users_region');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_bookmaker');
            $table->dropIndex('idx_users_profil');
            $table->dropIndex('idx_users_region');
            $table->dropColumn(['bookmaker_slug', 'parieur_profil', 'detected_region']);
        });
    }
};
