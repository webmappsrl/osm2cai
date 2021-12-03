<?php

namespace Tests\Unit;

use App\Models\HikingRoute;
use PHPUnit\Framework\TestCase;

/*
 * 0: cai_scale null, source null
 * 1: cai_scale not null, source null
 * 2: cai_scale null, source contains "survey:CAI"
 * 3: cai_scale not null, source contains "survey:CAI"
 * 4: validation_date not_null
 */

class HikingRouteSetOms2CaiStatusTest extends TestCase
{
    /**
     * @test
     */
    public function when_is_validated_then_status_is_4()
    {
        $h = new HikingRoute();
        $h->validation_date = date('Y-m-d');
        $h->setOsm2CaiStatus();
        $this->assertEquals(4, $h->osm2cai_status);
    }

    /**
     * @test
     */
    public function when_is_nothing_then_status_is_0()
    {
        $h = new HikingRoute();
        $h->setOsm2CaiStatus();
        $this->assertEquals(0, $h->osm2cai_status);
    }

    /**
     * @test
     */
    public function when_has_cai_scale_and_no_source_then_status_is_1()
    {
        $h = new HikingRoute();
        $h->cai_scale_osm = 'E';
        $h->setOsm2CaiStatus();
        $this->assertEquals(1, $h->osm2cai_status);
    }

    /**
     * @test
     */
    public function when_has_cai_scale_and_source_does_not_contains_survey_CAI_then_status_is_1()
    {
        $h = new HikingRoute();
        $h->cai_scale_osm = 'E';
        $h->source_osm = 'XXX';
        $h->setOsm2CaiStatus();
        $this->assertEquals(1, $h->osm2cai_status);
    }

    /**
     * @test
     */
    public function when_has_cai_scale_and_source_is_survey_CAI_then_status_is_3()
    {
        $h = new HikingRoute();
        $h->cai_scale_osm = 'E';
        $h->source_osm = 'survey:CAI';
        $h->setOsm2CaiStatus();
        $this->assertEquals(3, $h->osm2cai_status);
    }

    /**
     * @test
     */
    public function when_has_cai_scale_and_source_contains_survey_CAI_then_status_is_3()
    {
        $h = new HikingRoute();
        $h->cai_scale_osm = 'E';
        $h->source_osm = 'Regione Liguria,survey:CAI';
        $h->setOsm2CaiStatus();
        $this->assertEquals(3, $h->osm2cai_status);
    }

    /**
     * @test
     */
    public function when_has_no_cai_scale_and_source_is_survey_CAI_then_status_is_2()
    {
        $h = new HikingRoute();
        $h->source_osm = 'survey:CAI';
        $h->setOsm2CaiStatus();
        $this->assertEquals(2, $h->osm2cai_status);
    }

    /**
     * @test
     */
    public function when_has_no_cai_scale_and_source_contains_survey_CAI_then_status_is_2()
    {
        $h = new HikingRoute();
        $h->source_osm = 'Regione Liguria,survey:CAI';
        $h->setOsm2CaiStatus();
        $this->assertEquals(2, $h->osm2cai_status);
    }

}
