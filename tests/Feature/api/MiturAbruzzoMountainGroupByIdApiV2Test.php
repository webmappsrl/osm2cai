<?php

namespace Tests\Feature\api;

use Tests\TestCase;
use App\Models\User;
use App\Models\MountainGroups;
use GeoJson\Geometry\MultiPolygon;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;

class MiturAbruzzoMountainGroupByIdApiV2Test extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $mountainGroup;

    protected function setUp(): void
    {

        parent::setUp();
        $line = new MultiPolygon([[[[1, 1], [1, 2], [2, 2], [2, 1], [1, 1]]]]);
        $res = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . json_encode($line->jsonSerialize()) . '\') as geom'));
        $this->user = User::where('email', 'team@webmapp.it')->first();
        $this->mountainGroup = MountainGroups::create([
            'name' => 'Mountain group Test',
            'geometry' => $res[0]->geom
        ]);

        Artisan::call('osm2cai:cache-mitur-abruzzo-api', ['model' => 'MountainGroups', 'id' => $this->mountainGroup->id]);
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
            ->get('/api/v2/mitur_abruzzo/mountain_group/' . $this->mountainGroup->id)
            ->assertStatus(200);
    }

    /**
     * @test
     * 
     * It returns a 404 code if the mountain group is not present
     * 
     * @return void
     */
    public function test_it_returns_a_404_code_if_the_mountain_group_does_not_exist()
    {
        $response = $this->actingAs($this->user, 'api')
            ->get('/api/v2/mitur_abruzzo/mountain_group/999999')
            ->assertStatus(404);
    }

    /**
     * @test
     * 
     * It has the correct json structure
     * 
     * @return void
     */
    public function test_it_has_the_correct_json_structure()
    {
        $response = $this->actingAs($this->user, 'api')
            ->get('/api/v2/mitur_abruzzo/mountain_group/' . $this->mountainGroup->id)
            ->assertJsonStructure([
                'type',
                'properties' => [
                    'id',
                    'name',
                    'description',
                    'activity',
                    'disclaimer',
                    'area',
                    'ele_min',
                    'ele_max',
                    'ele_avg',
                    'ele_stddev',
                    'slope_min',
                    'slope_max',
                    'slope_avg',
                    'slope_stddev',
                    'region',
                    'provinces',
                    'municipalities',
                    'protected_area',
                    'section_ids',
                    'hiking_routes',
                    'ec_pois',
                    'cai_huts',
                    'map',
                    'hiking_routes_map',
                    'images',
                ],
                'geometry',
            ]);
    }
}
