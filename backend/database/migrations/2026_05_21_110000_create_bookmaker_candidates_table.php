<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookmaker_candidates', function (Blueprint $table) {
            $table->id();

            // Source API
            $table->string('api_source', 50);        // 'apifootball' | 'oddsapi' | 'manual'
            $table->string('api_id', 100)->nullable(); // ID chez la source
            $table->string('name', 150);
            $table->string('slug', 150)->nullable();
            $table->string('logo_url')->nullable();
            $table->string('website_url')->nullable();
            $table->text('description')->nullable();
            $table->string('country', 100)->nullable();
            $table->json('raw_data')->nullable();      // payload brut de l'API

            // Détails bonus scrappés / enrichis
            $table->string('bonus_label', 255)->nullable();     // ex: "200% jusqu'à 100 000 FCFA"
            $table->text('bonus_description')->nullable();
            $table->string('primary_color', 20)->nullable();

            // Statut workflow
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            // Une fois approuvé, référence vers bookmakers
            $table->foreignId('bookmaker_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            $table->unique(['api_source', 'api_id']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookmaker_candidates');
    }
};
