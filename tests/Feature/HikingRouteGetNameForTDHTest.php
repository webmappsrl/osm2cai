<?php

namespace Tests\Feature;

use App\Models\HikingRoute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Service;
use Mockery\MockInterface;

class HikingRouteGetNameForTDHTest extends TestCase
{
    /**
     * method exists
     * @test
     */
    public function method_getNameForTDH_exists() {
        $hr = new HikingRoute();
        $this->assertTrue(method_exists($hr,'getNameForTDH'));
    }


    /**
     * The name is not translated (it,en,es,de,fr,pt)
     * @test
     *
     * @return void
     */
    public function with_name_it_returns_name_with_languages() {
        $hr = HikingRoute::factory()->create(['name'=>'Sentiero della Speranza']);
        $name = $hr->getNameForTDH();
        $this->assertEquals('Sentiero della Speranza',$name['it']);
        $this->assertEquals('Sentiero della Speranza',$name['en']);
        $this->assertEquals('Sentiero della Speranza',$name['es']);
        $this->assertEquals('Sentiero della Speranza',$name['de']);
        $this->assertEquals('Sentiero della Speranza',$name['fr']);
        $this->assertEquals('Sentiero della Speranza',$name['pt']);
    } 


    /**
     * Name built with ref (it,en,es,de,fr,pt)
     * @test
     *
     * @return void
     */
    public function without_name_and_with_ref_it_returns_name_ref_with_languages() {
        $hr = HikingRoute::factory()->create(['name'=>null,'ref'=>'135']);
        $name = $hr->getNameForTDH();
        $this->assertEquals('Sentiero 135',$name['it']);
        $this->assertEquals('Path 135',$name['en']);
        $this->assertEquals('Camino 135',$name['es']);
        $this->assertEquals('Weg 135',$name['de']);
        $this->assertEquals('Chemin 135',$name['fr']);
        $this->assertEquals('Caminho 135',$name['pt']);
    } 


    /**
     * Name built with from (it,en,es,de,fr,pt)
     * @test
     *
     * @return void
     */
    public function without_name_and_without_ref_it_returns_name_with_languages() {
        $hr = HikingRoute::factory()->create(['name'=>null,'ref'=>null]);
        $name = $hr->getNameForTDH();

        $this->assertEquals('Sentiero del Comune di Sconosciuto',$name['it']);
        $this->assertEquals('Path in the municipality of Sconosciuto',$name['en']);
        $this->assertEquals('Camino en el municipio de Sconosciuto',$name['es']);
        $this->assertEquals('Weg in der Gemeinde Sconosciuto',$name['de']);
        $this->assertEquals('Chemin dans la municipalité de Sconosciuto',$name['fr']);
        $this->assertEquals('Caminho no município de Sconosciuto',$name['pt']);
    } 
    
}
