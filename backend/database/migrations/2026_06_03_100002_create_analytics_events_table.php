<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();

            // Nullable : visiteurs non connectés aussi trackés
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Nom de l'event (ex: scratch_card_seen, premium_wall_hit…)
            $table->string('event_name', 60);

            // Payload JSON — toujours anonymisable (pas d'IP en clair)
            $table->json('properties')->nullable();

            // Session anonyme (hash SHA-256 de IP+UA, pas d'IP brute)
            $table->string('session_hash', 64)->nullable();

            // Source : flutter_app | web | unknown
            $table->string('source', 20)->default('flutter_app');

            $table->timestamp('created_at')->useCurrent();

            $table->index(['event_name', 'created_at'], 'idx_analytics_event_date');
            $table->index(['user_id',    'event_name'],  'idx_analytics_user_event');
            $table->index('session_hash',                'idx_analytics_session');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
