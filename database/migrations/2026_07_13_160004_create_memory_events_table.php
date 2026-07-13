<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('memory_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_type');
            // Subjects may be deleted out from under the ledger; the payload
            // snapshots what the row needs to stay human-readable.
            $table->nullableMorphs('subject');
            $table->jsonb('payload')->nullable();
            $table->timestamp('occurred_at');

            $table->index(['user_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memory_events');
    }
};
