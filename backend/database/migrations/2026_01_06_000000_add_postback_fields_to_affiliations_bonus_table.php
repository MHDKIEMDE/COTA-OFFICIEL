<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Ajoute les champs pour stocker les données des postbacks AffiliateControl
     */
    public function up(): void
    {
        Schema::table('affiliations_bonus', function (Blueprint $table) {
            // Données du postback AffiliateControl
            $table->string('player_id')->nullable()->after('bonus_expires_at');
            $table->string('event_type')->nullable()->after('player_id');
            $table->decimal('revenue', 10, 2)->nullable()->after('event_type');
            $table->string('request_id')->nullable()->after('revenue');

            // Index pour recherche rapide
            $table->index('player_id');
            $table->index('request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliations_bonus', function (Blueprint $table) {
            $table->dropIndex(['player_id']);
            $table->dropIndex(['request_id']);
            $table->dropColumn(['player_id', 'event_type', 'revenue', 'request_id']);
        });
    }
};

