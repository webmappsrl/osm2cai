<?php

namespace Tests\Fixtures;

use App\Models\Area;
use App\Models\HikingRoute;
use App\Models\Province;
use App\Models\Region;
use App\Models\Sector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;


class TerritorialUnitsFixtures
{

    /**
     * Singleton Management
     */
    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new TerritorialUnitsFixtures();
        }
        return self::$instance;
    }
    
    use RefreshDatabase;

    private $firstTimeGenerateAllSectors = true;
    private $firstTimeGenerateAllAreas = true;
    private $firstTimeGenerateAllProvinces = true;
    private $firstTimeGenerateAllRegions = true;

    public function getGeometry($type, $coordinates): string
    {
        $geojson = json_encode([
            "type" => $type,
            "coordinates" => $coordinates
        ]);
        $res = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . $geojson . '\') as geom'));
        return $res[0]->geom;
    }

    public function generateSector($code, $coordinates): void
    {
        $data = ['code' => $code,
            'geometry' => $this->getGeometry('Polygon', $coordinates)];
        $fake_geom = ['geometry' => $this->getGeometry('Polygon', [[[-2, -2], [-2, -1], [-1, -1], [-2, -2]]])];
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

    public function generateAllSectors(): void
    {
        if ($this->firstTimeGenerateAllSectors) {
            $this->firstTimeGenerateAllSectors = false;
            $this->generateSector('A', [[[0, 0], [0, 5], [5, 5], [5, 0], [0, 0]]]);
            $this->generateSector('B', [[[0, 5], [0, 10], [5, 10], [5, 5], [0, 5]]]);
            $this->generateSector('C', [[[5, 5], [5, 10], [10, 10], [10, 5], [5, 5]]]);
            $this->generateSector('D', [[[5, 0], [5, 5], [10, 5], [10, 0], [5, 0]]]);
        }
    }

    public function generateArea($code, $coordinates): void
    {
        $data = ['code' => $code,
            'geometry' => $this->getGeometry('Polygon', $coordinates)];
        $fake_geom = ['geometry' => $this->getGeometry('Polygon', [[[-2, -2], [-2, -1], [-1, -1], [-2, -2]]])];
        Region::factory($fake_geom)
            ->has(Province::factory($fake_geom)->has(
                Area::factory($data)->count(1)
            )->count(1))
            ->create();
    }

    public function generateAllAreas(): void
    {
        if ($this->firstTimeGenerateAllAreas) {
            $this->firstTimeGenerateAllAreas = false;
            $this->generateArea('A', [[[0, 0], [0, 5], [5, 5], [5, 0], [0, 0]]]);
            $this->generateArea('B', [[[0, 5], [0, 10], [5, 10], [5, 5], [0, 5]]]);
            $this->generateArea('C', [[[5, 5], [5, 10], [10, 10], [10, 5], [5, 5]]]);
            $this->generateArea('D', [[[5, 0], [5, 5], [10, 5], [10, 0], [5, 0]]]);
        }
    }

    public function generateProvince($code, $coordinates): void
    {
        $data = ['code' => $code,
            'geometry' => $this->getGeometry('Polygon', $coordinates)];
        $fake_geom = ['geometry' => $this->getGeometry('Polygon', [[[-2, -2], [-2, -1], [-1, -1], [-2, -2]]])];
        Region::factory($fake_geom)
            ->has(Province::factory($data)->count(1))
            ->create();
    }

    public function generateAllProvinces(): void
    {
        if ($this->firstTimeGenerateAllProvinces) {
            $this->firstTimeGenerateAllProvinces = false;
            $this->generateProvince('A', [[[0, 0], [0, 5], [5, 5], [5, 0], [0, 0]]]);
            $this->generateProvince('B', [[[0, 5], [0, 10], [5, 10], [5, 5], [0, 5]]]);
            $this->generateProvince('C', [[[5, 5], [5, 10], [10, 10], [10, 5], [5, 5]]]);
            $this->generateProvince('D', [[[5, 0], [5, 5], [10, 5], [10, 0], [5, 0]]]);
        }
    }

    public function generateRegion($code, $coordinates): void
    {
        $data = ['code' => $code,
            'geometry' => $this->getGeometry('Polygon', $coordinates)];
        Region::factory($data)->count(1)->create();
    }

    public function generateAllRegions(): void
    {
        if ($this->firstTimeGenerateAllRegions) {
            $this->firstTimeGenerateAllRegions = false;
            $this->generateRegion('A', [[[0, 0], [0, 5], [5, 5], [5, 0], [0, 0]]]);
            $this->generateRegion('B', [[[0, 5], [0, 10], [5, 10], [5, 5], [0, 5]]]);
            $this->generateRegion('C', [[[5, 5], [5, 10], [10, 10], [10, 5], [5, 5]]]);
            $this->generateRegion('D', [[[5, 0], [5, 5], [10, 5], [10, 0], [5, 0]]]);
        }
    }

    /**
     * Simple saved track across LPIO1 and LLUO1 sectors
     * @return HikingRoute
     */
    public function getHikingRouteWithOsmGeometry($coordinates): HikingRoute
    {
        static $relation_id = 1234;
        $r = new HikingRoute(['relation_id' => $relation_id]);
        $relation_id++;
        $r->geometry_osm = $this->getGeometry('LineString', $coordinates);
        return $r;
    }

    public function getHikingRouteWithCaiGeometry($coordinates): HikingRoute
    {
        static $relation_id = 1234;
        $r = new HikingRoute(['relation_id' => $relation_id]);
        $relation_id++;
        $r->geometry = $this->getGeometry('LineString', $coordinates);
        return $r;
    }

    public function getHikingRouteWithOsmGeometryAndCaiGeometry($osm_coordinates, $cai_coordinates): HikingRoute
    {
        static $relation_id = 1234;
        $r = new HikingRoute(['relation_id' => $relation_id]);
        $relation_id++;
        $r->geometry_osm = $this->getGeometry('LineString', $osm_coordinates);
        $r->geometry = $this->getGeometry('LineString', $cai_coordinates);
        return $r;
    }

    public function generateAllHikingRoutes(): void
    {
        $r = $this->getHikingRouteWithOsmGeometry([[1, 1], [2, 2]]);
        $r = $this->getHikingRouteWithOsmGeometry([[3, 4], [3, 6]]);
        $r = $this->getHikingRouteWithOsmGeometry([[15, 1], [15, 2]]);

    }

    public function generateAllTerritorialUnits(): void
    {
        $this->generateAllSectors();
        $this->generateAllAreas();
        $this->generateAllProvinces();
        $this->generateAllRegions();
        $this->generateAllHikingRoutes();
    }

}
