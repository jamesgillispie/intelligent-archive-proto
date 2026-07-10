<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('type', 50)->nullable();
            $table->string('author')->nullable();
            $table->date('publication_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('author');
            $table->dropColumn('publication_date');
        });
    }
};
