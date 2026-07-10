<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleTypesTables extends Migration
{
    public function up(): void
    {
        Schema::create('article_types', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('title', 200)->nullable();
            $table->integer('position')->unsigned()->nullable();
        });

        Schema::create('article_type_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'article_type');
        });

        Schema::create('article_type_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'article_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_type_revisions');
        Schema::dropIfExists('article_type_slugs');
        Schema::dropIfExists('article_types');
    }
}
