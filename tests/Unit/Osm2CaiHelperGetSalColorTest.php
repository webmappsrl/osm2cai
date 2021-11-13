<?php

namespace Tests\Unit;

use App\Helpers\Osm2CaiHelper;
use PHPUnit\Framework\TestCase;

class Osm2CaiHelperGetSalColorTest extends TestCase
{


    /**
     * @test
     */
    public function when_sal_is_between_0_and_0p2_color_is_f1eef6()
    {
        $this->assertEquals('#f1eef6', Osm2CaiHelper::getSalColor(0.1));
    }

    /**
     * @test
     */
    public function when_sal_is_between_0p2_and_0p4_color_is_bdc9e1()
    {
        $this->assertEquals('#bdc9e1', Osm2CaiHelper::getSalColor(0.3));
    }

    /**
     * @test
     */
    public function when_sal_is_between_0p4_and_0p6_color_is_74a9cf()
    {
        $this->assertEquals('#74a9cf', Osm2CaiHelper::getSalColor(0.5));
    }
    /**
     * 0.8 < SAL  -> #045a8d
     **/

    /**
     * @test
     */
    public function when_sal_is_between_0p6_and_0p8_color_is_2b8cbe()
    {
        $this->assertEquals('#2b8cbe', Osm2CaiHelper::getSalColor(0.7));
    }

    /**
     * @test
     */
    public function when_sal_is_greater_than_0p8_color_is_045a8d()
    {
        $this->assertEquals('#045a8d', Osm2CaiHelper::getSalColor(0.9));
    }


    /**
     * @test
     */
    public function when_sal_is_equal_to_0p2_color_is_f1eef6()
    {
        $this->assertEquals('#f1eef6', Osm2CaiHelper::getSalColor(0.2));
    }

    /**
     * @test
     */
    public function when_sal_is_equal_to_0p4_color_is_bdc9e1()
    {
        $this->assertEquals('#bdc9e1', Osm2CaiHelper::getSalColor(0.4));
    }

    /**
     * @test
     */
    public function when_sal_is_equal_to_0p6_color_is_74a9cf()
    {
        $this->assertEquals('#74a9cf', Osm2CaiHelper::getSalColor(0.6));
    }
    /**
     * 0.8 < SAL  -> #045a8d
     **/

    /**
     * @test
     */
    public function when_sal_is_equal_to_0p8_color_is_2b8cbe()
    {
        $this->assertEquals('#2b8cbe', Osm2CaiHelper::getSalColor(0.8));
    }


}
