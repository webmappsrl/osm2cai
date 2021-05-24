<?php

namespace Tests\Unit;

use App\Models\HikingRoute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fixtures\TerritorialUnitsFixtures;
use Tests\TestCase;

class HikingRouteIdsByBoundingBoxTest extends TestCase
{
    use RefreshDatabase;

    public function testNoGeom()
    {
        HikingRoute::truncate();
        $r = HikingRoute::factory(["geometry" => null])->create();
        $ids = $r->idsByBoundingBox(0, 0, 0, 5, 5);
        $this->assertIsArray($ids);
        $this->assertCount(0, $ids);
    }

    public function testWrongStatus()
    {
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();
        $r = $t->getHikingRouteWithOsmGeometry([[1, 1], [2, 2]]);
        $r->save();

        $ids = HikingRoute::idsByBoundingBox(6, 0, 0, 5, 5);
        $this->assertIsArray($ids);
        $this->assertCount(0, $ids);

    }

    /**
     * OSM GEOM
     */
    public function testOsmGeomInBB()
    {
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();
        $r = $t->getHikingRouteWithOsmGeometry([[1, 1], [2, 2]]);
        $r->osm2cai_status = 1;
        $r->save();

        $ids = HikingRoute::idsByBoundingBox(1, 0, 0, 5, 5);
        $this->assertIsArray($ids);
        $this->assertCount(1, $ids);
        $this->assertEquals($r->id, $ids[0]);
    }

    public function testOsmGeomInBBButDifferentStatus()
    {
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();
        $r = $t->getHikingRouteWithOsmGeometry([[1, 1], [2, 2]]);
        $r->osm2cai_status = 1;
        $r->save();

        $ids = HikingRoute::idsByBoundingBox(2, 0, 0, 5, 5);
        $this->assertIsArray($ids);
        $this->assertCount(0, $ids);
    }

    public function testOsmGeomPartiallyInBB()
    {
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();
        $r = $t->getHikingRouteWithOsmGeometry([[1, 1], [6, 6]]);
        $r->osm2cai_status = 1;
        $r->save();

        $ids = HikingRoute::idsByBoundingBox(1, 0, 0, 5, 5);
        $this->assertIsArray($ids);
        $this->assertCount(1, $ids);
        $this->assertEquals($r->id, $ids[0]);
    }

    public function testOsmGeomOutBB()
    {
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();
        $r = $t->getHikingRouteWithOsmGeometry([[6, 6], [7, 7]]);
        $r->osm2cai_status = 1;
        $r->save();

        $ids = HikingRoute::idsByBoundingBox(1, 0, 0, 5, 5);
        $this->assertIsArray($ids);
        $this->assertCount(0, $ids);
    }

    /**
     * CAI Geometry -> status 4
     */

    public function testCaiGeomInBB()
    {
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();
        $r = $t->getHikingRouteWithCaiGeometry([[1, 1], [2, 2]]);
        $r->osm2cai_status = 4;
        $r->save();

        $ids = HikingRoute::idsByBoundingBox(4, 0, 0, 5, 5);
        $this->assertIsArray($ids);
        $this->assertCount(1, $ids);
        $this->assertEquals($r->id, $ids[0]);
    }

    public function testCaiGeomInBBButDifferentStatus()
    {
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();
        $r = $t->getHikingRouteWithCaiGeometry([[1, 1], [2, 2]]);
        $r->osm2cai_status = 4;
        $r->save();

        $ids = HikingRoute::idsByBoundingBox(3, 0, 0, 5, 5);
        $this->assertIsArray($ids);
        $this->assertCount(0, $ids);
    }

    public function testCaiGeomPartiallyInBB()
    {
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();
        $r = $t->getHikingRouteWithCaiGeometry([[1, 1], [6, 6]]);
        $r->osm2cai_status = 4;
        $r->save();

        $ids = HikingRoute::idsByBoundingBox(4, 0, 0, 5, 5);
        $this->assertIsArray($ids);
        $this->assertCount(1, $ids);
        $this->assertEquals($r->id, $ids[0]);
    }

    public function testCaiGeomOutBB()
    {
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();
        $r = $t->getHikingRouteWithCaiGeometry([[6, 6], [7, 7]]);
        $r->osm2cai_status = 4;
        $r->save();

        $ids = HikingRoute::idsByBoundingBox(4, 0, 0, 5, 5);
        $this->assertIsArray($ids);
        $this->assertCount(0, $ids);
    }

    /**
     * Osm AND CAI Mixed
     */
    public function testOsmAndCaiGeomInBB()
    {
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();

        $r = $t->getHikingRouteWithOsmGeometryAndCaiGeometry([[1, 1], [2, 2]], [[1, 1], [2, 2]]);
        $r->osm2cai_status = 4;
        $r->save();

        $ids = HikingRoute::idsByBoundingBox(4, 0, 0, 5, 5);
        $this->assertIsArray($ids);
        $this->assertCount(1, $ids);
        $this->assertEquals($r->id, $ids[0]);

        $ids = HikingRoute::idsByBoundingBox(3, 0, 0, 5, 5);
        $this->assertIsArray($ids);
        $this->assertCount(0, $ids);

    }

}
