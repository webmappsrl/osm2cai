<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\HikingRoute;
use App\Models\Province;
use App\Models\Region;
use App\Models\Sector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SallableTraitGetSalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function a_sector_with_one_single_route_with_sda_0_has_sal0()
    {
        $route = HikingRoute::factory()->create(['osm2cai_status' => 0]);
        $sector = Sector::factory()->create();
        $sector->hikingRoutes()->attach($route->id);
        $this->assertEquals(0, $sector->getSal());
    }

    /**
     * @test
     */
    public function a_sector_with_one_single_route_with_sda_1_has_sal_0p25()
    {
        $route = HikingRoute::factory()->create(['osm2cai_status' => 1]);
        $sector = Sector::factory()->create(['num_expected' => 1]);
        $sector->hikingRoutes()->attach($route->id);
        $this->assertEquals(0.25, $sector->getSal());
    }

    /**
     * @test
     */
    public function a_sector_with_one_single_route_with_sda_2_has_sal_0p50()
    {
        $route = HikingRoute::factory()->create(['osm2cai_status' => 2]);
        $sector = Sector::factory()->create(['num_expected' => 1]);
        $sector->hikingRoutes()->attach($route->id);
        $this->assertEquals(0.50, $sector->getSal());
    }

    /**
     * @test
     */
    public function a_sector_with_one_single_route_with_sda_3_has_sal_0p75()
    {
        $route = HikingRoute::factory()->create(['osm2cai_status' => 3]);
        $sector = Sector::factory()->create(['num_expected' => 1]);
        $sector->hikingRoutes()->attach($route->id);
        $this->assertEquals(0.75, $sector->getSal());
    }

    /**
     * @test
     */
    public function a_sector_with_one_single_route_with_sda_4_has_sal_1()
    {
        $route = HikingRoute::factory()->create(['osm2cai_status' => 4]);
        $sector = Sector::factory()->create(['num_expected' => 1]);
        $sector->hikingRoutes()->attach($route->id);
        $this->assertEquals(1.0, $sector->getSal());
    }

    /**
     * @test
     */
    public function an_area_with_one_single_route_with_sda_1_has_sal_0p25()
    {
        $route = HikingRoute::factory()->create(['osm2cai_status' => 1]);
        $area = Area::factory()->create(['num_expected' => 1]);
        $area->hikingRoutes()->attach($route->id);
        $this->assertEquals(0.25, $area->getSal());
    }

    /**
     * @test
     */
    public function a_province_with_one_single_route_with_sda_1_has_sal_0p25()
    {
        $route = HikingRoute::factory()->create(['osm2cai_status' => 1]);
        $province = Province::factory()->create(['num_expected' => 1]);
        $province->hikingRoutes()->attach($route->id);
        $this->assertEquals(0.25, $province->getSal());
    }

    /**
     * @test
     */
    public function a_region_with_one_single_route_with_sda_1_has_sal_0p25()
    {
        $route = HikingRoute::factory()->create(['osm2cai_status' => 1]);
        $region = Region::factory()->create(['num_expected' => 1]);
        $region->hikingRoutes()->attach($route->id);
        $this->assertEquals(0.25, $region->getSal());
    }


}
