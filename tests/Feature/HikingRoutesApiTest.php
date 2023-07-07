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
        $hikingRoute = HikingRoute::factory()->create(['osm2cai_status' => 4]);
        $hikingRouteWithoutOsm2caiStatus = HikingRoute::factory()->create([]);

        $response = $this->get('/api/v1/hiking-route/' . $hikingRoute->id);
        $response2 = $this->get('/api/v1/hiking-route/' . $hikingRouteWithoutOsm2caiStatus->id);

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
                    'validation_date',
                    'issues_status',
                    'issues_last_update',
                    'issues_description'
                ],
                'geometry' => [
                    'type',
                    'coordinates'
                ]
            ]);

        //validatio_date field must not be in the geojson if osm2cai_status is not 4
        $response2->assertStatus(200)
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
                    'issues_status',
                    'issues_last_update',
                    'issues_description'
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
        $hikingRoute = HikingRoute::factory()->create(['osm2cai_status' => 4]);
        $hikingRouteWithoutOsm2caiStatus = HikingRoute::factory()->create([]);


        $response = $this->get('/api/v1/hiking-route-osm/' . $hikingRoute->relation_id);
        $response2 = $this->get('/api/v1/hiking-route/' . $hikingRouteWithoutOsm2caiStatus->id);


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
                    'validation_date',
                    'issues_status',
                    'issues_last_update',
                    'issues_description'
                ],
                'geometry' => [
                    'type',
                    'coordinates'
                ]
            ]);

        //validation_date field must not be in the geojson if osm2cai_status is not 4
        $response2->assertStatus(200)
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
                    'issues_status',
                    'issues_last_update',
                    'issues_description'
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
        $hikingRoute = HikingRoute::factory()->create(['osm2cai_status' => 4]);
        $hikingRouteWithoutOsm2caiStatus = HikingRoute::factory()->create([]);


        $response = $this->get('/api/v2/hiking-route/' . $hikingRoute->id);
        $response2 = $this->get('/api/v1/hiking-route/' . $hikingRouteWithoutOsm2caiStatus->id);


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
                    'validation_date',
                    'issues_status',
                    'issues_last_update',
                    'issues_description'
                ],
                'geometry' => [
                    'type',
                    'coordinates'
                ]
            ]);

        //validation_date field must not be in the geojson if osm2cai_status is not 4
        $response2->assertStatus(200)
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
                    'issues_status',
                    'issues_last_update',
                    'issues_description'
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
        $hikingRoute = HikingRoute::factory()->create(['osm2cai_status' => 4]);
        $hikingRouteWithoutOsm2caiStatus = HikingRoute::factory()->create([]);

        $response = $this->get('/api/v2/hiking-route-osm/' . $hikingRoute->relation_id);
        $response2 = $this->get('/api/v1/hiking-route/' . $hikingRouteWithoutOsm2caiStatus->id);

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
                    'validation_date',
                    'issues_status',
                    'issues_last_update',
                    'issues_description'

                ],
                'geometry' => [
                    'type',
                    'coordinates'
                ]
            ]);

        //validation_date field must not be in the geojson if osm2cai_status is not 4
        $response2->assertStatus(200)
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
                    'issues_status',
                    'issues_last_update',
                    'issues_description'
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
        $hikingRoute = HikingRoute::factory()->create([
            'osm2cai_status' => 4,
            'relation_id' => 1234567,
            'deleted_on_osm' => false,
            'geometry_check' => false,
            'geometry_sync' => false,
            'region_favorite' => false,
            'tdh' => [
                'gpx_url' => 'https://www.gpx.com',
                'cai_scale_string' => 'E',
                'cai_scale_description' => 'Escursionistico',
                'from' => 'From',
                'city_from' => 'City From',
                'city_from_istat' => '123456',
                'region_from' => 'Region From',
                'region_from_istat' => '123456',
                'to' => 'To',
                'city_to' => 'City To',
                'city_to_istat' => '123456',
                'region_to' => 'Region To',
                'region_to_istat' => '123456',
                'roundtrip' => false,
                'abstract' => 'Abstract',
                'distance' => 123,
                'ascent' => 123,
                'descent' => 123,
                'duration_forward' => 123,
                'duration_backward' => 123,
                'ele_from' => 123,
                'ele_to' => 123,
                'ele_max' => 123,
                'ele_min' => 123
            ]
        ]);
        $hikingRouteWithoutOsm2caiStatus = HikingRoute::factory()->create([
            'relation_id' => 1234567,
            'deleted_on_osm' => false,
            'geometry_check' => false,
            'geometry_sync' => false,
            'region_favorite' => false,
            'tdh' => [
                'gpx_url' => 'https://www.gpx.com',
                'cai_scale_string' => 'E',
                'cai_scale_description' => 'Escursionistico',
                'from' => 'From',
                'city_from' => 'City From',
                'city_from_istat' => '123456',
                'region_from' => 'Region From',
                'region_from_istat' => '123456',
                'to' => 'To',
                'city_to' => 'City To',
                'city_to_istat' => '123456',
                'region_to' => 'Region To',
                'region_to_istat' => '123456',
                'roundtrip' => false,
                'abstract' => 'Abstract',
                'distance' => 123,
                'ascent' => 123,
                'descent' => 123,
                'duration_forward' => 123,
                'duration_backward' => 123,
                'ele_from' => 123,
                'ele_to' => 123,
                'ele_max' => 123,
                'ele_min' => 123
            ]
        ]);
        $response = $this->get('/api/v2/hiking-route-tdh/' . $hikingRoute->id);
        $response2 = $this->get('/api/v2/hiking-route-tdh/' . $hikingRouteWithoutOsm2caiStatus->id);

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

        //validation_date field must not be in the geojson if osm2cai_status is not 4
        $response2->assertStatus(200)
            ->assertJsonStructure([
                "data" => [
                    'type',
                    'properties' => [
                        'id',
                        'created_at',
                        'updated_at',
                        'osm2cai_status',
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
