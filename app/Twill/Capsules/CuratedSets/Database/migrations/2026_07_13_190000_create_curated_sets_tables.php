<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('curated_sets', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('position')->unsigned()->nullable();
        });

        Schema::create('curated_set_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'curated_set');
        });

        Schema::create('curated_set_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'curated_set');
        });

        Schema::create('artwork_curated_set', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curated_set_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artwork_id')->constrained()->cascadeOnDelete();
            $table->integer('position')->unsigned()->nullable();
            $table->unique(['curated_set_id', 'artwork_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artwork_curated_set');
        Schema::dropIfExists('curated_set_revisions');
        Schema::dropIfExists('curated_set_slugs');
        Schema::dropIfExists('curated_sets');
    }
};
