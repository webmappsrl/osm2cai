<?php

namespace Tests\Feature\api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Region;
use GeoJson\Geometry\MultiPolygon;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MiturAbruzzoRegionByIdApiV2Test extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $region;

    protected function setUp(): void
    {

        parent::setUp();
        $line = new MultiPolygon([[[[1, 1], [1, 2], [2, 2], [2, 1], [1, 1]]]]);
        $res = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . json_encode($line->jsonSerialize()) . '\') as geom'));
        $this->user = User::where('email', 'team@webmapp.it')->first();
        $this->region = Region::create([
            'name' => 'regionTest',
            'geometry' => $res[0]->geom,
            'code' => '1',
            'num_expected' => 1
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
            ->get('/api/v2/mitur_abruzzo/region/' . $this->region->id)
            ->assertStatus(200);
    }

    /**
     * @test
     * 
     * It returns a 404 code if the region is not present
     * 
     * @return void
     */
    public function test_it_returns_a_404_code_if_the_region_does_not_exist()
    {
        $response = $this->actingAs($this->user, 'api')
            ->get('/api/v2/mitur_abruzzo/region/999999')
            ->assertStatus(404);
    }

    /**
     * @test
     * 
     * It has the correct structure
     * 
     * @return void
     */
    public function test_it_has_the_correct_structure()
    {
        $response = $this->actingAs($this->user, 'api')
            ->get('/api/v2/mitur_abruzzo/region/' . $this->region->id)
            ->assertJsonStructure([
                'type',
                'properties' => [
                    'id',
                    'name',
                    'mountain_groups',
                ],
                'geometry'
            ]);
    }
}
