<?php

namespace Tests\Feature\api;

use App\Models\Section;
use Tests\TestCase;
use App\Models\User;
use GeoJson\Geometry\MultiPolygon;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MiturAbruzzoSectionByIdApiV2Test extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $section;

    protected function setUp(): void
    {

        parent::setUp();
        $line = new MultiPolygon([[[[1, 1], [1, 2], [2, 2], [2, 1], [1, 1]]]]);
        $res = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . json_encode($line->jsonSerialize()) . '\') as geom'));
        $this->user = User::where('email', 'team@webmapp.it')->first();
        $this->section = Section::create([
            'name' => 'SectionTest',
            'cai_code' => '1',
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
            ->get('/api/v2/mitur_abruzzo/section/' . $this->section->id)
            ->assertStatus(200);
    }

    /**
     * @test
     * 
     * It returns a 404 code if the section is not present
     * 
     * @return void
     */
    public function test_it_returns_a_404_code_if_the_section_does_not_exist()
    {
        $response = $this->actingAs($this->user, 'api')
            ->get('/api/v2/mitur_abruzzo/section/999999')
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
            ->get('/api/v2/mitur_abruzzo/section/' . $this->section->id)
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
