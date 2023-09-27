<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEleFieldsToHikingRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hiking_routes', function (Blueprint $table) {
            $table->float('ele_max')->nullable();
            $table->float('ele_max_osm')->nullable();
            $table->float('ele_max_comp')->nullable();
            $table->float('ele_min')->nullable();
            $table->float('ele_min_osm')->nullable();
            $table->float('ele_min_comp')->nullable();
            $table->float('ele_from')->nullable();
            $table->float('ele_from_osm')->nullable();
            $table->float('ele_from_comp')->nullable();
            $table->float('ele_to')->nullable();
            $table->float('ele_to_osm')->nullable();
            $table->float('ele_to_comp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hiking_routes', function (Blueprint $table) {
            $table->dropColumn('ele_max');
            $table->dropColumn('ele_max_osm');
            $table->dropColumn('ele_max_comp');
            $table->dropColumn('ele_min');
            $table->dropColumn('ele_min_osm');
            $table->dropColumn('ele_min_comp');
            $table->dropColumn('ele_from');
            $table->dropColumn('ele_from_osm');
            $table->dropColumn('ele_from_comp');
            $table->dropColumn('ele_to');
            $table->dropColumn('ele_to_osm');
            $table->dropColumn('ele_to_comp');
        });
    }
}
