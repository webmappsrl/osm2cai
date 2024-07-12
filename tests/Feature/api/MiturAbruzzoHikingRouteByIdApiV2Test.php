<?php

namespace Tests\Feature\api;

use Tests\TestCase;
use App\Models\User;
use GeoJson\Geometry\LineString;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MiturAbruzzoHikingRouteByIdApiV2Test extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $hr;

    protected function setUp(): void
    {

        parent::setUp();
        $line = new LineString([[0, 0], [1, 1]]);
        $res = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . json_encode($line->jsonSerialize()) . '\') as geom'));
        $this->user = User::where('email', 'team@webmapp.it')->first();
        $this->hr = \App\Models\HikingRoute::create([
            'id' => 12,
            'relation_id' => 123445553,
            'osm2cai_status' => 4,
            'deleted_on_osm' => false,
            'region_favorite' => true,
            'issues_status' => 'sconosciuto',
            'geometry' => $res[0]->geom
        ]);
    }

    /**
     * @test
     * 
     * It returns a 200 status code
     * 
     * @return void
     */
    public function test_it_returns_a_200_status_code()
    {
        $response = $this->actingAs($this->user, 'api')
            ->get('/api/v2/mitur_abruzzo/hiking_route/' . $this->hr->id);
        $response->assertStatus(200);
    }

    /**
     * @test
     * 
     * It returns a 404 status code if the hikingroute is not found
     * 
     * @return void
     */
    public function test_it_returns_a_404_status_code_if_the_hikingroute_is_not_found()
    {
        $response = $this->actingAs($this->user, 'api')
            ->get('/api/v2/mitur_abruzzo/hiking_route/999999');
        $response->assertStatus(404);
    }

    /**
     * @test
     * 
     * It returns a correct json structure
     * 
     * @return void
     */
    public function test_it_creates_the_correct_json_structure()
    {
        $response = $this->actingAs($this->user, 'api')
            ->get('/api/v2/mitur_abruzzo/hiking_route/' . $this->hr->id);
        $response->assertJsonStructure([
            'type',
            'properties' => [
                'id',
                'ref',
                'name',
                'abstract',
                'info',
                'activity',
                'symbol',
                'cai_scale',
                'from',
                'to',
                'from:coordinate',
                'to:coordinate',
                'distance',
                'duration_forward',
                'duration_backward',
                'ele_max',
                'ele_min',
                'ele_from',
                'ele_to',
                'ascent',
                'descent',
                'difficulty',
                'issues_status',
                'section_ids',
                'cai_huts',
                'pois',
                'map',
                'gpx_url',
                'images',
            ],
            'geometry',
        ]);
    }
}
