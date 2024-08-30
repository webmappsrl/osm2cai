<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddAppIdToUgcTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ugc_tracks', function (Blueprint $table) {
            $table->string('app_id')->nullable();
        });
        Schema::table('ugc_pois', function (Blueprint $table) {
            $table->string('app_id')->nullable();
        });
        Schema::table('ugc_media', function (Blueprint $table) {
            $table->string('app_id')->nullable();
        });

        //foreach record in ugc_tracks, ugc_pois, ugc_media update the app_id with 'geohub_26'
        DB::table('ugc_tracks')->update(['app_id' => 'geohub_26']);
        DB::table('ugc_pois')->update(['app_id' => 'geohub_26']);
        DB::table('ugc_media')->update(['app_id' => 'geohub_26']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ugc_tracks', function (Blueprint $table) {
            $table->dropColumn('app_id');
        });
        Schema::table('ugc_pois', function (Blueprint $table) {
            $table->dropColumn('app_id');
        });
        Schema::table('ugc_media', function (Blueprint $table) {
            $table->dropColumn('app_id');
        });
    }
}
