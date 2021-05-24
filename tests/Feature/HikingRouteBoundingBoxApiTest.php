<?php

namespace Tests\Feature;

use App\Models\HikingRoute;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Fixtures\TerritorialUnitsFixtures;
use Tests\TestCase;

class HikingRouteBoundingBoxApiTest extends TestCase
{
    public function testNoGeomReturnsEmptyFeatureCollection()
    {

        HikingRoute::truncate();
        $r = HikingRoute::factory(["geometry" => null])->create();

        $post_data = [
            "osm2cai_status" => 1,
            "lo0" => 0,
            "la0" => 0,
            "lo1" => 5,
            "la1" => 5
        ];

        $result = $this->postJson('/api/geojson/hiking_route/bounding_box', $post_data);
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertIsString($result->getContent());

        $geojson = json_decode($result->getContent());

        $this->assertTrue(isset($geojson->type));
        $this->assertTrue(isset($geojson->features));
        $this->assertEquals('FeatureCollection', $geojson->type);
        $this->assertIsArray($geojson->features);
        $this->assertEquals(0, count($geojson->features));


    }

    public function testWrongStatusReturnsEmptyFeatureCollection()
    {
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();
        $r = $t->getHikingRouteWithOsmGeometry([[1, 1], [2, 2]]);
        $r->save();

        $post_data = [
            "osm2cai_status" => 6,
            "lo0" => 0,
            "la0" => 0,
            "lo1" => 5,
            "la1" => 5
        ];
        $result = $this->postJson('/api/geojson/hiking_route/bounding_box', $post_data);
        $geojson = json_decode($result->getContent());
        $this->assertEquals(0, count($geojson->features));
    }

    /**
     * OSM GEOM
     */
    public function testOsmGeomInBBWithStatus1ReturnsAFeatureCollectionWithOneHikingroute()
    {

        // BUILD DATA
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();
        $r = $t->getHikingRouteWithOsmGeometry([[1, 1], [2, 2]]);
        $r->osm2cai_status = 1;
        $r->relation_id = 1234;
        $r->validation_date = '2021-01-01';

        // SET Properties
        $props = [
            'ref', 'old_ref', 'source', 'source_ref', 'survey_date', 'name', 'rwn_name',
            'ref_osm', 'old_ref_osm', 'source_osm', 'source_ref_osm', 'survey_date_osm', 'name_osm', 'rwn_name_osm',
            'ref_REI', 'ref_REI_osm', 'ref_REI_comp',
            'cai_scale', 'from', 'to', 'osmc_symbol', 'network', 'roundtrip', 'symbol', 'symbol_it',
            'cai_scale_osm', 'from_osm', 'to_osm', 'osmc_symbol_osm', 'network_osm', 'roundtrip_osm', 'symbol_osm', 'symbol_it_osm',
            "operator", "state", "description", "description_it", "website", "wikimedia_commons",
            "maintenance", "maintenance_it", "note", "note_it", "note_project_page",
            "operator_osm", "state_osm", "description_osm", "description_it_osm", "website_osm", "wikimedia_commons_osm",
            "maintenance_osm", "maintenance_it_osm", "note_osm", "note_it_osm", "note_project_page_osm",
        ];
        foreach ($props as $prop) {
            $r->$prop = $prop;
        }
        $props_float = [
            "ascent", "descent", "distance", "duration_forward", "duration_backward",
            "ascent_osm", "descent_osm", "distance_osm", "duration_forward_osm", "duration_backward_osm",
            "ascent_comp", "descent_comp", "distance_comp", "duration_forward_comp", "duration_backward_comp",
        ];
        foreach ($props_float as $prop) {
            $r->$prop = 1;
        }
        $r->save();

        $post_data = [
            "osm2cai_status" => 1,
            "lo0" => 0,
            "la0" => 0,
            "lo1" => 5,
            "la1" => 5
        ];
        $result = $this->postJson('/api/geojson/hiking_route/bounding_box', $post_data);
        $geojson = json_decode($result->getContent());
        $this->assertEquals(1, count($geojson->features));
        $hr = $geojson->features[0];

        // Check Structure
        $this->assertTrue(isset($hr->type));
        $this->assertTrue(isset($hr->geometry));
        $this->assertTrue(isset($hr->properties));
        $this->assertEquals('Feature', $hr->type);
        $this->assertEquals('LineString', $hr->geometry->type);
        $this->assertIsArray($hr->geometry->coordinates);

        // Check Main Data
        $this->assertEquals($r->id, $hr->properties->id);
        $this->assertEquals(2, count($hr->geometry->coordinates));
        $this->assertEquals(1, $hr->geometry->coordinates[0][0]);
        $this->assertEquals(1, $hr->geometry->coordinates[0][1]);
        $this->assertEquals(2, $hr->geometry->coordinates[1][0]);
        $this->assertEquals(2, $hr->geometry->coordinates[1][1]);
        $this->assertTrue(isset($hr->properties->created_at));
        $this->assertTrue(isset($hr->properties->updated_at));
        $this->assertEquals(1, $hr->properties->osm2cai_status);
        $this->assertEquals('2021-01-01', $hr->properties->validation_date);
        $this->assertEquals(1234, $hr->properties->relation_id);


        // Check Other Data (properties)
        foreach ($props as $prop) {
            $this->assertEquals($prop, $hr->properties->$prop);
        }
        foreach ($props_float as $prop) {
            $this->assertEquals(1, $hr->properties->$prop);
        }

    }

