<?php

namespace Tests\Feature;

use App\Models\HikingRoute;
use GeoJson\Geometry\LineString;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GeojsonableTraitGetCentroidTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function when_hiking_routes_has_null_geometry_it_returns_null()
    {
        $hr = HikingRoute::factory()->create(['geometry' => null]);
        $this->assertNull($hr->getCentroid());

    }

    /**
     * @test
     */
    public function when_hiking_routes_has_geometry_it_returns_array()
    {
        $hr = $this->_get_hiking_route_with_simple_geometry();
        $this->assertIsArray($hr->getCentroid());
    }

    /**
     * @test
     */
    public function when_hiking_routes_has_geometry_it_returns_array_with_proper_elements()
    {
        $hr = $this->_get_hiking_route_with_simple_geometry();
        $this->assertEquals(1, $hr->getCentroid()[0]);
        $this->assertEquals(1, $hr->getCentroid()[1]);
    }


    private function _get_hiking_route_with_simple_geometry(): HikingRoute
    {
        $line = new LineString([[0, 0], [2, 2]]);
        $res = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . json_encode($line->jsonSerialize()) . '\') as geom'));
        return HikingRoute::factory()->create(['geometry' => $res[0]->geom]);
    }


}
