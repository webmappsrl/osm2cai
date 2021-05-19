<?php

namespace Tests\Unit;

use App\Models\Area;
use App\Models\HikingRoute;
use App\Models\Province;
use App\Models\Region;
use App\Models\Sector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use phpseclib3\Math\PrimeField\Integer;
use Tests\TestCase;


class HikingRouteComputeAndSetTerritorialUnitsTest extends TestCase
{
    use RefreshDatabase;

    private $firstTimeGenerateAllSectors = true;
    private $firstTimeGenerateAllAreas = true;
    private $firstTimeGenerateAllProvinces = true;
    private $firstTimeGenerateAllRegions = true;

    public function _getGeometry($type, $coordinates): string
    {
        $geojson = json_encode([
            "type" => $type,
            "coordinates" => $coordinates
        ]);
        $res = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . $geojson . '\') as geom'));
        return $res[0]->geom;
    }

    /**
     * Simple saved track across LPIO1 and LLUO1 sectors
     * @return HikingRoute
     */
    private function _getHikingRouteWithOsmGeometry($coordinates): HikingRoute
    {
        static $relation_id = 1234;
        $r = new HikingRoute(['relation_id' => $relation_id]);
        $relation_id++;
        $r->geometry_osm = $this->_getGeometry('LineString', $coordinates);
        return $r;
    }

    private function _getHikingRouteWithCaiGeometry($coordinates): HikingRoute
    {
        static $relation_id = 1234;
        $r = new HikingRoute(['relation_id' => $relation_id]);
        $relation_id++;
        $r->geometry = $this->_getGeometry('LineString', $coordinates);
        return $r;
    }

    private function _getHikingRouteWithOsmGeometryAndCaiGeometry($osm_coordinates, $cai_coordinates): HikingRoute
    {
        static $relation_id = 1234;
        $r = new HikingRoute(['relation_id' => $relation_id]);
        $relation_id++;
        $r->geometry_osm = $this->_getGeometry('LineString', $osm_coordinates);
        $r->geometry = $this->_getGeometry('LineString', $cai_coordinates);
        return $r;
    }

    /**
     * Sector
     */

    public function _generateSector($code, $coordinates): void
    {
        $data = ['code' => $code,
            'geometry' => $this->_getGeometry('Polygon', $coordinates)];
        $fake_geom = ['geometry' => $this->_getGeometry('Polygon', [[[-2, -2], [-2, -1], [-1, -1], [-2, -2]]])];
        Region::factory($fake_geom)
            ->has(Province::factory($fake_geom)->has(
                Area::factory($fake_geom)
                    ->has(
                        Sector::factory($data)->count(1)
                    )
                    ->count(1)
            )->count(1))
            ->create();
    }

    public function _generateAllSectors(): void
    {
        if ($this->firstTimeGenerateAllSectors) {
            $this->firstTimeGenerateAllSectors = false;
            $this->_generateSector('A', [[[0, 0], [0, 5], [5, 5], [5, 0], [0, 0]]]);
            $this->_generateSector('B', [[[0, 5], [0, 10], [5, 10], [5, 5], [0, 5]]]);
            $this->_generateSector('C', [[[5, 5], [5, 10], [10, 10], [10, 5], [5, 5]]]);
            $this->_generateSector('D', [[[5, 0], [5, 5], [10, 5], [10, 0], [5, 0]]]);
        }
    }

    public function testNoGeometryNoSectors()
    {
        $r = new HikingRoute(['relation_id' => 1234]);
        $r->computeAndSetSectors();
        $this->assertEquals(0, $r->sectors()->count());
    }

    public function testHikingRouteWithOsmGeometryHasSectorA()
    {
        $this->_generateAllSectors();
        $r = $this->_getHikingRouteWithOsmGeometry([[1, 1], [2, 2]]);
        $r->computeAndSetSectors();
        $r->save();
        $this->assertEquals(1, $r->sectors()->count());
        $this->assertEquals('A', $r->sectors()->first()->code);
    }

    public function testHikingRouteWithOsmGeometryHasSectorAandB()
    {
        $this->_generateAllSectors();
        $r = $this->_getHikingRouteWithOsmGeometry([[3, 4], [3, 6]]);
        $r->computeAndSetSectors();
        $r->save();
        $this->assertEquals(2, $r->sectors()->count());
        $codes = [];
        foreach ($r->sectors as $sector) {
            $codes[] = $sector->code;
        }
        $this->assertTrue(in_array('A', $codes));
        $this->assertTrue(in_array('B', $codes));
    }

    public function testHikingRouteWithOsmGeometryNoSector()
    {
        $this->_generateAllSectors();
        $r = $this->_getHikingRouteWithOsmGeometry([[15, 1], [15, 2]]);
        $r->computeAndSetSectors();
        $r->save();
        $this->assertEquals(0, $r->sectors()->count());
    }

    public function testHikingRouteWithCaiGeometryHasSectorA()
    {
        $this->_generateAllSectors();
        $r = $this->_getHikingRouteWithCaiGeometry([[1, 1], [2, 2]]);
        $r->computeAndSetSectors();
        $r->save();
        $this->assertEquals(1, $r->sectors()->count());
        $this->assertEquals('A', $r->sectors()->first()->code);
    }

    public function testHikingRouteWithCaiGeometryHasSectorAandB()
    {
        $this->_generateAllSectors();
        $r = $this->_getHikingRouteWithCaiGeometry([[3, 4], [3, 6]]);
        $r->computeAndSetSectors();
        $r->save();
        $this->assertEquals(2, $r->sectors()->count());
        $codes = [];
        foreach ($r->sectors as $sector) {
            $codes[] = $sector->code;
        }
        $this->assertTrue(in_array('A', $codes));
        $this->assertTrue(in_array('B', $codes));
    }

    public function testHikingRouteWithCaiGeometryNoSector()
    {
        $this->_generateAllSectors();
        $r = $this->_getHikingRouteWithCaiGeometry([[15, 1], [15, 2]]);
        $r->computeAndSetSectors();
        $r->save();
        $this->assertEquals(0, $r->sectors()->count());
    }

    public function testHikingRouteWithCaiGeometryAndOsmGeometryHasSectorAandNotB()
    {
        $this->_generateAllSectors();
        $r = $this->_getHikingRouteWithOsmGeometryAndCaiGeometry([[3, 6], [3, 7]], [[1, 1], [2, 2]]);
        $r->computeAndSetSectors();
        $r->save();
        $this->assertEquals(1, $r->sectors()->count());
        $this->assertEquals('A', $r->sectors()->first()->code);
    }

    /**
     * Area
     */

    public function _generateArea($code, $coordinates): void
    {
        $data = ['code' => $code,
            'geometry' => $this->_getGeometry('Polygon', $coordinates)];
        $fake_geom = ['geometry' => $this->_getGeometry('Polygon', [[[-2, -2], [-2, -1], [-1, -1], [-2, -2]]])];
        Region::factory($fake_geom)
            ->has(Province::factory($fake_geom)->has(
                Area::factory($data)->count(1)
            )->count(1))
            ->create();
    }

    public function _generateAllAreas(): void
    {
        if ($this->firstTimeGenerateAllAreas) {
            $this->firstTimeGenerateAllAreas = false;
            $this->_generateArea('A', [[[0, 0], [0, 5], [5, 5], [5, 0], [0, 0]]]);
            $this->_generateArea('B', [[[0, 5], [0, 10], [5, 10], [5, 5], [0, 5]]]);
            $this->_generateArea('C', [[[5, 5], [5, 10], [10, 10], [10, 5], [5, 5]]]);
            $this->_generateArea('D', [[[5, 0], [5, 5], [10, 5], [10, 0], [5, 0]]]);
        }
    }

    public function testNoGeometryNoAreas()
    {
        $r = new HikingRoute(['relation_id' => 1234]);
        $r->computeAndSetAreas();
        $this->assertEquals(0, $r->areas()->count());
    }

    public function testHikingRouteWithOsmGeometryHasAreaA()
    {
        $this->_generateAllAreas();
        $r = $this->_getHikingRouteWithOsmGeometry([[1, 1], [2, 2]]);
        $r->computeAndSetAreas();
        $r->save();
        $this->assertEquals(1, $r->areas()->count());
        $this->assertEquals('A', $r->areas()->first()->code);
    }

    public function testHikingRouteWithOsmGeometryHasAreaAandB()
    {
        $this->_generateAllAreas();
        $r = $this->_getHikingRouteWithOsmGeometry([[3, 4], [3, 6]]);
        $r->computeAndSetAreas();
        $r->save();
        $this->assertEquals(2, $r->areas()->count());
        $codes = [];
        foreach ($r->areas as $area) {
            $codes[] = $area->code;
        }
        $this->assertTrue(in_array('A', $codes));
        $this->assertTrue(in_array('B', $codes));
    }

    public function testHikingRouteWithOsmGeometryNoArea()
    {
        $this->_generateAllAreas();
        $r = $this->_getHikingRouteWithOsmGeometry([[15, 1], [15, 2]]);
        $r->computeAndSetAreas();
        $r->save();
        $this->assertEquals(0, $r->areas()->count());
    }

    public function testHikingRouteWithCaiGeometryHasAreaA()
    {
        $this->_generateAllAreas();
        $r = $this->_getHikingRouteWithCaiGeometry([[1, 1], [2, 2]]);
        $r->computeAndSetAreas();
        $r->save();
        $this->assertEquals(1, $r->areas()->count());
        $this->assertEquals('A', $r->areas()->first()->code);
    }

    public function testHikingRouteWithCaiGeometryHasAreaAandB()
    {
        $this->_generateAllAreas();
        $r = $this->_getHikingRouteWithCaiGeometry([[3, 4], [3, 6]]);
        $r->computeAndSetAreas();
        $r->save();
        $this->assertEquals(2, $r->areas()->count());
        $codes = [];
        foreach ($r->areas as $area) {
            $codes[] = $area->code;
        }
        $this->assertTrue(in_array('A', $codes));
        $this->assertTrue(in_array('B', $codes));
    }

    public function testHikingRouteWithCaiGeometryNoArea()
    {
        $this->_generateAllAreas();
        $r = $this->_getHikingRouteWithCaiGeometry([[15, 1], [15, 2]]);
        $r->computeAndSetAreas();
        $r->save();
        $this->assertEquals(0, $r->areas()->count());
    }

    public function testHikingRouteWithCaiGeometryAndOsmGeometryHasAreaAandNotB()
    {
        $this->_generateAllAreas();
        $r = $this->_getHikingRouteWithOsmGeometryAndCaiGeometry([[3, 6], [3, 7]], [[1, 1], [2, 2]]);
        $r->computeAndSetAreas();
        $r->save();
        $this->assertEquals(1, $r->areas()->count());
        $this->assertEquals('A', $r->areas()->first()->code);
    }

    /**
     * Province
     */

    public function _generateProvince($code, $coordinates): void
    {
        $data = ['code' => $code,
            'geometry' => $this->_getGeometry('Polygon', $coordinates)];
        $fake_geom = ['geometry' => $this->_getGeometry('Polygon', [[[-2, -2], [-2, -1], [-1, -1], [-2, -2]]])];
        Region::factory($fake_geom)
            ->has(Province::factory($data)->count(1))
            ->create();
    }

    public function _generateAllProvinces(): void
    {
        if ($this->firstTimeGenerateAllProvinces) {
            $this->firstTimeGenerateAllProvinces = false;
            $this->_generateProvince('A', [[[0, 0], [0, 5], [5, 5], [5, 0], [0, 0]]]);
            $this->_generateProvince('B', [[[0, 5], [0, 10], [5, 10], [5, 5], [0, 5]]]);
            $this->_generateProvince('C', [[[5, 5], [5, 10], [10, 10], [10, 5], [5, 5]]]);
            $this->_generateProvince('D', [[[5, 0], [5, 5], [10, 5], [10, 0], [5, 0]]]);
        }
    }

    public function testNoGeometryNoProvinces()
    {
        $r = new HikingRoute(['relation_id' => 1234]);
        $r->computeAndSetProvinces();
        $this->assertEquals(0, $r->provinces()->count());
    }

    public function testHikingRouteWithOsmGeometryHasProvinceA()
    {
        $this->_generateAllProvinces();
        $r = $this->_getHikingRouteWithOsmGeometry([[1, 1], [2, 2]]);
        $r->computeAndSetProvinces();
        $r->save();
        $this->assertEquals(1, $r->provinces()->count());
        $this->assertEquals('A', $r->provinces()->first()->code);
    }

    public function testHikingRouteWithOsmGeometryHasProvinceAandB()
    {
        $this->_generateAllProvinces();
        $r = $this->_getHikingRouteWithOsmGeometry([[3, 4], [3, 6]]);
        $r->computeAndSetProvinces();
        $r->save();
        $this->assertEquals(2, $r->provinces()->count());
        $codes = [];
        foreach ($r->provinces as $province) {
            $codes[] = $province->code;
        }
        $this->assertTrue(in_array('A', $codes));
        $this->assertTrue(in_array('B', $codes));
    }

    public function testHikingRouteWithOsmGeometryNoProvince()
    {
        $this->_generateAllProvinces();
        $r = $this->_getHikingRouteWithOsmGeometry([[15, 1], [15, 2]]);
        $r->computeAndSetProvinces();
        $r->save();
        $this->assertEquals(0, $r->provinces()->count());
    }

    public function testHikingRouteWithCaiGeometryHasProvinceA()
    {
        $this->_generateAllProvinces();
        $r = $this->_getHikingRouteWithCaiGeometry([[1, 1], [2, 2]]);
        $r->computeAndSetProvinces();
        $r->save();
        $this->assertEquals(1, $r->provinces()->count());
        $this->assertEquals('A', $r->provinces()->first()->code);
    }

    public function testHikingRouteWithCaiGeometryHasProvinceAandB()
    {
        $this->_generateAllProvinces();
        $r = $this->_getHikingRouteWithCaiGeometry([[3, 4], [3, 6]]);
        $r->computeAndSetProvinces();
        $r->save();
        $this->assertEquals(2, $r->provinces()->count());
        $codes = [];
        foreach ($r->provinces as $province) {
            $codes[] = $province->code;
        }
        $this->assertTrue(in_array('A', $codes));
        $this->assertTrue(in_array('B', $codes));
    }

    public function testHikingRouteWithCaiGeometryNoProvince()
    {
        $this->_generateAllProvinces();
        $r = $this->_getHikingRouteWithCaiGeometry([[15, 1], [15, 2]]);
        $r->computeAndSetProvinces();
        $r->save();
        $this->assertEquals(0, $r->provinces()->count());
    }

    public function testHikingRouteWithCaiGeometryAndOsmGeometryHasProvinceAandNotB()
    {
        $this->_generateAllProvinces();
        $r = $this->_getHikingRouteWithOsmGeometryAndCaiGeometry([[3, 6], [3, 7]], [[1, 1], [2, 2]]);
        $r->computeAndSetProvinces();
        $r->save();
        $this->assertEquals(1, $r->provinces()->count());
        $this->assertEquals('A', $r->provinces()->first()->code);
    }

    /**
     * Region
     */

    public function _generateRegion($code, $coordinates): void
    {
        $data = ['code' => $code,
            'geometry' => $this->_getGeometry('Polygon', $coordinates)];
        Region::factory($data)->count(1)->create();
    }

    public function _generateAllRegions(): void
    {
        if ($this->firstTimeGenerateAllRegions) {
            $this->firstTimeGenerateAllRegions = false;
            $this->_generateRegion('A', [[[0, 0], [0, 5], [5, 5], [5, 0], [0, 0]]]);
            $this->_generateRegion('B', [[[0, 5], [0, 10], [5, 10], [5, 5], [0, 5]]]);
            $this->_generateRegion('C', [[[5, 5], [5, 10], [10, 10], [10, 5], [5, 5]]]);
            $this->_generateRegion('D', [[[5, 0], [5, 5], [10, 5], [10, 0], [5, 0]]]);
        }
    }

    public function testNoGeometryNoRegions()
    {
        $r = new HikingRoute(['relation_id' => 1234]);
        $r->computeAndSetRegions();
        $this->assertEquals(0, $r->regions()->count());
    }

    public function testHikingRouteWithOsmGeometryHasRegionA()
    {
        $this->_generateAllRegions();
        $r = $this->_getHikingRouteWithOsmGeometry([[1, 1], [2, 2]]);
        $r->computeAndSetRegions();
        $r->save();
        $this->assertEquals(1, $r->regions()->count());
        $this->assertEquals('A', $r->regions()->first()->code);
    }

    public function testHikingRouteWithOsmGeometryHasRegionAandB()
    {
        $this->_generateAllRegions();
        $r = $this->_getHikingRouteWithOsmGeometry([[3, 4], [3, 6]]);
        $r->computeAndSetRegions();
        $r->save();
        $this->assertEquals(2, $r->regions()->count());
        $codes = [];
        foreach ($r->regions as $region) {
            $codes[] = $region->code;
        }
        $this->assertTrue(in_array('A', $codes));
        $this->assertTrue(in_array('B', $codes));
    }

    public function testHikingRouteWithOsmGeometryNoRegion()
    {
        $this->_generateAllRegions();
        $r = $this->_getHikingRouteWithOsmGeometry([[15, 1], [15, 2]]);
        $r->computeAndSetRegions();
        $r->save();
        $this->assertEquals(0, $r->regions()->count());
    }

    public function testHikingRouteWithCaiGeometryHasRegionA()
    {
        $this->_generateAllRegions();
        $r = $this->_getHikingRouteWithCaiGeometry([[1, 1], [2, 2]]);
        $r->computeAndSetRegions();
        $r->save();
        $this->assertEquals(1, $r->regions()->count());
        $this->assertEquals('A', $r->regions()->first()->code);
    }

    public function testHikingRouteWithCaiGeometryHasRegionAandB()
    {
        $this->_generateAllRegions();
        $r = $this->_getHikingRouteWithCaiGeometry([[3, 4], [3, 6]]);
        $r->computeAndSetRegions();
        $r->save();
        $this->assertEquals(2, $r->regions()->count());
        $codes = [];
        foreach ($r->regions as $region) {
            $codes[] = $region->code;
        }
        $this->assertTrue(in_array('A', $codes));
        $this->assertTrue(in_array('B', $codes));
    }

    public function testHikingRouteWithCaiGeometryNoRegion()
    {
        $this->_generateAllRegions();
        $r = $this->_getHikingRouteWithCaiGeometry([[15, 1], [15, 2]]);
        $r->computeAndSetRegions();
        $r->save();
        $this->assertEquals(0, $r->regions()->count());
    }

    public function testHikingRouteWithCaiGeometryAndOsmGeometryHasRegionAandNotB()
    {
        $this->_generateAllRegions();
        $r = $this->_getHikingRouteWithOsmGeometryAndCaiGeometry([[3, 6], [3, 7]], [[1, 1], [2, 2]]);
        $r->computeAndSetRegions();
        $r->save();
        $this->assertEquals(1, $r->regions()->count());
        $this->assertEquals('A', $r->regions()->first()->code);
    }

    /**
     * Sector Area Province and Region at once
     */
    public function testHikingRouteWithCaiGeometryAndOsmGeometryHasAllTerritorialUnitsAAndNotB()
    {
        $this->_generateAllSectors();
        $this->_generateAllAreas();
        $this->_generateAllProvinces();
        $this->_generateAllRegions();

        $r = $this->_getHikingRouteWithOsmGeometryAndCaiGeometry([[3, 6], [3, 7]], [[1, 1], [2, 2]]);
        $r->computeAndSetTerritorialUnits();
        $r->save();

        $this->assertEquals(1, $r->regions()->count());
        $this->assertEquals('A', $r->regions()->first()->code);

        $this->assertEquals(1, $r->provinces()->count());
        $this->assertEquals('A', $r->provinces()->first()->code);

        $this->assertEquals(1, $r->areas()->count());
        $this->assertEquals('A', $r->areas()->first()->code);

        $this->assertEquals(1, $r->sectors()->count());
        $this->assertEquals('A', $r->sectors()->first()->code);
    }
    
}
