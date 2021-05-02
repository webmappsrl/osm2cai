<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HikingRouteRegion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hiking_route_region', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('hiking_route_id');
            $table->foreign('hiking_route_id')->references('id')->on('hiking_routes');
            $table->unsignedBigInteger('region_id');
            $table->foreign('region_id')->references('id')->on('regions');
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
        Schema::dropIfExists('hiking_route_region');
    }
}
