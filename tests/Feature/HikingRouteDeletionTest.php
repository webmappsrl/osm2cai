<?php

namespace Tests\Feature;

use App\Models\HikingRoute;
use App\Models\User;
use App\Nova\Actions\DeleteHikingRouteAction;
use App\Nova\Actions\OsmSyncHikingRouteAction;
use App\Nova\Actions\RevertValidateHikingRouteAction;
use App\Nova\Actions\ValidateHikingRouteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Tests\TestCase;

class HikingRouteDeletionTest extends TestCase
{
    use RefreshDatabase;


    /**
     * @test
     */
    public function normal_users_cant_delete_hiking_route(){
        $user = User::factory(["is_administrator" => false,"is_national_referent" => false])->create();
        $this->actingAs($user);
        $hr = HikingRoute::factory()->create();
        $hr->deleted_on_osm = true;
        $hr->save();
        $action = new DeleteHikingRouteAction();
        $f = new ActionFields(Collection::make(),Collection::make());
        $res = $action->handle($f,Collection::make([0=>$hr]));
        $this->assertModelExists($hr);
    }

    /**
     * @test
     */
    public function administrator_and_national_referent_can_delete_hiking_route(){
        $user = User::factory(["is_administrator" => true,"is_national_referent" => true])->create();
        $this->actingAs($user);
        $hr = HikingRoute::factory()->create();
        $hr->deleted_on_osm = true;
        $hr->save();
        $action = new DeleteHikingRouteAction();
        $f = new ActionFields(Collection::make(),Collection::make());
        $res = $action->handle($f,Collection::make([0=>$hr]));
        $this->assertModelMissing($hr);
    }

    /**
     * @test
     */
    public function administrator_and_national_referent_cant_delete_hiking_route_when_delete_on_osm_is_false(){
        $user = User::factory(["is_administrator" => true,"is_national_referent" => true])->create();
        $this->actingAs($user);
        $hr = HikingRoute::factory()->create();
        $hr->deleted_on_osm = false;
        $hr->save();
        $action = new DeleteHikingRouteAction();
        $f = new ActionFields(Collection::make(),Collection::make());
        $res = $action->handle($f,Collection::make([0=>$hr]));
        $this->assertModelExists($hr);
    }

}
