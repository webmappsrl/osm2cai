<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUgcMediaUgcTrack extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ugc_media_ugc_track', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ugc_media_id');
            $table->unsignedBigInteger('ugc_track_id');
            $table->foreign('ugc_media_id')->references('id')->on('ugc_media')->onDelete('cascade');
            $table->foreign('ugc_track_id')->references('id')->on('ugc_tracks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ugc_media_ugc_track');
    }
}
