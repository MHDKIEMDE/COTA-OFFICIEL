<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookmaker_blogs', function (Blueprint $table) {
            // Catégorie marketing
            $table->enum('category', ['guide', 'tutoriel', 'video', 'photo', 'promotion', 'actualite'])
                  ->default('guide')
                  ->after('bonus_description');

            // Médias
            $table->string('media_url')->nullable()->after('category');       // URL vidéo YouTube / image
            $table->string('thumbnail_url')->nullable()->after('media_url');  // miniature

            // Méta SEO / partage
            $table->string('title', 255)->nullable()->after('thumbnail_url'); // titre article (différent du bonus_title)
            $table->text('excerpt')->nullable()->after('title');              // accroche courte

            // Publication
            $table->boolean('is_featured')->default(false)->after('is_active'); // mis en avant
            $table->timestamp('published_at')->nullable()->after('is_featured');

            $table->index(['bookmaker_id', 'category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('bookmaker_blogs', function (Blueprint $table) {
            $table->dropColumn([
                'category', 'media_url', 'thumbnail_url',
                'title', 'excerpt', 'is_featured', 'published_at',
            ]);
        });
    }
};
