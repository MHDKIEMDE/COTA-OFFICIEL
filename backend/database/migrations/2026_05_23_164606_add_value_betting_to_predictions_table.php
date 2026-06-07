<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->decimal('value_score', 6, 3)->nullable()->after('total_score');
            $table->decimal('kelly_fraction', 5, 4)->nullable()->after('value_score');
            $table->boolean('ev_positive')->default(false)->after('kelly_fraction');
        });
    }

    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn(['value_score', 'kelly_fraction', 'ev_positive']);
        });
    }
};
