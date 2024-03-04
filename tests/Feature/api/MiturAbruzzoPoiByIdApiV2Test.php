<?php

namespace Tests\Feature\api;

use Tests\TestCase;
use App\Models\User;
use App\Models\EcPoi;
use GeoJson\Geometry\Point;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MiturAbruzzoPoiByIdApiV2Test extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $poi;

    protected function setUp(): void
    {

        parent::setUp();
        $line = new Point([42.5, 12.5]);
        $res = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . json_encode($line->jsonSerialize()) . '\') as geom'));
        $this->user = User::where('email', 'team@webmapp.it')->first();
        $this->poi = EcPoi::create([
            'name' => 'PoiTest',
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
            ->get('/api/v2/mitur_abruzzo/poi/' . $this->poi->id)
            ->assertStatus(200);
    }

    /**
     * @test
     * 
     * It returns a 404 code if the poi is not present
     * 
     * @return void
     */
    public function test_it_returns_a_404_code_if_the_poi_does_not_exist()
    {
        $response = $this->actingAs($this->user, 'api')
            ->get('/api/v2/mitur_abruzzo/poi/999999')
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
            ->get('/api/v2/mitur_abruzzo/poi/' . $this->poi->id)
            ->assertJsonStructure([
                'type',
                'properties' => [
                    'id',
                    'name',
                ],
                'geometry'
            ]);
    }
}
