<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50); // routine_morning, routine_afternoon, routine_evening, event, re_engagement
            $table->boolean('enabled')->default(true);
            $table->unsignedTinyInteger('quiet_hours_start')->default(23); // heure locale 0-23
            $table->unsignedTinyInteger('quiet_hours_end')->default(7);
            $table->timestamps();

            $table->unique(['user_id', 'type']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
