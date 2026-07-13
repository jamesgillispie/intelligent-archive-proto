<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('memory_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('consent_version');
            $table->timestamp('granted_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            // Re-consent after a wording change creates a new row for the new
            // version; the pair stays unique per user.
            $table->unique(['user_id', 'consent_version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memory_consents');
    }
};
