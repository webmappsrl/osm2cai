<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Province;
use App\Models\Region;
use App\Models\Sector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KmlGenerationTest extends TestCase
{


    public function _getGeometry($type, $coordinates): string
    {
        $geojson = json_encode([
            "type" => $type,
            "coordinates" => $coordinates
        ]);
        $res = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . $geojson . '\') as geom'));
        return $res[0]->geom;
    }


    use RefreshDatabase;

    /**
     * Test Kml for Region Model
     *
     * @return void
     */
    public function testRegionKmlGeneration()
    {
        $fake_geom = ['geometry' => $this->_getGeometry('Polygon', [[[-2, -2], [-2, -1], [-1, -1], [-2, -2]]])];
        $region = Region::factory($fake_geom)->has(
            Province::factory($fake_geom)->has(
                Area::factory($fake_geom)->has(
                    Sector::factory($fake_geom)->count(2)
                )->count(2)
            )->count(2)
        )->create()->first();
        $result = $this->get(route('api.kml.region', ['id' => $region->id]));
        $this->assertIsString($result->getContent());
        $kml_obj = simplexml_load_string($result->getContent());
        $this->assertTrue(isset($kml_obj->Document));
        $this->assertTrue(isset($kml_obj->Document->Placemark));
        $this->assertTrue(isset($kml_obj->Document->Placemark[0]));
        $this->assertTrue(isset($kml_obj->Document->Placemark[0]));
        $this->assertTrue(isset($kml_obj->Document->Placemark[0]->ExtendedData));
        $this->assertTrue(isset($kml_obj->Document->Placemark[0]->Polygon));
        $this->assertTrue(isset($kml_obj->Document->Placemark[0]->Polygon->outerBoundaryIs));
        $this->assertTrue(isset($kml_obj->Document->Placemark[0]->Polygon->outerBoundaryIs->LinearRing));
        $this->assertTrue(isset($kml_obj->Document->Placemark[0]->Polygon->outerBoundaryIs->LinearRing->coordinates));
    }
}
