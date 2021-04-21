<?php

namespace Tests\Unit;

use App\Models\Area;
use App\Models\Province;
use App\Models\Region;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class UserSectorsTest extends TestCase {
    use RefreshDatabase;

    public function testWithNoSectors() {
        $user = User::factory([
            'region_id' => null
        ])->create();

        $sectors = $user->getSectors();

        $this->assertIsArray($sectors->toArray());
        $this->assertCount(0, $sectors);
    }

    public function testWithOnlyRegion() {
        Region::factory()->has(
            Province::factory()->has(
                Area::factory()->has(
                    Sector::factory()->count(2)
                )->count(2)
            )->count(2)
        )->has(User::factory()->count(1))
            ->create();

        $user = User::first();

        $sectors = $user->getSectors();

        $this->assertIsArray($sectors->toArray());
        $this->assertCount(8, $sectors);
    }

    public function testWithOnlyProvinces() {
        Region::factory()->has(
            Province::factory()->has(
                Area::factory()->has(
                    Sector::factory()->count(2)
                )->count(2)
            )->count(2)
        )->create();

        $user = User::factory()->create();
        $user->provinces()->attach(Province::all());

        $sectors = $user->getSectors();

        $this->assertIsArray($sectors->toArray());
        $this->assertCount(8, $sectors);
    }

    public function testWithOnlyAreas() {
        Region::factory()->has(
            Province::factory()->has(
                Area::factory()->has(
                    Sector::factory()->count(2)
                )->count(2)
            )->count(2)
        )->create();

        $user = User::factory()->create();
        $user->areas()->attach(Area::all());

        $sectors = $user->getSectors();

        $this->assertIsArray($sectors->toArray());
        $this->assertCount(8, $sectors);
    }

    public function testWithOnlySectors() {
        Region::factory()->has(
            Province::factory()->has(
                Area::factory()->has(
                    Sector::factory()->count(2)
                )->count(2)
            )->count(2)
        )->create();

        $user = User::factory()->create();
        $user->sectors()->attach(Sector::all());

        $sectors = $user->getSectors();

        $this->assertIsArray($sectors->toArray());
        $this->assertCount(8, $sectors);
    }

    public function testWithAll() {
        Region::factory()->has(
            Province::factory()->has(
                Area::factory()->has(
                    Sector::factory()->count(2)
                )->count(2)
            )->count(2)
        )->create();

        $user = User::factory()->create();
        $user->region()->associate(Region::first());
        $user->provinces()->attach(Province::first());
        $user->areas()->attach(Area::first());
        $user->sectors()->attach(Sector::first());

        $sectors = $user->getSectors();

        $this->assertIsArray($sectors->toArray());
        $this->assertCount(8, $sectors);
    }
}
