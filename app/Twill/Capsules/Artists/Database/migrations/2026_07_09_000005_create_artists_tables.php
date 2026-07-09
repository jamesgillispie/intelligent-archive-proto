<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('artists', function (Blueprint $table) {
            createDefaultTableFields($table);

            // Identity and provenance
            $table->string('nga_constituent_id')->unique();
            $table->foreignId('source_record_id')->nullable()->constrained('source_records')->nullOnDelete();
            $table->timestamp('last_synced_at')->nullable();

            // Display fields (editable in Twill; see HasSourceFields)
            $table->string('name', 500)->nullable();
            $table->string('display_date')->nullable();

            // Imported values (written only by the import pipeline)
            $table->string('source_name', 500)->nullable();
            $table->string('source_display_date')->nullable();
            $table->string('nationality')->nullable()->index();
            $table->integer('begin_year')->nullable();
            $table->integer('end_year')->nullable();
            $table->string('ulan_id')->nullable();
            $table->string('wikidata_id')->nullable();
            $table->boolean('artist_of_nga_object')->default(true);

            // Editorial-only
            $table->text('description')->nullable();
        });

        Schema::create('artist_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'artist');
        });

        Schema::create('artist_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'artist');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_revisions');
        Schema::dropIfExists('artist_slugs');
        Schema::dropIfExists('artists');
    }
};
