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
        Schema::table('users', function (Blueprint $table) {
            // Authentification
            $table->string('phone', 20)->unique()->nullable()->after('email');
            $table->string('facebook_id')->unique()->nullable()->after('phone');
            $table->string('otp_code', 6)->nullable()->after('facebook_id');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            $table->integer('otp_attempts')->default(0)->after('otp_expires_at');

            // Statut Premium
            $table->boolean('is_premium')->default(false)->after('otp_attempts');
            $table->timestamp('premium_expires_at')->nullable()->after('is_premium');
            $table->enum('premium_source', ['subscription', 'referral', 'affiliation', 'welcome'])->nullable()->after('premium_expires_at');

            // Parrainage
            $table->string('referral_code', 10)->unique()->after('premium_source');
            $table->foreignId('referred_by')->nullable()->constrained('users')->onDelete('set null')->after('referral_code');
            $table->integer('referral_count')->default(0)->after('referred_by');
            $table->integer('referral_days_earned')->default(0)->after('referral_count');

            // Combiné Bienvenue
            $table->boolean('welcome_combined_used')->default(false)->after('referral_days_earned');
            $table->timestamp('welcome_combined_expires_at')->nullable()->after('welcome_combined_used');

            // FCM Push Notifications
            $table->text('fcm_token')->nullable()->after('welcome_combined_expires_at');

            // Métadonnées
            $table->string('country_code', 5)->nullable()->after('fcm_token');
            $table->string('device_type', 20)->nullable()->after('country_code');
            $table->timestamp('last_login_at')->nullable()->after('device_type');

            // Rendre email nullable (auth par téléphone prioritaire)
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'facebook_id',
                'otp_code',
                'otp_expires_at',
                'otp_attempts',
                'is_premium',
                'premium_expires_at',
                'premium_source',
                'referral_code',
                'referred_by',
                'referral_count',
                'referral_days_earned',
                'welcome_combined_used',
                'welcome_combined_expires_at',
                'fcm_token',
                'country_code',
                'device_type',
                'last_login_at',
            ]);
        });
    }
};
