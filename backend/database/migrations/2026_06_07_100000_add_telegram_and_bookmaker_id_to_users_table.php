<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_id')->nullable()->unique()->after('google_id');
            $table->string('telegram_username')->nullable()->after('telegram_id');
            $table->unsignedBigInteger('preferred_bookmaker_id')->nullable()->after('bookmaker_slug');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telegram_id', 'telegram_username', 'preferred_bookmaker_id']);
        });
    }
};
