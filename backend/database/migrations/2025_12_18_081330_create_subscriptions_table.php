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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Plan d'abonnement
            $table->string('plan'); // weekly, monthly, quarterly
            $table->integer('amount'); // Montant en FCFA
            $table->string('currency', 5)->default('XOF'); // XOF = Franc CFA

            // Dates
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status')->default('active'); // active, cancelled, expired

            // Paiement
            $table->string('payment_method')->default('paydunya'); // paydunya, referral, affiliation, admin
            $table->string('payment_id')->nullable(); // ID transaction Paydunya
            $table->string('payment_status')->default('pending'); // pending, completed, failed, refunded
            $table->text('payment_details')->nullable(); // JSON avec détails paiement

            // Métadonnées
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();

            // Index
            $table->index(['expires_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
