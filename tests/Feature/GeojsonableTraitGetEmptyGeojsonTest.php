<?php

namespace Tests\Feature;

use App\Models\HikingRoute;
use GeoJson\Geometry\LineString;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GeojsonableTraitGetEmptyGeojsonTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function when_hiking_routes_has_null_geometry_it_returns_null()
    {
        $hr = HikingRoute::factory()->create(['geometry' => null]);
        $this->assertNull($hr->getEmptyGeojson());

    }

    /**
     * @test
     */
    public function when_hiking_routes_has_geometry_it_returns_array()
    {
        $hr = $this->_get_hiking_route_with_simple_geometry();
        $this->assertIsArray($hr->getEmptyGeojson());
    }

    /**
     * @test
     */
    public function when_hiking_routes_has_geometry_it_returns_array_with_proper_keys()
    {
        $hr = $this->_get_hiking_route_with_simple_geometry();
        $this->assertArrayHasKey('type', $hr->getEmptyGeojson());
        $this->assertArrayHasKey('properties', $hr->getEmptyGeojson());
        $this->assertArrayHasKey('geometry', $hr->getEmptyGeojson());
        $this->assertEquals('Feature', $hr->getEmptyGeojson()['type']);
    }

    /**
     * @test
     */
    public function when_hiking_routes_has_geometry_it_returns_array_with_proper_geometry()
    {
        $hr = $this->_get_hiking_route_with_simple_geometry();
        $this->assertArrayHasKey('type', $hr->getEmptyGeojson()['geometry']);
        $this->assertArrayHasKey('coordinates', $hr->getEmptyGeojson()['geometry']);
        $this->assertEquals('LineString', $hr->getEmptyGeojson()['geometry']['type']);
    }

    /**
     * @test
     */
    public function when_hiking_routes_has_geometry_it_returns_array_with_proper_coordinates()
    {
        $hr = $this->_get_hiking_route_with_simple_geometry();
        $this->assertIsArray($hr->getEmptyGeojson()['geometry']['coordinates']);
        $this->assertEquals(0, $hr->getEmptyGeojson()['geometry']['coordinates'][0][0]);
        $this->assertEquals(0, $hr->getEmptyGeojson()['geometry']['coordinates'][0][1]);
        $this->assertEquals(2, $hr->getEmptyGeojson()['geometry']['coordinates'][1][0]);
        $this->assertEquals(2, $hr->getEmptyGeojson()['geometry']['coordinates'][1][1]);
    }

    private function _get_hiking_route_with_simple_geometry(): HikingRoute
    {
        $line = new LineString([[0, 0], [2, 2]]);
        $res = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . json_encode($line->jsonSerialize()) . '\') as geom'));
        return HikingRoute::factory()->create(['geometry' => $res[0]->geom]);
    }
}
