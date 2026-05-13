<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin', 255)->nullable()->after('password');
            $table->boolean('pin_set')->default(false)->after('pin');
            $table->boolean('biometric_enabled')->default(false)->after('pin_set');
            $table->boolean('registration_completed')->default(false)->after('biometric_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pin', 'pin_set', 'biometric_enabled', 'registration_completed']);
        });
    }
};
