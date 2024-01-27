<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCorrectGeometriesToPoisMountainGroupsNaturalSpringsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ec_pois', function (Blueprint $table) {
            $table->point('geometry')->nullable();
        });

        Schema::table('mountain_groups', function (Blueprint $table) {
            $table->multiPolygon('geometry')->nullable();
        });

        Schema::table('natural_springs', function (Blueprint $table) {
            $table->point('geometry')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ec_pois', function (Blueprint $table) {
            $table->dropColumn('geometry');
        });

        Schema::table('mountain_groups', function (Blueprint $table) {
            $table->dropColumn('geometry');
        });

        Schema::table('natural_springs', function (Blueprint $table) {
            $table->dropColumn('geometry');
        });
    }
}
