<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('research_trail_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artwork_id')->constrained()->cascadeOnDelete();

            // continuation | counterpoint — mirrors the Nexus artifact kinds.
            $table->string('kind');

            // The explanation shown to the user, verbatim, and the evidence
            // behind it — "why this suggestion?" answers from this row, not
            // from a fresh query.
            $table->text('reason');
            $table->jsonb('citations');

            // Snapshot of the context the suggestion was drawn from (trail
            // title and size, anchors, dismissals respected at the time).
            $table->jsonb('context');

            // shown | accepted | dismissed — every suggestion is persisted
            // the moment it is shown; dismissals suppress re-suggestion.
            $table->string('status')->default('shown')->index();
            $table->timestamp('responded_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['research_trail_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
