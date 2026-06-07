<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookmakers', function (Blueprint $table) {
            // JSON : ['Wave','Orange Money','MTN','Moov','Carte bancaire','Crypto']
            $table->json('deposit_methods')->nullable()->after('regions')
                ->comment('Moyens de dépôt acceptés');
            $table->json('withdrawal_methods')->nullable()->after('deposit_methods')
                ->comment('Moyens de retrait acceptés');
            // Rang de popularité global (1 = le plus populaire, null = non classé)
            $table->unsignedSmallInteger('popular_rank')->nullable()->after('sort_order')
                ->comment('Rang popularité pour tri global');
            // Dépôt et retrait minimum en FCFA
            $table->unsignedInteger('min_deposit')->nullable()->after('withdrawal_methods');
            $table->unsignedInteger('min_withdrawal')->nullable()->after('min_deposit');
            // Bonus de bienvenue court (ex: "100% jusqu'à 50 000 FCFA")
            $table->string('bonus_label')->nullable()->after('min_withdrawal');
            // Note globale sur 5
            $table->decimal('rating', 3, 1)->nullable()->after('bonus_label');
        });
    }

    public function down(): void
    {
        Schema::table('bookmakers', function (Blueprint $table) {
            $table->dropColumn([
                'deposit_methods',
                'withdrawal_methods',
                'popular_rank',
                'min_deposit',
                'min_withdrawal',
                'bonus_label',
                'rating',
            ]);
        });
    }
};
