<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Province;
use App\Models\Region;
use App\Models\Sector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SallableTraitGetViewTest extends TestCase
{
    /**
     * @test
     */
    public function with_regions_is_regions_view()
    {
        $this->assertEquals('regions_view', (new Region())->getView());
    }

    /**
     * @test
     */
    public function with_provinces_is_provinces_view()
    {
        $this->assertEquals('provinces_view', (new Province())->getView());
    }

    /**
     * @test
     */
    public function with_areas_is_areas_view()
    {
        $this->assertEquals('areas_view', (new Area())->getView());
    }

    /**
     * @test
     */
    public function with_sectors_is_sectors_view()
    {
        $this->assertEquals('sectors_view', (new Sector())->getView());
    }
}
