<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_quota_usage', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 50);
            $table->date('date');
            $table->integer('requests_count')->default(0);
            $table->integer('quota_limit');
            $table->timestamp('last_request_at')->nullable();

            $table->unique(['provider', 'date'], 'unique_provider_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_quota_usage');
    }
};
