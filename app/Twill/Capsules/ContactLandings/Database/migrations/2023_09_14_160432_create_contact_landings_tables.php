<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactLandingsTables extends Migration
{
    public function up(): void
    {
        Schema::create('contact_landings', function (Blueprint $table) {
            // this will create an id, a "published" column, and soft delete and timestamps columns
            createDefaultTableFields($table);

            // feel free to modify the name of this column, but title is supported by default (you would need to specify the name of the column Twill should consider as your "title" column in your module controller if you change it)
            $table->string('title', 200)->nullable();

            $table->string('page_title', 200)->nullable();

            // add those 2 columns to enable publication timeframe fields (you can use publish_start_date only if you don't need to provide the ability to specify an end date)
            // $table->timestamp('publish_start_date')->nullable();
            // $table->timestamp('publish_end_date')->nullable();
        });

        Schema::create('contact_landing_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'contact_landing');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_landing_revisions');
        Schema::dropIfExists('contact_landings');
    }
}