    public function testOsmGeomInBBButDifferentStatusReturnsEmptyCollection()
    {
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();
        $r = $t->getHikingRouteWithOsmGeometry([[1, 1], [2, 2]]);
        $r->osm2cai_status = 1;
        $r->save();

        $post_data = [
            "osm2cai_status" => 2,
            "lo0" => 0,
            "la0" => 0,
            "lo1" => 5,
            "la1" => 5
        ];

        $result = $this->postJson('/api/geojson/hiking_route/bounding_box', $post_data);
        $geojson = json_decode($result->getContent());
        $this->assertEquals(0, count($geojson->features));
    }

    public function testOsmGeomPartiallyInBBReturnsAFeatureCollectionWithOneHikingRoute()
    {
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();
        $r = $t->getHikingRouteWithOsmGeometry([[1, 1], [6, 6]]);
        $r->osm2cai_status = 1;
        $r->save();

        $post_data = [
            "osm2cai_status" => 1,
            "lo0" => 0,
            "la0" => 0,
            "lo1" => 5,
            "la1" => 5
        ];

        // Check Results
        $result = $this->postJson('/api/geojson/hiking_route/bounding_box', $post_data);
        $geojson = json_decode($result->getContent());
        $this->assertEquals(1, count($geojson->features));
        $hr = $geojson->features[0];
        $this->assertEquals($r->id, $hr->properties->id);

    }

    public function testOsmGeomOutBBReturnsEmptyFeatureCollection()
    {
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();
        $r = $t->getHikingRouteWithOsmGeometry([[6, 6], [7, 7]]);
        $r->osm2cai_status = 1;
        $r->save();

        $post_data = [
            "osm2cai_status" => 1,
            "lo0" => 0,
            "la0" => 0,
            "lo1" => 5,
            "la1" => 5
        ];

        $result = $this->postJson('/api/geojson/hiking_route/bounding_box', $post_data);
        $geojson = json_decode($result->getContent());
        $this->assertEquals(0, count($geojson->features));
    }

    /**
     * CAI Geometry -> status 4
     */

    public function testCaiGeomInBBReturnsFeatureCollectionWithOneHikingRoute()
    {
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();
        $r = $t->getHikingRouteWithCaiGeometry([[1, 1], [2, 2]]);
        $r->osm2cai_status = 4;
        $r->save();

        $post_data = [
            "osm2cai_status" => 4,
            "lo0" => 0,
            "la0" => 0,
            "lo1" => 5,
            "la1" => 5
        ];

        // Check Results
        $result = $this->postJson('/api/geojson/hiking_route/bounding_box', $post_data);
        $geojson = json_decode($result->getContent());
        $this->assertEquals(1, count($geojson->features));
        $hr = $geojson->features[0];
        $this->assertEquals($r->id, $hr->properties->id);

    }

    public function testCaiGeomInBBButDifferentStatusReturnsEmptyCollection()
    {
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();
        $r = $t->getHikingRouteWithCaiGeometry([[1, 1], [2, 2]]);
        $r->osm2cai_status = 4;
        $r->save();

        $post_data = [
            "osm2cai_status" => 1,
            "lo0" => 0,
            "la0" => 0,
            "lo1" => 5,
            "la1" => 5
        ];

        $result = $this->postJson('/api/geojson/hiking_route/bounding_box', $post_data);
        $geojson = json_decode($result->getContent());
        $this->assertEquals(0, count($geojson->features));


    }

    public function testCaiGeomPartiallyInBBReturnsFeatureCollectionWithOneHikingRoute()
    {
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();
        $r = $t->getHikingRouteWithCaiGeometry([[1, 1], [6, 6]]);
        $r->osm2cai_status = 4;
        $r->save();

        $post_data = [
            "osm2cai_status" => 4,
            "lo0" => 0,
            "la0" => 0,
            "lo1" => 5,
            "la1" => 5
        ];

        // Check Results
        $result = $this->postJson('/api/geojson/hiking_route/bounding_box', $post_data);
        $geojson = json_decode($result->getContent());
        $this->assertEquals(1, count($geojson->features));
        $hr = $geojson->features[0];
        $this->assertEquals($r->id, $hr->properties->id);

    }

    public function testCaiGeomOutBBReturnsEmptyFeatureCollection()
    {
        HikingRoute::truncate();
        $t = TerritorialUnitsFixtures::getInstance();
        $r = $t->getHikingRouteWithCaiGeometry([[6, 6], [7, 7]]);
        $r->osm2cai_status = 4;
        $r->save();

        $post_data = [
            "osm2cai_status" => 1,
            "lo0" => 0,
            "la0" => 0,
            "lo1" => 5,
            "la1" => 5
        ];

        $result = $this->postJson('/api/geojson/hiking_route/bounding_box', $post_data);
        $geojson = json_decode($result->getContent());
        $this->assertEquals(0, count($geojson->features));

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

        // With status 4 returns FeatureCollection With One Hiking Route
        $post_data = [
            "osm2cai_status" => 4,
            "lo0" => 0,
            "la0" => 0,
            "lo1" => 5,
            "la1" => 5
        ];

        $result = $this->postJson('/api/geojson/hiking_route/bounding_box', $post_data);
        $geojson = json_decode($result->getContent());
        $this->assertEquals(1, count($geojson->features));
        $hr = $geojson->features[0];
        $this->assertEquals($r->id, $hr->properties->id);


        // With status 3 returns Empty FeatureCollection
        $post_data = [
            "osm2cai_status" => 3,
            "lo0" => 0,
            "la0" => 0,
            "lo1" => 5,
            "la1" => 5
        ];
        $result = $this->postJson('/api/geojson/hiking_route/bounding_box', $post_data);
        $geojson = json_decode($result->getContent());
        $this->assertEquals(0, count($geojson->features));

    }


}
