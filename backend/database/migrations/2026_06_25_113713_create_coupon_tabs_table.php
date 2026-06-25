<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupon_tabs', function (Blueprint $table) {
            $table->id();
            // Clé technique stable (prudent|equilibre|kamikaze|featured) — jamais éditée.
            $table->string('key')->unique();
            $table->string('label');                 // Libellé affiché (éditable)
            $table->string('subtitle')->nullable();  // Sous-titre / plage de cote (éditable)
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_tabs');
    }
};
