<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('classifications', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('name')->nullable();
            // Verbatim NGA field value; immutable match key for re-imports.
            $table->string('source_name')->unique();
            $table->text('description')->nullable();
        });

        Schema::create('classification_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'classification');
        });

        Schema::create('classification_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'classification');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classification_revisions');
        Schema::dropIfExists('classification_slugs');
        Schema::dropIfExists('classifications');
    }
};
