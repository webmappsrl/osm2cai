<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\HikingRoute;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class HikingRoutesApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test GET /api/v1/hiking-route/{id}
     *
     * @return void
     */
    public function testGetHikingRouteById()
    {
        $hikingRoute = HikingRoute::find(15576);

        $response = $this->get('/api/v1/hiking-route/' . $hikingRoute->id);

        $response->assertStatus(200);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'type',
                'properties' => [
                    'id',
                    'relation_id',
                    'source',
                    'cai_scale',
                    'from',
                    'to',
                    'ref',
                    'public_page',
                    'sda',
                    'validation_date'
                ],
                'geometry' => [
                    'type',
                    'coordinates'
                ]
            ]);
    }

    /**
     * Test GET /api/v1/hiking-route-osm/{id}
     *
     * @return void
     */
    public function testGetHikingRouteByOsmId()
    {
        $hikingRoute = HikingRoute::find(15576);

        $response = $this->get('/api/v1/hiking-route-osm/' . $hikingRoute->relation_id);

        $response->assertStatus(200);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'type',
                'properties' => [
                    'id',
                    'relation_id',
                    'source',
                    'cai_scale',
                    'from',
                    'to',
                    'ref',
                    'public_page',
                    'sda',
                    'validation_date'
                ],
                'geometry' => [
                    'type',
                    'coordinates'
                ]
            ]);
    }

    /**
     * Test GET /api/v2/hiking-routes/{id}
     *
     * @return void
     */
    public function testGetHikingRouteByIdV2()
    {
        $hikingRoute = HikingRoute::find(15576);

        $response = $this->get('/api/v2/hiking-route/' . $hikingRoute->id);

        $response->assertStatus(200);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'type',
                'properties' => [
                    'id',
                    'relation_id',
                    'source',
                    'cai_scale',
                    'from',
                    'to',
                    'ref',
                    'public_page',
                    'sda',
                    'validation_date'
                ],
                'geometry' => [
                    'type',
                    'coordinates'
                ]
            ]);
    }

    /**
     * Test GET /api/v2/hiking-route-osm/{id}
     *
     * @return void
     */
    public function testGetHikingRouteByOsmIdV2()
    {
        $hikingRoute = HikingRoute::find(15576);

        $response = $this->get('/api/v2/hiking-route-osm/' . $hikingRoute->relation_id);

        $response->assertStatus(200);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'type',
                'properties' => [
                    'id',
                    'relation_id',
                    'source',
                    'cai_scale',
                    'from',
                    'to',
                    'ref',
                    'public_page',
                    'sda',
                    'validation_date'
                ],
                'geometry' => [
                    'type',
                    'coordinates'
                ]
            ]);
    }

    /**
     * Test GET /api/v2/hiking-route-tdh/{id}
     * 
     * @return void
     */
    public function testGetHikingRouteTdhByIdV2()
    {
        $hikingRoute = HikingRoute::find(15576);

        $response = $this->get('/api/v2/hiking-route-tdh/' . $hikingRoute->id);

        $response->assertStatus(200);

        $response->assertStatus(200)
            ->assertJsonStructure([
                "data" => [
                    'type',
                    'properties' => [
                        'id',
                        'created_at',
                        'updated_at',
                        'osm2cai_status',
                        'validation_date',
                        'relation_id',
                        'ref',
                        'ref_REI',
                        'gpx_url',
                        'cai_scale',
                        'cai_scale_string',
                        'cai_scale_description',
                        'survey_date',
                        'from',
                        'city_from',
                        'city_from_istat',
                        'region_from',
                        'region_from_istat',
                        'to',
                        'city_to',
                        'city_to_istat',
                        'region_to',
                        'region_to_istat',
                        'name',
                        'roundtrip',
                        'abstract',
                        'distance',
                        'ascent',
                        'descent',
                        'duration_forward',
                        'duration_backward',
                        'ele_from',
                        'ele_to',
                        'ele_max',
                        'ele_min',
                        'issues_status',
                        'issues_last_update',
                        'issues_description'
                    ],
                    'geometry' => [
                        'type',
                        'coordinates'
                    ]
                ]

            ]);
    }
}
