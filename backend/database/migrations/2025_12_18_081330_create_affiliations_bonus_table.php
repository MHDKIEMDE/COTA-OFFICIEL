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
        Schema::create('affiliations_bonus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Bookmaker
            $table->enum('bookmaker', ['betwinner', '1xbet', 'melbet', 'other']);
            $table->string('bookmaker_custom_name')->nullable(); // Si "other"
            $table->string('affiliate_link'); // Lien affilié tracké

            // Tracking
            $table->integer('clicks_count')->default(0);
            $table->timestamp('clicked_at')->nullable();
            $table->string('user_ip', 45)->nullable();
            $table->string('user_agent')->nullable();

            // Bonus
            $table->boolean('registration_confirmed')->default(false);
            $table->timestamp('registration_confirmed_at')->nullable();
            $table->boolean('bonus_activated')->default(false); // Premium 7j activé
            $table->timestamp('bonus_activated_at')->nullable();
            $table->timestamp('bonus_expires_at')->nullable();

            // Métadonnées
            $table->text('tracking_details')->nullable(); // JSON avec détails tracking
            $table->timestamps();

            // Index
            $table->index('user_id');
            $table->index(['bookmaker', 'registration_confirmed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliations_bonus');
    }
};
