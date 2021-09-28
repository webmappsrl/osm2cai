<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TerritorialUnitViews2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        \DB::statement("
            DROP VIEW IF EXISTS regions_view;
        ");
        \DB::statement("
            DROP VIEW IF EXISTS provinces_view;
        ");

        // REGIONS VIEW
        \DB::statement("
            CREATE OR REPLACE VIEW regions_view 
            AS
            SELECT
               regions.name,
               regions.code,
               regions.num_expected,
               (SELECT count(*) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status <> 0) as tot,
               (SELECT count(*) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status = 4) as tot4,
               (SELECT count(*) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status = 3) as tot3,
               (SELECT count(*) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status = 2) as tot2,
               (SELECT count(*) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status = 1) as tot1,

               (SELECT sum(distance_comp) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status <> 0) as km_tot,
               (SELECT sum(distance_comp) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status = 4) as km_tot4,
               (SELECT sum(distance_comp) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status = 3) as km_tot3,
               (SELECT sum(distance_comp) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status = 2) as km_tot2,
               (SELECT sum(distance_comp) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status = 1) as km_tot1,
               regions.geometry
            FROM
               regions;
        ");

        // PROVINCES VIEW
        \DB::statement("
            CREATE OR REPLACE VIEW provinces_view 
            AS
            SELECT
               provinces.name,
               provinces.code,
               provinces.full_code,
               provinces.num_expected,
               (SELECT count(*) FROM hiking_route_province LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE province_id=provinces.id AND osm2cai_status <> 0) as tot,
               (SELECT count(*) FROM hiking_route_province LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE province_id=provinces.id AND osm2cai_status = 4) as tot4,
               (SELECT count(*) FROM hiking_route_province LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE province_id=provinces.id AND osm2cai_status = 3) as tot3,
               (SELECT count(*) FROM hiking_route_province LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE province_id=provinces.id AND osm2cai_status = 2) as tot2,
               (SELECT count(*) FROM hiking_route_province LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE province_id=provinces.id AND osm2cai_status = 1) as tot1,

               (SELECT sum(distance_comp) FROM hiking_route_province LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE province_id=provinces.id AND osm2cai_status <> 0) as km_tot,
               (SELECT sum(distance_comp) FROM hiking_route_province LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE province_id=provinces.id AND osm2cai_status = 4) as km_tot4,
               (SELECT sum(distance_comp) FROM hiking_route_province LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE province_id=provinces.id AND osm2cai_status = 3) as km_tot3,
               (SELECT sum(distance_comp) FROM hiking_route_province LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE province_id=provinces.id AND osm2cai_status = 2) as km_tot2,
               (SELECT sum(distance_comp) FROM hiking_route_province LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE province_id=provinces.id AND osm2cai_status = 1) as km_tot1,
               provinces.geometry
            FROM
               provinces;
        ");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
