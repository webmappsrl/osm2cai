<?php


use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TerritorialUnitViewSections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // SECTIONS VIEW
        \DB::statement("
    CREATE OR REPLACE VIEW sections_view 
    AS
    SELECT
       sections.id,    
       sections.name,
       sections.cai_code,
       (SELECT count(*) FROM hiking_route_section LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE section_id=sections.id AND osm2cai_status <> 0) as tot,
       (SELECT count(*) FROM hiking_route_section LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE section_id=sections.id AND osm2cai_status = 4) as tot4,
       (SELECT count(*) FROM hiking_route_section LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE section_id=sections.id AND osm2cai_status = 3) as tot3,
       (SELECT count(*) FROM hiking_route_section LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE section_id=sections.id AND osm2cai_status = 2) as tot2,
       (SELECT count(*) FROM hiking_route_section LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE section_id=sections.id AND osm2cai_status = 1) as tot1,
       (SELECT sum(distance_comp) FROM hiking_route_section LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE section_id=sections.id AND osm2cai_status <> 0) as km_tot,
       (SELECT sum(distance_comp) FROM hiking_route_section LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE section_id=sections.id AND osm2cai_status = 4) as km_tot4,
       (SELECT sum(distance_comp) FROM hiking_route_section LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE section_id=sections.id AND osm2cai_status = 3) as km_tot3,
       (SELECT sum(distance_comp) FROM hiking_route_section LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE section_id=sections.id AND osm2cai_status = 2) as km_tot2,
       (SELECT sum(distance_comp) FROM hiking_route_section LEFT JOIN hiking_routes ON hiking_route_id=hiking_routes.id WHERE section_id=sections.id AND osm2cai_status = 1) as km_tot1
    FROM
       sections;
");;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement("
    DROP VIEW IF EXISTS sections_view;
");
    }
}
