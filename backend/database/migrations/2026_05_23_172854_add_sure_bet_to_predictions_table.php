<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->string('sure_bet_level')->nullable()->after('ev_positive'); // null | '95' | '99'
            $table->json('sure_bet_analysis')->nullable()->after('sure_bet_level'); // détails vérifications
        });
    }

    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn(['sure_bet_level', 'sure_bet_analysis']);
        });
    }
};
