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

class MiturAbruzzoRegionListApiV2Test extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $regions;

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
            ->get('/api/v2/mitur_abruzzo/region_list')
            ->assertStatus(200);
    }
}
