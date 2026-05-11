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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Feedback
            $table->enum('category', ['bug', 'question', 'suggestion', 'complaint', 'other']);
            $table->string('subject');
            $table->text('message');
            $table->string('screenshot_url')->nullable(); // URL upload screenshot (S3, etc.)

            // Pronostic contesté (si applicable)
            $table->foreignId('prediction_id')->nullable()->constrained()->onDelete('set null');
            $table->text('contest_reason')->nullable();

            // Traitement
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->text('admin_response')->nullable();
            $table->timestamp('resolved_at')->nullable();

            // Métadonnées
            $table->string('app_version', 20)->nullable();
            $table->string('device_info')->nullable();
            $table->timestamps();

            // Index
            $table->index('user_id');
            $table->index('status');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
