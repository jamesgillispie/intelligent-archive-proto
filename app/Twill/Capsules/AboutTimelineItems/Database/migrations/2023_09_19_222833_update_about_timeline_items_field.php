<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('about_timeline_items', function (Blueprint $table) {
            $table->dropColumn('label');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('description');
            $table->text('label')->nullable();
        });
    }
};
