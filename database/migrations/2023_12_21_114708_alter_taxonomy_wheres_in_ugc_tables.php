<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTaxonomyWheresInUgcTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ugc_pois', function (Blueprint $table) {
            $table->string('taxonomy_wheres', 2000)->change();
        });

        Schema::table('ugc_tracks', function (Blueprint $table) {
            $table->string('taxonomy_wheres', 2000)->change();
        });

        Schema::table('ugc_media', function (Blueprint $table) {
            $table->string('taxonomy_wheres', 2000)->change();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ugc_pois', function (Blueprint $table) {
            $table->string('taxonomy_wheres', 255)->change();
        });

        Schema::table('ugc_tracks', function (Blueprint $table) {
            $table->string('taxonomy_wheres', 255)->change();
        });

        Schema::table('ugc_media', function (Blueprint $table) {
            $table->string('taxonomy_wheres', 255)->change();
        });
    }
}
