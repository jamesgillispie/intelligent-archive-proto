<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('artwork_enrichments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artwork_id')->constrained()->cascadeOnDelete();

            // Which display field the generated text is proposed for.
            $table->string('field');
            $table->text('generated_text');

            // Provenance of the generation, for the review trail.
            $table->string('model_ref')->nullable();
            $table->string('prompt_ref')->nullable();

            // pending | approved | rejected — pending text never reaches
            // display fields or the Nexus index.
            $table->string('status')->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            // Evidence the generator claimed for the text, reviewable.
            $table->jsonb('citations')->nullable();

            $table->timestamps();

            $table->index(['artwork_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artwork_enrichments');
    }
};
