<?php

namespace Tests\Unit;

use App\Helpers\Osm2CaiHelper;
use PHPUnit\Framework\TestCase;


/**
 * https://docs.google.com/document/d/10KL91mkn7H2IlHTaz_mpseLUWShMZePGqlvQsSko1U8/edit#heading=h.mz1do7ew6snu
 * 
 * 0 969696
 * 1 FFD23F
 * 2 B43E8F
 * 3 1E3888
 * 4 47AC34
 */

class Osm2CaiHelperGetSdaColorTest extends TestCase
{
    /**
     * @test
     */
    public function with_sda_0_color_is_969696()
    {
        $this->assertEquals('#969696', Osm2CaiHelper::getSdaColor(0));
    }

    /**
     * @test
     */
    public function with_sda_1_color_is_FFD23F()
    {
        $this->assertEquals('#FFD23F', Osm2CaiHelper::getSdaColor(1));
    }

    /**
     * @test
     */
    public function with_sda_2_color_is_B43E8F()
    {
        $this->assertEquals('#B43E8F', Osm2CaiHelper::getSdaColor(2));
    }

    /**
     * @test
     */
    public function with_sda_3_color_is_1E3888()
    {
        $this->assertEquals('#1E3888', Osm2CaiHelper::getSdaColor(3));
    }

    /**
     * @test
     */
    public function with_sda_4_color_is_47AC34()
    {
        $this->assertEquals('#47AC34', Osm2CaiHelper::getSdaColor(4));
    }
}
