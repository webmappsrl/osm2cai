<?php

namespace Tests\Feature;

use App\Models\HikingRoute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HikingRouteFileToGeometryTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_with_simple_geojson_track()
    {
        $hr = new HikingRoute();
        // Read file

        $content = file_get_contents(__DIR__."/../Fixtures/simpleGeojsonTrack.geojson");

        $geom = $hr->fileToGeometry($content);

        $g = json_decode(DB::select(DB::raw("select st_asgeojson('$geom') as g"))[0]->g,true);
        $this->assertEquals('LineString',$g['type']);
        $this->assertEquals(730,count($g['coordinates']));

    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_with_gpx_track_with_waypoints()
    {
        $hr = new HikingRoute();
        // Read file

        $content = file_get_contents(__DIR__."/../Fixtures/trackAndWayPoint.gpx");

        $geom = $hr->fileToGeometry($content);

        $g = json_decode(DB::select(DB::raw("select st_asgeojson('$geom') as g"))[0]->g,true);
        $this->assertEquals('LineString',$g['type']);
        $this->assertEquals(730,count($g['coordinates']));

    }
}
