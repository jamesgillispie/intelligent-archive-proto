<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/*
 * Source layer: faithful, immutable-by-convention preservation of raw NGA
 * Open Data rows. Never Twill-managed, never edited by hand (CLAUDE.md
 * architecture boundaries).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('source_imports', function (Blueprint $table) {
            $table->id();
            // Upstream dataset reference (e.g. git commit SHA of the
            // NationalGalleryOfArt/opendata snapshot), when known.
            $table->string('snapshot_ref')->nullable();
            $table->string('data_path');
            $table->string('status')->default('pending')->index();
            $table->json('report')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('source_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_import_id')->constrained()->cascadeOnDelete();
            // Upstream CSV name, e.g. objects, constituents, published_images.
            $table->string('source_table');
            // Original NGA identifier (objectid, constituentid, uuid, ...).
            $table->string('source_id');
            // Raw row, verbatim.
            $table->jsonb('payload');
            // Stable hash of the normalized row for change detection.
            $table->string('content_hash', 64)->index();
            $table->unsignedInteger('version')->default(1);
            // Rights signal (e.g. CC0 for open-access imagery), when derivable.
            $table->string('rights')->nullable();
            // Upstream modification timestamp (objects.lastdetectedmodification).
            $table->timestamp('source_modified_at')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            // Set when a re-import no longer contains this row; never hard-deleted.
            $table->timestamp('missing_from_source_at')->nullable();
            $table->timestamps();

            $table->unique(['source_table', 'source_id']);
            $table->index('source_table');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source_records');
        Schema::dropIfExists('source_imports');
    }
};
