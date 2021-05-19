<?php

namespace Tests\Unit;

use App\Models\HikingRoute;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HikingRouteActualGeometryTest extends TestCase
{

    private function _createHikingRouteOsm(): HikingRoute
    {
        static $relation_id = 1234;

        $geojson = json_encode([
            "type" => "LineString",
            "coordinates" => [
                [
                    10.497050285339355,
                    43.76164125192523
                ],
                [
                    10.49393892288208,
                    43.755163057453906
                ]
            ]
        ]);
        $r = new HikingRoute(['relation_id' => $relation_id]);
        $relation_id++;
        $r->geometry_osm = DB::raw('ST_GeomFromGeoJSON(\'' . $geojson . '\')');
        return $r;

    }

    private function _createHikingRouteOsmAndCai(): HikingRoute
    {
        static $relation_id = 1234;

        $geojson = json_encode([
            "type" => "LineString",
            "coordinates" => [
                [
                    10.497050285339355,
                    43.76164125192523
                ],
                [
                    10.49393892288208,
                    43.755163057453906
                ]
            ]
        ]);
        $r = new HikingRoute(['relation_id' => $relation_id]);
        $relation_id++;
        $r->geometry_osm = DB::raw('ST_GeomFromGeoJSON(\'' . $geojson . '\')');
        $r->geometry = DB::raw('ST_GeomFromGeoJSON(\'' . $geojson . '\')');
        return $r;

    }

    public function testWhenNoGeometryHasGeometryFalse()
    {
        $r = new HikingRoute(['relation_id' => 1234]);
        $this->assertFalse($r->hasGeometry());
    }

    public function testWhenNoGeometryGetActualGeometryFalse()
    {
        $r = new HikingRoute(['relation_id' => 1234]);
        $this->assertEquals('', $r->getActualGeometryField());
    }

    public function testWhenGeometryOsmHasGeometryTrue()
    {
        $r = $this->_createHikingRouteOsm();
        $this->assertTrue($r->hasGeometry());
    }

    public function testWhenGeometryCaiHasGeometryTrue()
    {
        $r = $this->_createHikingRouteOsmAndCai();
        $this->assertTrue($r->hasGeometry());
    }

    public function testWhenGeometryOsmActualGeometryReturnsGeometryOsm()
    {
        $r = $this->_createHikingRouteOsm();
        $this->assertEquals('geometry_osm', $r->getActualGeometryField());

    }

    public function testWhenGeometryCaiActualGeometryReturnsGeometry()
    {
        $r = $this->_createHikingRouteOsmAndCai();
        $this->assertEquals('geometry', $r->getActualGeometryField());
    }


}
