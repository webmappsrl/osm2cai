<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Sector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SectorsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function after_updating_sector_full_code_is_recalculated_if_area_is_change(){
        $area1 = Area::factory()->create(['name'=>'GXDE']);
        $area2 = Area::factory()->create(['name'=>'XDER']);
        $lastFullCode = 'X'.$area1->name;
        $sector = Sector::factory(['full_code'=>$lastFullCode,'code'=>'X','area_id'=>$area1->id])->create();
        $sector->area_id = $area2->id;
        $sector->save();
        $sector->refresh();
        $newFullCode = $area2->name.'X';
        $this->assertEquals($sector->full_code,$newFullCode);
    }

}
