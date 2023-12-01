<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHikingRouteItineraryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hiking_route_itinerary', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('hiking_route_id')->constrained()->onDelete('cascade');
            $table->foreignId('itinerary_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hiking_route_itinerary');
    }
}
