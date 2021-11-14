<?php

namespace Tests\Unit;

use App\Helpers\Osm2CaiHelper;
use PHPUnit\Framework\TestCase;


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
    public function with_sda_1_color_is_F7CA16()
    {
        $this->assertEquals('#F7CA16', Osm2CaiHelper::getSdaColor(1));
    }

    /**
     * @test
     */
    public function with_sda_2_color_is_F7A117()
    {
        $this->assertEquals('#F7A117', Osm2CaiHelper::getSdaColor(2));
    }

    /**
     * @test
     */
    public function with_sda_3_color_is_F36E45()
    {
        $this->assertEquals('#F36E45', Osm2CaiHelper::getSdaColor(3));
    }

    /**
     * @test
     */
    public function with_sda_4_color_is_47AC34()
    {
        $this->assertEquals('#47AC34', Osm2CaiHelper::getSdaColor(4));
    }
}
