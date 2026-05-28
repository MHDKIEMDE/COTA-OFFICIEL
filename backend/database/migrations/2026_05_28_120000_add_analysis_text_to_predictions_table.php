<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            // Texte d'analyse généré par le LLM ou le template (§9 CDC V2)
            $table->text('analysis_text')->nullable()->after('analysis_details');
            $table->string('analysis_source', 20)->nullable()->after('analysis_text')
                ->comment('anthropic | openai | template');
        });
    }

    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn(['analysis_text', 'analysis_source']);
        });
    }
};
