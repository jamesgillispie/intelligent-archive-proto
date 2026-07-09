<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('name')->nullable();
            // Verbatim NGA field value; immutable match key for re-imports.
            $table->string('source_name')->unique();
            $table->text('description')->nullable();
        });

        Schema::create('department_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'department');
        });

        Schema::create('department_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'department');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_revisions');
        Schema::dropIfExists('department_slugs');
        Schema::dropIfExists('departments');
    }
};
