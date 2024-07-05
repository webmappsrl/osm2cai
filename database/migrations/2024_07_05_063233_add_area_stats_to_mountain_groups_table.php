<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAreaStatsToMountainGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mountain_groups', function (Blueprint $table) {
            $table->integer('elevation_min')->nullable();
            $table->integer('elevation_max')->nullable();
            $table->integer('elevation_avg')->nullable();
            $table->integer('elevation_stddev')->nullable();
            $table->integer('slope_min')->nullable();
            $table->integer('slope_max')->nullable();
            $table->integer('slope_avg')->nullable();
            $table->integer('slope_stddev')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mountain_groups', function (Blueprint $table) {
            $table->dropColumn(['elevation_min', 'elevation_max', 'elevation_avg', 'elevation_stddev', 'slope_min', 'slope_max', 'slope_avg', 'slope_stddev']);
        });
    }
}
