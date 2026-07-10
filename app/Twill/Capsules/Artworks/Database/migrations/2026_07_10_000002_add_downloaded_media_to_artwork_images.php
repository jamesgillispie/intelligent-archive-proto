<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('artwork_images', function (Blueprint $table) {
            // Downloaded copy in the Twill media library. Nullable: remote
            // IIIF metadata always survives, so the local file is re-derivable.
            $table
                ->foreignId('media_id')
                ->nullable()
                ->constrained(config('twill.medias_table', 'twill_medias'))
                ->nullOnDelete();
            $table->timestamp('downloaded_at')->nullable();
            $table->string('failure_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('artwork_images', function (Blueprint $table) {
            $table->dropConstrainedForeignId('media_id');
            $table->dropColumn(['downloaded_at', 'failure_reason']);
        });
    }
};
