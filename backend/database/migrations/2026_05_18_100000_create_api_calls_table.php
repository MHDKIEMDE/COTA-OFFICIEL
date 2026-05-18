<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_calls', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 50);
            $table->string('endpoint', 255);
            $table->string('method', 10)->default('GET');
            $table->integer('status_code')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('was_fallback')->default(false);
            $table->boolean('cache_hit')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['provider', 'created_at'], 'idx_provider_date');
            $table->index(['was_fallback', 'created_at'], 'idx_fallbacks');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_calls');
    }
};
