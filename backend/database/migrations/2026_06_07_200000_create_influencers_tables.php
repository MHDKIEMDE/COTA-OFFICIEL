<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table principale influenceurs
        Schema::create('influencers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();          // lien : /r/{slug}
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('platform')->nullable();    // instagram, tiktok, youtube, telegram...
            $table->unsignedBigInteger('user_id')->nullable(); // compte COTA lié
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            // Stats
            $table->unsignedInteger('total_clicks')->default(0);
            $table->unsignedInteger('total_registrations')->default(0);
            $table->unsignedInteger('total_subscriptions')->default(0);

            // Récompenses
            $table->enum('reward_type', ['premium_days', 'cash', 'both'])->default('premium_days');
            $table->unsignedInteger('reward_threshold')->default(10); // nb inscriptions pour déclencher
            $table->unsignedInteger('reward_value')->default(30);     // jours ou montant
            $table->unsignedInteger('total_rewards_given')->default(0);
            $table->timestamp('last_rewarded_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Clics sur les liens influenceurs
        Schema::create('influencer_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('influencer_id')->constrained()->cascadeOnDelete();
            $table->string('ip', 45)->nullable();
            $table->string('country', 2)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('device')->nullable();      // mobile, desktop
            $table->timestamp('clicked_at')->useCurrent();
            $table->index(['influencer_id', 'clicked_at']);
        });

        // Conversions (inscriptions + abonnements)
        Schema::create('influencer_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('influencer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['registration', 'subscription']);
            $table->boolean('reward_given')->default(false);
            $table->timestamp('converted_at')->useCurrent();
            $table->index(['influencer_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('influencer_conversions');
        Schema::dropIfExists('influencer_clicks');
        Schema::dropIfExists('influencers');
    }
};
