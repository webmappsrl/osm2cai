<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIntersectionsToMountainGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mountain_groups', function (Blueprint $table) {
            $table->json('hiking_routes_intersecting')->nullable();
            $table->json('huts_intersecting')->nullable();
            $table->json('sections_intersecting')->nullable();
            $table->json('ec_pois_intersecting')->nullable();
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
            $table->dropColumn('hiking_routes_intersecting');
            $table->dropColumn('huts_intersecting');
            $table->dropColumn('sections_intersecting');
            $table->dropColumn('ec_pois_intersecting');
        });
    }
}
