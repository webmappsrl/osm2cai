<?php

namespace Tests\Unit;

use App\Models\HikingRoute;
use PHPUnit\Framework\TestCase;

class HikingRouteValidationFlowTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testNotValidated()
    {
        $r = new HikingRoute();
        $this->assertFalse($r->validated());
    }

    public function testValidated()
    {
        $r = new HikingRoute();
        $r->validation_date='2021-01-01';
        $this->assertTrue($r->validated());
    }

    public function testOsm2CaiStatusIs4(){
        $r = new HikingRoute();
        $r->source_osm = 'survey:CAI';
        $r->cai_scale_osm = 'E';
        $r->validation_date = '2021-01-01';
        $r->setOsm2CaiStatus();
        $this->assertEquals(4,$r->osm2cai_status);
    }

    public function testOsm2CaiStatusIs3(){
        $r = new HikingRoute();
        $r->source_osm = 'survey:CAI';
        $r->cai_scale_osm = 'E';
        $r->setOsm2CaiStatus();
        $this->assertEquals(3,$r->osm2cai_status);
    }

    public function testOsm2CaiStatusIs2(){
        $r = new HikingRoute();
        $r->source_osm = 'survey:CAI';
        $r->setOsm2CaiStatus();
        $this->assertEquals(2,$r->osm2cai_status);
    }

    public function testOsm2CaiStatusIs1(){
        $r = new HikingRoute();
        $r->cai_scale_osm = 'E';
        $r->setOsm2CaiStatus();
        $this->assertEquals(1,$r->osm2cai_status);
    }

    public function testOsm2CaiStatusIs0(){
        $r = new HikingRoute();
        $r->setOsm2CaiStatus();
        $this->assertEquals(0,$r->osm2cai_status);
    }


}
