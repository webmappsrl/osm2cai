<?php

namespace Tests\Feature;

use App\Models\Region;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserGetTerritorialRoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function a_user_with_is_administrator_true_returns_admin()
    {
        $region = Region::factory()->create();
        $sector = Sector::factory()->create();

        $data = [
            'is_administrator' => true,
            'is_national_referent' => true,
            'region_id' => $region->id,
        ];
        $user = User::factory()->create($data);
        $user->sectors()->attach($sector->id);
        $this->assertEquals('admin', $user->getTerritorialRole());
    }

    /**
     * @test
     */
    public function a_user_with_is_is_national_referent_true_returns_national()
    {
        $region = Region::factory()->create();
        $sector = Sector::factory()->create();

        $data = [
            'is_administrator' => false,
            'is_national_referent' => true,
            'region_id' => $region->id,
        ];
        $user = User::factory()->create($data);
        $user->sectors()->attach($sector->id);
        $this->assertEquals('national', $user->getTerritorialRole());
    }

    /**
     * @test
     */
    public function a_user_with_region_id_not_null_returns_regional()
    {
        $region = Region::factory()->create();
        $sector = Sector::factory()->create();

        $data = [
            'is_administrator' => false,
            'is_national_referent' => false,
            'region_id' => $region->id,
        ];
        $user = User::factory()->create($data);
        $user->sectors()->attach($sector->id);
        $this->assertEquals('regional', $user->getTerritorialRole());
    }

    /**
     * @test
     */
    public function a_user_with_a_sector_attached_returns_local()
    {
        $sector = Sector::factory()->create();

        $data = [
            'is_administrator' => false,
            'is_national_referent' => false,
            'region_id' => null,
        ];
        $user = User::factory()->create($data);
        $user->sectors()->attach($sector->id);
        $this->assertEquals('local', $user->getTerritorialRole());
    }

    /**
     * @test
     */
    public function a_user_with_nothing_returns_unknown()
    {
        $data = [
            'is_administrator' => false,
            'is_national_referent' => false,
            'region_id' => null,
        ];
        $user = User::factory()->create($data);
        $this->assertEquals('unknown', $user->getTerritorialRole());
    }
}
