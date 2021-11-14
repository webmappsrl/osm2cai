<?php

namespace Tests\Unit\Models;

use App\Models\HikingRoute;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HikingRouteComputeTechInfoTest extends TestCase
{
    /**
     * First set of test: create new hiking route with OSM geom AND NO CAI geom.
     * In this case computed tech value must be taken from OSM geom.
     *
     * Second set of test: create new hiking route with OSM geom AND CAI geom.
     * In this case computed tech value must be taken from CAI geom AND NOT from Osm geom.
     */

    /**
     * OSM geometry
     * distance: 441
     * ascent:
     * descent:
     * duration forward:
     * duration backword:
     */
    private function _getOsmGeom()
    {
        $geojson = json_encode([
            "type" => "LineString",
            "coordinates" => [
                [
                    10.435316562652588,
                    43.77163613277956
                ],
                [
                    10.437934398651123,
                    43.7710318385836
                ],
                [
                    10.4360032081604,
                    43.772441848876234
                ]

            ]
        ]);

        return DB::raw('ST_GeomFromGeoJSON(\'' . $geojson . '\')');
    }

    /**
     * CAI geometry
     * distance: 671
     * ascent:
     * descent:
     * duration forward:
     * duration backword:
     */
    private function _getCaiGeom()
    {
        $geojson = json_encode([
            "type" => "LineString",
            "coordinates" => [
                [
                    10.43527364730835,
                    43.77163613277956
                ],
                [
                    10.437912940979004,
                    43.77104733338286
                ],
                [
                    10.436089038848877,
                    43.77245734331019
                ],
                [
                    10.438706874847412,
                    43.771527670168346
                ]

            ]
        ]);

        return DB::raw('ST_GeomFromGeoJSON(\'' . $geojson . '\')');
    }

    public function testIfNoGeometryNothingIsDone()
    {
        $r = new HikingRoute();
        $r->computeAndSetTechInfo();
        $this->assertTrue(is_null($r->distance_computed));
        $this->assertTrue(is_null($r->ascent_computed));
        $this->assertTrue(is_null($r->descent_computed));
        $this->assertTrue(is_null($r->duration_forward_computed));
        $this->assertTrue(is_null($r->duration_backward_computed));
    }

    /**
     * First set
     */

    public function testDistanceWithOsmGeom()
    {
        $r = new HikingRoute(['relation_id' => 1234, 'geometry' => null]);
        $r->geometry_osm = $this->_getOsmGeom();
        $r->save();
        $r->computeAndSetTechInfo();
        $this->assertEquals(0.44, $r->distance_comp);
    }

    public function _testAscentWithOsmGeom()
    {
    }

    public function _testDecentWithOsmGeom()
    {
    }

    public function _testDurationForwardWithOsmGeom()
    {
    }

    public function _testDurationBackwardWithOsmGeom()
    {
    }

    /**
     * First set
     */

    public function testDistanceWithCaiGeom()
    {
        $r = new HikingRoute(['relation_id' => 1234]);
        $r->geometry_osm = $this->_getOsmGeom();
        $r->geometry = $this->_getCaiGeom();
        $r->save();
        $r->computeAndSetTechInfo();
        $this->assertEquals(0.67, $r->distance_comp);
    }

    public function _testAscentWithCaiGeom()
    {
    }

    public function _testDecentWithCaiGeom()
    {
    }

    public function _testDurationForwardWithCaiGeom()
    {
    }

    public function _testDurationBackwardWithCaiGeom()
    {
    }
}
