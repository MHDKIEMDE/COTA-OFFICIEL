<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookmakers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nom affiché (ex: "1xBet")');
            $table->string('slug')->unique()->comment('Identifiant unique (ex: "1xbet")');
            $table->string('primary_color', 7)->default('#FF0000')->comment('Couleur primaire (hex)');
            $table->string('secondary_color', 7)->nullable()->comment('Couleur secondaire (hex)');
            $table->string('affiliate_link')->nullable()->comment('URL avec ID affilié');
            $table->string('download_link')->nullable()->comment('URL APK ou store');
            $table->boolean('is_active')->default(true)->comment('Actif dans l\'app');
            $table->integer('sort_order')->default(0)->comment('Ordre d\'affichage');
            $table->string('logo_url')->nullable()->comment('URL du logo');
            $table->text('description')->nullable()->comment('Description du bookmaker');
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmakers');
    }
};
