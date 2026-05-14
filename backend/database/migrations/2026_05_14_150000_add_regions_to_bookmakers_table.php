<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookmakers', function (Blueprint $table) {
            // JSON array de codes région : ['west_africa', 'europe', 'global', ...]
            $table->json('regions')->nullable()->after('description')
                ->comment('Régions cibles (ex: ["west_africa","global"])');
        });
    }

    public function down(): void
    {
        Schema::table('bookmakers', function (Blueprint $table) {
            $table->dropColumn('regions');
        });
    }
};
