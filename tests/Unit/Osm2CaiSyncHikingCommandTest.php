<?php

namespace Tests\Unit;

use App\Console\Commands\Osm2CaiSyncHikingRoutesCommand;
use App\Providers\Osm2CaiHikingRoutesServiceProvider;
use Tests\TestCase;

class Osm2CaiSyncHikingCommandTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testGetZoneL()
    {
        $r1 = new \stdClass();
        $r1->relation_id = 1;
        $r1->ref = '111';
        $r2 = new \stdClass();
        $r2->relation_id = 2;
        $r2->ref = '222';
        $routes = [$r1,$r2];
        $code='L';
        $serviceProviderMock = $this->mock(Osm2CaiHikingRoutesServiceProvider::class, function ($mock) use ($code, $routes) {
            $mock->shouldReceive('getHikingRoutes')
                ->once()
                ->with($code)
                ->andReturn($routes);
        });
        $cmd = new Osm2CaiSyncHikingRoutesCommand($serviceProviderMock);
        $routes_new = $cmd->getZone($code,$serviceProviderMock);
        $this->assertTrue(is_array($routes_new));
        $this->assertEquals(2,count($routes_new));
    }
}
