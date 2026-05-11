<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Ajoute les champs pour la vérification manuelle des affiliations
     */
    public function up(): void
    {
        Schema::table('affiliations_bonus', function (Blueprint $table) {
            // Vérification manuelle par admin
            $table->boolean('is_verified')->default(false)->after('bonus_expires_at');
            $table->timestamp('verified_at')->nullable()->after('is_verified');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null')->after('verified_at');
            $table->text('rejection_reason')->nullable()->after('verified_by');
            
            // Bonus appliqué
            $table->boolean('bonus_applied')->default(false)->after('rejection_reason');
            $table->integer('bonus_days')->default(7)->after('bonus_applied');
            
            // Index
            $table->index('is_verified');
            $table->index('bonus_applied');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliations_bonus', function (Blueprint $table) {
            $table->dropIndex(['is_verified']);
            $table->dropIndex(['bonus_applied']);
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'is_verified',
                'verified_at',
                'verified_by',
                'rejection_reason',
                'bonus_applied',
                'bonus_days',
            ]);
        });
    }
};
