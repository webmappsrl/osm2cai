<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Region;
use App\Models\Province;
use App\Models\Area;
use App\Models\Sector;


class SectorsIdsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Check sectorsIds on regions
     */
    public function testRegionSectorsIds()
    {
        $region = Region::factory()
            ->has(Province::factory()->has(
                Area::factory()
                    ->has(
                        Sector::factory()->count(2)
                    )
                    ->count(2)
            )->count(2))
            ->create();
        $this->assertCount(8, $region->sectorsIds());
    }

    /**
     * Check sectorsIds on provinces
     */
    public function testProvinceSectorsIds()
    {
        Region::factory()
            ->has(Province::factory()->has(
                Area::factory()
                    ->has(
                        Sector::factory()->count(2)
                    )
                    ->count(2)
            ))
            ->create();
        $province = Province::first();
        $this->assertCount(4, $province->sectorsIds());
    }

    /**
     * Check sectorsIds on areas
     */
    public function testAreaSectorsIds()
    {
        Region::factory()
            ->has(Province::factory()->has(
                Area::factory()
                    ->has(
                        Sector::factory()->count(2)
                    )
            ))
            ->create();
        $area = Area::first();
        $this->assertCount(2, $area->sectorsIds());
    }

    /**
     * Check sectorsIds on sectors
     */
    public function testSectorSectorsIds()
    {
        Region::factory()
            ->has(Province::factory()->has(
                Area::factory()
                    ->has(
                        Sector::factory()
                    )
            ))
            ->create();
        $sector = Sector::first();
        $this->assertCount(1, $sector->sectorsIds());
    }
}
