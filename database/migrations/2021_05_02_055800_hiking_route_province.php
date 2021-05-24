<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HikingRouteProvince extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hiking_route_province', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('hiking_route_id');
            $table->foreign('hiking_route_id')->references('id')->on('hiking_routes');
            $table->unsignedBigInteger('province_id');
            $table->foreign('province_id')->references('id')->on('provinces');
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
        Schema::dropIfExists('hiking_route_province');
    }
}
