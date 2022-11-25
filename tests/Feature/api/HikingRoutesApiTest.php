<?php


namespace Tests\Feature\api;

use App\Models\HikingRoute;
use App\Models\Region;
use GeoJson\Geometry\LineString;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\TerritorialUnitsFixtures;
use Tests\TestCase;

class HikingRoutesApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function hiking_routes_api_return_ids_of_selected_region_and_sda()
    {
       $regions = Region::factory(4)->create();
       $hikingRoutes = HikingRoute::factory(10)->create();
       $idsHiking = [];
       foreach ($hikingRoutes as $k=>$hr){
           if ($k % 2 == 0) {
               $hr->osm2cai_status = 4;
               $hr->save();
               $hr->regions()->sync([
                   $regions[0]->id
               ]);
               $idsHiking[] = $hr->id;
           }
           else {
               $hr->osm2cai_status = 3;
               $hr->regions()->sync([
                   $regions[1]->id
               ]);
               $hr->save();
           }
       }
       $response = $this->get(url('/').'/api/v1/hiking-routes/region/'.$regions[0]->code.'/4')
            ->assertStatus(200);
       $responseData = json_decode($response->content(),true);
       sort($responseData);
        sort($idsHiking);
       $this->assertEquals($responseData,$idsHiking);
    }

    /**
     * @test
     */
    public function hiking_routes_osm_api_return_ids_of_selected_region_and_sda()
    {
        $regions = Region::factory(4)->create();
        $hikingRoutes = HikingRoute::factory(10)->create();
        $idsHiking = [];
        foreach ($hikingRoutes as $k=>$hr){
            if ($k % 2 == 0) {
                $hr->osm2cai_status = 4;
                $hr->save();
                $hr->regions()->sync([
                    $regions[0]->id
                ]);
                $idsHiking[] = $hr->relation_id;
            }
            else {
                $hr->osm2cai_status = 3;
                $hr->regions()->sync([
                    $regions[1]->id
                ]);
                $hr->save();
            }
        }
        $response = $this->get(url('/').'/api/v1/hiking-routes-osm/region/'.$regions[0]->code.'/4')
            ->assertStatus(200);
        $responseData = json_decode($response->content(),true);
        sort($responseData);
        sort($idsHiking);
        $this->assertEquals($responseData,$idsHiking);
    }

    /**
     * @test
     */
    public function hiking_route_api_return_return_the_correct_values(){
        $hr = HikingRoute::factory(1)->create()->first();
        $hr->source = 'SOURCE';
        $hr->cai_scale = 'E';
        $hr->from = 'FROM';
        $hr->to = 'TO';
        $hr->ref = 'REF';
        $hr->osm2cai_status = 2;
        $hr->save();
        $response = $this->get(url('/').'/api/v1/hiking-route/'.$hr->id)
            ->assertStatus(200);
        $prop = json_decode($response->content(),true)['properties'];
        $this->assertEquals($hr->id,$prop['id']);
        $this->assertEquals($hr->source,$prop['source']);
        $this->assertEquals($hr->cai_scale,$prop['cai_scale']);
        $this->assertEquals($hr->from,$prop['from']);
        $this->assertEquals($hr->to,$prop['to']);
        $this->assertEquals($hr->ref,$prop['ref']);
        $this->assertEquals($hr->osm2cai_status,$prop['sda']);
        $this->assertEquals($hr->getPublicPage(),$prop['public_page']);

    }

    /**
     * @test
     */
    public function hiking_route_osm_api_return_return_the_correct_values(){
        $hr = HikingRoute::factory(1)->create()->first();
        $hr->source = 'SOURCE';
        $hr->cai_scale = 'E';
        $hr->from = 'FROM';
        $hr->to = 'TO';
        $hr->ref = 'REF';
        $hr->osm2cai_status = 2;
        $hr->save();
        $response = $this->get(url('/').'/api/v1/hiking-route-osm/'.$hr->relation_id)
            ->assertStatus(200);
        $prop = json_decode($response->content(),true)['properties'];
        $this->assertEquals($hr->id,$prop['id']);
        $this->assertEquals($hr->source,$prop['source']);
        $this->assertEquals($hr->cai_scale,$prop['cai_scale']);
        $this->assertEquals($hr->from,$prop['from']);
        $this->assertEquals($hr->to,$prop['to']);
        $this->assertEquals($hr->ref,$prop['ref']);
        $this->assertEquals($hr->osm2cai_status,$prop['sda']);
        $this->assertEquals($hr->getPublicPage(),$prop['public_page']);
    }

    /**
     * @test
     */
    public function hiking_routes_bb_api_return_ids_of_selected_bb_and_sda()
    {
        $bb_interno = new LineString([[10.37, 43.68], [10.6, 43.7]]);
        $res_interno = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . json_encode($bb_interno->jsonSerialize()) . '\') as geom'));
        $hr_interno = HikingRoute::factory()->create(['geometry' => $res_interno[0]->geom,'osm2cai_status'=>4]);
        $bb_esterno = new LineString([[10.2, 43.68], [10.2, 40.68]]);
        $res_esterno = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . json_encode($bb_esterno->jsonSerialize()) . '\') as geom'));
        $hr_esterno = HikingRoute::factory()->create(['geometry' => $res_esterno[0]->geom,'osm2cai_status'=>4]);
        $bb_montepisano = "10.363097,43.672057,10.638464,43.851693";
        $response = $this->get(url('/').'/api/v1/hiking-routes/bb/'.$bb_montepisano.'/4')
            ->assertStatus(200);
        $responseData = json_decode($response->content(),true)[0];
        $this->assertEquals($hr_interno->id,$responseData);
    }

    /**
     * @test
     */
    public function hiking_routes_osm_bb_api_return_ids_of_selected_bb_and_sda()
    {
        $bb_interno = new LineString([[10.37, 43.68], [10.6, 43.7]]);
        $res_interno = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . json_encode($bb_interno->jsonSerialize()) . '\') as geom'));
        $hr_interno = HikingRoute::factory()->create(['geometry' => $res_interno[0]->geom,'osm2cai_status'=>4]);
        $bb_esterno = new LineString([[10.2, 43.68], [10.2, 40.68]]);
        $res_esterno = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . json_encode($bb_esterno->jsonSerialize()) . '\') as geom'));
        $hr_esterno = HikingRoute::factory()->create(['geometry' => $res_esterno[0]->geom,'osm2cai_status'=>4]);
        $bb_montepisano = "10.363097,43.672057,10.638464,43.851693";
        $response = $this->get(url('/').'/api/v1/hiking-routes-osm/bb/'.$bb_montepisano.'/4')
            ->assertStatus(200);
        $responseData = json_decode($response->content(),true)[0];
        $this->assertEquals($hr_interno->id,$responseData);
    }


}
