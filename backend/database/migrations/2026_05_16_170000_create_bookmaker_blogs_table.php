<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookmaker_blogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bookmaker_id')->constrained()->cascadeOnDelete();
            $table->string('promo_code', 50)->default('COTA');
            $table->string('bonus_title', 255)->nullable();       // ex: "Bonus 200% jusqu'à 100 000 FCFA"
            $table->text('bonus_description')->nullable();        // description complète du bonus
            $table->json('steps')->nullable();                    // [{step:1, title:"...", body:"..."}]
            $table->string('cta_label', 100)->default("S'inscrire et obtenir le bonus");
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['bookmaker_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookmaker_blogs');
    }
};
