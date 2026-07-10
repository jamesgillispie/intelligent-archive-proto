<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesTables extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('title', 200)->nullable();
            $table->timestamp('publish_start_date')->nullable();
            $table->timestamp('publish_end_date')->nullable();
        });

        Schema::create('article_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'article');
        });

        Schema::create('article_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'article');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_revisions');
        Schema::dropIfExists('article_slugs');
        Schema::dropIfExists('articles');
    }
}
