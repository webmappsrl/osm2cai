<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Province;
use App\Models\Region;
use App\Models\Sector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShapefileGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function testRegionShapefileGeneration()
    {
        Storage::disk('public')->deleteDirectory('shape_files');
        $region = $this->_getRegion();
        $shapefileLocation = $region->getShapefile();

        $this->assertTrue(Storage::disk('public')->exists($shapefileLocation));
    }

    public function testProvinceShapefileGeneration()
    {
        Storage::disk('public')->deleteDirectory('shape_files');
        $region = $this->_getRegion();

        $province = Province::first();
        $shapefileLocation = $province->getShapefile();

        $this->assertTrue(Storage::disk('public')->exists($shapefileLocation));
    }

    public function testAreaShapefileGeneration()
    {
        Storage::disk('public')->deleteDirectory('shape_files');
        $region = $this->_getRegion();

        $area = Area::first();
        $shapefileLocation = $area->getShapefile();

        $this->assertTrue(Storage::disk('public')->exists($shapefileLocation));
    }

    public function testSectorShapefileGeneration()
    {

        Storage::disk('public')->deleteDirectory('shape_files');
        $region = $this->_getRegion();

        $sector = Sector::first();
        $shapefileLocation = $sector->getShapefile();

        $this->assertTrue(Storage::disk('public')->exists($shapefileLocation));
    }

    private function _getGeometry($type, $coordinates): string
    {
        $geojson = json_encode([
            "type" => $type,
            "coordinates" => $coordinates
        ]);
        $res = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . $geojson . '\') as geom'));
        return $res[0]->geom;
    }

    private function _getRegion(): Region
    {
        $fake_geom = ['geometry' => $this->_getGeometry('Polygon', [[[-2, -2], [-2, -1], [-1, -1], [-2, -2]]])];
        $region = Region::factory($fake_geom)->has(
            Province::factory($fake_geom)->has(
                Area::factory($fake_geom)->has(
                    Sector::factory($fake_geom)->count(2)
                )->count(2)
            )->count(2)
        )->create()->first();
        return $region;
    }

}
