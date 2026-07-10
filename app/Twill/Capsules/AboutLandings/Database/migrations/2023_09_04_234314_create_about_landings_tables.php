<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAboutLandingsTables extends Migration
{
    public function up(): void
    {
        Schema::create('about_landings', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('title', 200)->nullable();
            $table->text('page_title')->nullable();
            $table->json('hero')->nullable();
            $table->json('people')->nullable();
        });

        Schema::create('about_landing_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'about_landing');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_landing_revisions');
        Schema::dropIfExists('about_landings');
    }
}
