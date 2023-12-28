<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUgcMediaUgcPoi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ugc_media_ugc_poi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ugc_media_id');
            $table->unsignedBigInteger('ugc_poi_id');
            $table->foreign('ugc_media_id')->references('id')->on('ugc_media')->onDelete('cascade');
            $table->foreign('ugc_poi_id')->references('id')->on('ugc_pois')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ugc_media_ugc_poi');
    }
}
