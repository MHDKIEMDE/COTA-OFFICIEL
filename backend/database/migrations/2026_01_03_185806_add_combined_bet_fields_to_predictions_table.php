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
        Schema::table('predictions', function (Blueprint $table) {
            // Champs pour le système de Combiné Premium Quotidien
            $table->boolean('is_combined_daily')->default(false)->after('is_premium');
            $table->date('combined_date')->nullable()->after('is_combined_daily');
            $table->integer('combined_position')->nullable()->after('combined_date'); // Position 1-5 dans le combiné

            // Index pour performance
            $table->index(['is_combined_daily', 'combined_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropIndex(['is_combined_daily', 'combined_date']);
            $table->dropColumn(['is_combined_daily', 'combined_date', 'combined_position']);
        });
    }
};
