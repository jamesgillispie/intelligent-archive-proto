<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('artworks', function (Blueprint $table) {
            createDefaultTableFields($table);

            // Identity and provenance
            $table->string('nga_object_id')->unique();
            $table->string('accession_number')->nullable()->index();
            $table->foreignId('source_record_id')->nullable()->constrained('source_records')->nullOnDelete();
            $table->timestamp('last_synced_at')->nullable();

            // Display fields (editable in Twill; see HasSourceFields)
            $table->string('title', 500)->nullable();
            $table->string('display_date')->nullable();
            $table->text('medium_text')->nullable();
            $table->text('dimensions')->nullable();
            $table->text('credit_line')->nullable();
            $table->string('attribution', 500)->nullable();

            // Imported values (written only by the import pipeline)
            $table->string('source_title', 500)->nullable();
            $table->string('source_display_date')->nullable();
            $table->text('source_medium_text')->nullable();
            $table->text('source_dimensions')->nullable();
            $table->text('source_credit_line')->nullable();
            $table->string('source_attribution', 500)->nullable();
            $table->integer('source_begin_year')->nullable()->index();
            $table->integer('source_end_year')->nullable();
            $table->text('source_inscription')->nullable();
            $table->string('source_classification')->nullable();
            $table->string('source_subclassification')->nullable();
            $table->string('source_department_abbr')->nullable();

            // Editorial-only
            $table->text('description')->nullable();

            // Canonical relations and display state
            $table->foreignId('classification_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('medium_id')->nullable()->constrained('mediums')->nullOnDelete();
            $table->boolean('is_displayable')->default(false)->index();
        });

        Schema::create('artwork_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'artwork');
        });

        Schema::create('artwork_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'artwork');
        });

        Schema::create('artwork_artist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artwork_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->string('role')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['artwork_id', 'artist_id']);
        });

        Schema::create('artwork_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artwork_id')->constrained()->cascadeOnDelete();
            $table->string('nga_uuid')->unique();
            $table->string('iiif_url', 1000)->nullable();
            $table->string('iiif_thumb_url', 1000)->nullable();
            $table->string('view_type')->nullable();
            $table->boolean('is_primary')->default(false)->index();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('max_pixels')->nullable();
            $table->text('assistive_text')->nullable();
            $table->string('validation_status')->default('unvalidated');
            $table->timestamp('validated_at')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artwork_images');
        Schema::dropIfExists('artwork_artist');
        Schema::dropIfExists('artwork_revisions');
        Schema::dropIfExists('artwork_slugs');
        Schema::dropIfExists('artworks');
    }
};
