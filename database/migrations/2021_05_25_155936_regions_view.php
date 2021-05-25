<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RegionsView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("
            CREATE OR REPLACE VIEW regions_view 
            AS
            SELECT
               regions.name,
               (SELECT count(*) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status <> 0) as tot,
               (SELECT count(*) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status = 4) as tot4,
               (SELECT count(*) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status = 3) as tot3,
               (SELECT count(*) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status = 2) as tot2,
               (SELECT count(*) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status = 1) as tot1,
               regions.geometry
            FROM
               regions;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement("
            DROP VIEW IF EXISTS regions_view;
        ");
    }
}
