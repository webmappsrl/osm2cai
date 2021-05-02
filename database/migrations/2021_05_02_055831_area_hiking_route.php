<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AreaHikingRoute extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('area_hiking_route', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('hiking_route_id');
            $table->foreign('hiking_route_id')->references('id')->on('hiking_routes');
            $table->unsignedBigInteger('area_id');
            $table->foreign('area_id')->references('id')->on('areas');
            $table->float("percentage")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('area_hiking_route');
    }
}
