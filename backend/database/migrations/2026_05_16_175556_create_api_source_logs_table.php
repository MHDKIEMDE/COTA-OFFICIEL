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
        Schema::create('api_source_logs', function (Blueprint $table) {
            $table->id();
            $table->date('fetch_date');
            $table->string('source', 32); // 'api_football' | 'thesportsdb' | 'api3' ...
            $table->integer('matches_saved')->default(0);
            $table->integer('matches_updated')->default(0);
            $table->integer('api_quota_used')->nullable();
            $table->integer('api_quota_remaining')->nullable();
            $table->string('status', 16)->default('success'); // success | fallback | error
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('fetch_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_source_logs');
    }
};
