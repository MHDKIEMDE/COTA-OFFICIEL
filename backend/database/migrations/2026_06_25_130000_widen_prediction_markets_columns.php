<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prediction_markets', function (Blueprint $table) {
            // Les marchés Team Goals incluent le nom d'équipe (parfois très long)
            $table->string('outcome', 120)->change();
            $table->string('market_selection', 120)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('prediction_markets', function (Blueprint $table) {
            $table->string('outcome', 60)->change();
            $table->string('market_selection', 30)->nullable()->change();
        });
    }
};
