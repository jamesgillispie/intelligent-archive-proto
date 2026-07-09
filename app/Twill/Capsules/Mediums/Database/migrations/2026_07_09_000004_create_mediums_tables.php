<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mediums', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('name')->nullable();
            // Verbatim NGA field value; immutable match key for re-imports.
            $table->string('source_name')->unique();
            $table->text('description')->nullable();
        });

        Schema::create('medium_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'medium', 'mediums');
        });

        Schema::create('medium_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'medium', 'mediums');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medium_revisions');
        Schema::dropIfExists('medium_slugs');
        Schema::dropIfExists('mediums');
    }
};
