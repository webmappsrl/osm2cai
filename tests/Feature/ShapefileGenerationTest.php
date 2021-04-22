<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Province;
use App\Models\Region;
use App\Models\Sector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShapefileGenerationTest extends TestCase {
    use RefreshDatabase;

    public function testRegionShapefileGeneration() {
        Storage::disk('public')->deleteDirectory('shape_files');
        $region = Region::factory()->has(
            Province::factory()->has(
                Area::factory()->has(
                    Sector::factory()->count(2)
                )->count(2)
            )->count(2)
        )->create()->first();

        $shapefileLocation = $region->getShapefile();

        $this->assertTrue(Storage::disk('public')->exists($shapefileLocation));
    }

    public function testProvinceShapefileGeneration() {
        Storage::disk('public')->deleteDirectory('shape_files');
        Region::factory()->has(
            Province::factory()->has(
                Area::factory()->has(
                    Sector::factory()->count(2)
                )->count(2)
            )->count(1)
        )->create()->first();

        $province = Province::first();
        $shapefileLocation = $province->getShapefile();

        $this->assertTrue(Storage::disk('public')->exists($shapefileLocation));
    }

    public function testAreaShapefileGeneration() {
        Storage::disk('public')->deleteDirectory('shape_files');
        Region::factory()->has(
            Province::factory()->has(
                Area::factory()->has(
                    Sector::factory()->count(2)
                )->count(1)
            )->count(1)
        )->create()->first();

        $area = Area::first();
        $shapefileLocation = $area->getShapefile();

        $this->assertTrue(Storage::disk('public')->exists($shapefileLocation));
    }

    public function testSectorShapefileGeneration() {
        Storage::disk('public')->deleteDirectory('shape_files');
        Region::factory()->has(
            Province::factory()->has(
                Area::factory()->has(
                    Sector::factory()->count(1)
                )->count(1)
            )->count(1)
        )->create()->first();

        $sector = Sector::first();
        $shapefileLocation = $sector->getShapefile();

        $this->assertTrue(Storage::disk('public')->exists($shapefileLocation));
    }
}
