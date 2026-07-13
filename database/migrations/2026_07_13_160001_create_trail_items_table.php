<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trail_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_trail_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artwork_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            // user_saved | suggestion_accepted — keeps AI influence
            // inspectable from the first row ever written (STRATEGY §3).
            $table->string('origin')->default('user_saved');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['research_trail_id', 'artwork_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trail_items');
    }
};
