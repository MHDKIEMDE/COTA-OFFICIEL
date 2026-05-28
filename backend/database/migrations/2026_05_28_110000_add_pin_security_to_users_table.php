<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Sécurité PIN (§14.2 CDC V2) — 5 tentatives max → verrouillage
            $table->tinyInteger('pin_attempts')->default(0)->after('pin_set');
            $table->timestamp('pin_locked_until')->nullable()->after('pin_attempts');
            // Tracking appareil : OTP forcé si nouvel appareil
            $table->string('last_device_id', 255)->nullable()->after('pin_locked_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pin_attempts', 'pin_locked_until', 'last_device_id']);
        });
    }
};
