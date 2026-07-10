<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Twill\Capsules\AboutLandings\Models\AboutLanding;

class CreateAboutTimelineItemsTables extends Migration
{
    public function up(): void
    {
        Schema::create('about_timeline_items', function (Blueprint $table) {
            createDefaultTableFields($table, true, false);
            $table->text('label')->nullable();
            $table->string('year')->nullable();
            $table->integer('position')->unsigned()->nullable();
            $table->foreignIdFor(AboutLanding::class);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_timeline_items');
    }
}
