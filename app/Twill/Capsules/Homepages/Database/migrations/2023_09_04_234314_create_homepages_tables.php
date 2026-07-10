<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHomepagesTables extends Migration
{
    public function up(): void
    {
        Schema::create('homepages', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('title', 200)->nullable();
            $table->text('page_title')->nullable();
            $table->text('tagline')->nullable();
            $table->json('about_us')->nullable();
        });

        Schema::create('homepage_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'homepage');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_revisions');
        Schema::dropIfExists('homepages');
    }
}
