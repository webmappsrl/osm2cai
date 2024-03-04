<?php

namespace Tests\Feature\api;

use Tests\TestCase;
use App\Models\User;
use App\Models\CaiHuts;
use GeoJson\Geometry\Point;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MiturAbruzzoHutByIdApiV2Test extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $hut;

    protected function setUp(): void
    {

        parent::setUp();
        $line = new Point([1, 1]);
        $res = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . json_encode($line->jsonSerialize()) . '\') as geom'));
        $this->user = User::where('email', 'team@webmapp.it')->first();
        $this->hut = CaiHuts::create([
            'name' => 'Rifugio Test',
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
            ->get('/api/v2/mitur_abruzzo/hut/' . $this->hut->id)
            ->assertStatus(200);
    }

    /**
     * @test
     * 
     * It returns a 404 code if the hut is not present
     * 
     * @return void
     */
    public function test_it_returns_a_404_code_if_the_hut_does_not_exist()
    {
        $response = $this->actingAs($this->user, 'api')
            ->get('/api/v2/mitur_abruzzo/hut/999999')
            ->assertStatus(404);
    }

    /**
     * @test
     * 
     * It returns the correct json structure
     * 
     * @return void
     */
    public function test_it_returns_the_correct_json_structure()
    {
        $response = $this->actingAs($this->user, 'api')
            ->get('/api/v2/mitur_abruzzo/hut/' . $this->hut->id)
            ->assertJsonStructure([
                'type',
                'properties' => [
                    'id',
                    'name',
                ],
                'geometry',
            ]);
    }
}
