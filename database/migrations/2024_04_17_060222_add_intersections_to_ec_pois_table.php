<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIntersectionsToEcPoisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ec_pois', function (Blueprint $table) {
            $table->json('hiking_routes_in_buffer')->nullable();
            $table->json('huts_intersecting')->nullable();
            $table->json('sections_intersecting')->nullable();
            $table->json('mountain_groups_intersecting')->nullable();
            $table->string('comuni')->nullable();
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
            $table->dropColumn('hiking_routes_in_buffer');
            $table->dropColumn('huts_intersecting');
            $table->dropColumn('sections_intersecting');
            $table->dropColumn('mountain_groups_intersecting');
            $table->dropColumn('comuni');
        });
    }
}