<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TerritorialUnitViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // REGIONS VIEW
        \DB::statement("
            CREATE OR REPLACE VIEW regions_view 
            AS
            SELECT
               regions.name,
               regions.code,
               (SELECT count(*) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status <> 0) as tot,
               (SELECT count(*) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status = 4) as tot4,
               (SELECT count(*) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status = 3) as tot3,
               (SELECT count(*) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status = 2) as tot2,
               (SELECT count(*) FROM hiking_route_region LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE region_id=regions.id AND osm2cai_status = 1) as tot1,
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
               (SELECT count(*) FROM hiking_route_province LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE province_id=provinces.id AND osm2cai_status <> 0) as tot,
               (SELECT count(*) FROM hiking_route_province LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE province_id=provinces.id AND osm2cai_status = 4) as tot4,
               (SELECT count(*) FROM hiking_route_province LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE province_id=provinces.id AND osm2cai_status = 3) as tot3,
               (SELECT count(*) FROM hiking_route_province LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE province_id=provinces.id AND osm2cai_status = 2) as tot2,
               (SELECT count(*) FROM hiking_route_province LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE province_id=provinces.id AND osm2cai_status = 1) as tot1,
               provinces.geometry
            FROM
               provinces;
        ");

        // AREAS VIEW
        \DB::statement("
            CREATE OR REPLACE VIEW areas_view 
            AS
            SELECT
               areas.name,
               areas.code,
               areas.full_code,
               (SELECT count(*) FROM area_hiking_route LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE area_id=areas.id AND osm2cai_status <> 0) as tot,
               (SELECT count(*) FROM area_hiking_route LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE area_id=areas.id AND osm2cai_status = 4) as tot4,
               (SELECT count(*) FROM area_hiking_route LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE area_id=areas.id AND osm2cai_status = 3) as tot3,
               (SELECT count(*) FROM area_hiking_route LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE area_id=areas.id AND osm2cai_status = 2) as tot2,
               (SELECT count(*) FROM area_hiking_route LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE area_id=areas.id AND osm2cai_status = 1) as tot1,
               areas.geometry
            FROM
               areas;
        ");

        // SECTORS VIEW
        \DB::statement("
            CREATE OR REPLACE VIEW sectors_view 
            AS
            SELECT
               sectors.name,
               sectors.code,
               sectors.full_code,
               (SELECT count(*) FROM hiking_route_sector LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE sector_id=sectors.id AND osm2cai_status <> 0) as tot,
               (SELECT count(*) FROM hiking_route_sector LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE sector_id=sectors.id AND osm2cai_status = 4) as tot4,
               (SELECT count(*) FROM hiking_route_sector LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE sector_id=sectors.id AND osm2cai_status = 3) as tot3,
               (SELECT count(*) FROM hiking_route_sector LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE sector_id=sectors.id AND osm2cai_status = 2) as tot2,
               (SELECT count(*) FROM hiking_route_sector LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE sector_id=sectors.id AND osm2cai_status = 1) as tot1,
               sectors.geometry
            FROM
               sectors;
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
        \DB::statement("
            DROP VIEW IF EXISTS provinces_view;
        ");
        \DB::statement("
            DROP VIEW IF EXISTS areas_view;
        ");
        \DB::statement("
            DROP VIEW IF EXISTS sectors_view;
        ");
    }
}
