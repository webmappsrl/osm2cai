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

class GeojsonGenerationTest extends TestCase {
    use RefreshDatabase;

    public function testRegionShapefileGeneration() {
        $region = Region::factory()->has(
            Province::factory()->has(
                Area::factory()->has(
                    Sector::factory()->count(2)
                )->count(2)
            )->count(2)
        )->create()->first();

        $result = $this->get(route('api.geojson.region', ['id' => $region->id]));
        $geojson = $result->json();
        $this->assertIsArray($geojson);
        $this->assertArrayHasKey('type', $geojson);
        $this->assertSame('FeatureCollection', $geojson['type']);
        $this->assertArrayHasKey('properties', $geojson);
        $this->assertIsArray($geojson['properties']);
        $this->assertArrayHasKey('id', $geojson['properties']);
        $this->assertArrayHasKey('name', $geojson['properties']);
        $this->assertArrayHasKey('code', $geojson['properties']);
        $this->assertArrayHasKey('full_code', $geojson['properties']);
        $this->assertArrayHasKey('geojson_url', $geojson['properties']);
        $this->assertArrayHasKey('shapefile_url', $geojson['properties']);
        $this->assertSame($region->id, $geojson['properties']['id']);
        $this->assertSame($region->name, $geojson['properties']['name']);
        $this->assertSame($region->code, $geojson['properties']['code']);
        $this->assertSame($region->code, $geojson['properties']['full_code']);
        $this->assertSame(route('api.geojson.region', ['id' => $region->id]), $geojson['properties']['geojson_url']);
        $this->assertSame(route('api.shapefile.region', ['id' => $region->id]), $geojson['properties']['shapefile_url']);
        $this->assertArrayHasKey('features', $geojson);
        $this->assertIsArray($geojson['features']);
        $this->assertCount(count($region->sectorsIds()), $geojson['features']);
    }

    public function testProvinceShapefileGeneration() {
        Region::factory()->has(
            Province::factory()->has(
                Area::factory()->has(
                    Sector::factory()->count(2)
                )->count(2)
            )->count(1)
        )->create()->first();

        $province = Province::first();

        $result = $this->get(route('api.geojson.province', ['id' => $province->id]));
        $geojson = $result->json();
        $this->assertIsArray($geojson);
        $this->assertArrayHasKey('type', $geojson);
        $this->assertSame('FeatureCollection', $geojson['type']);
        $this->assertArrayHasKey('properties', $geojson);
        $this->assertIsArray($geojson['properties']);
        $this->assertArrayHasKey('id', $geojson['properties']);
        $this->assertArrayHasKey('name', $geojson['properties']);
        $this->assertArrayHasKey('code', $geojson['properties']);
        $this->assertArrayHasKey('full_code', $geojson['properties']);
        $this->assertArrayHasKey('geojson_url', $geojson['properties']);
        $this->assertArrayHasKey('shapefile_url', $geojson['properties']);
        $this->assertSame($province->id, $geojson['properties']['id']);
        $this->assertSame($province->name, $geojson['properties']['name']);
        $this->assertSame($province->code, $geojson['properties']['code']);
        $this->assertSame($province->full_code, $geojson['properties']['full_code']);
        $this->assertSame(route('api.geojson.province', ['id' => $province->id]), $geojson['properties']['geojson_url']);
        $this->assertSame(route('api.shapefile.province', ['id' => $province->id]), $geojson['properties']['shapefile_url']);
        $this->assertArrayHasKey('features', $geojson);
        $this->assertIsArray($geojson['features']);
        $this->assertCount(count($province->sectorsIds()), $geojson['features']);
    }

    public function testAreaShapefileGeneration() {
        Region::factory()->has(
            Province::factory()->has(
                Area::factory()->has(
                    Sector::factory()->count(2)
                )->count(1)
            )->count(1)
        )->create()->first();

        $area = Area::first();

        $result = $this->get(route('api.geojson.area', ['id' => $area->id]));
        $geojson = $result->json();
        $this->assertIsArray($geojson);
        $this->assertArrayHasKey('type', $geojson);
        $this->assertSame('FeatureCollection', $geojson['type']);
        $this->assertArrayHasKey('properties', $geojson);
        $this->assertIsArray($geojson['properties']);
        $this->assertArrayHasKey('id', $geojson['properties']);
        $this->assertArrayHasKey('name', $geojson['properties']);
        $this->assertArrayHasKey('code', $geojson['properties']);
        $this->assertArrayHasKey('full_code', $geojson['properties']);
        $this->assertArrayHasKey('geojson_url', $geojson['properties']);
        $this->assertArrayHasKey('shapefile_url', $geojson['properties']);
        $this->assertSame($area->id, $geojson['properties']['id']);
        $this->assertSame($area->name, $geojson['properties']['name']);
        $this->assertSame($area->code, $geojson['properties']['code']);
        $this->assertSame($area->full_code, $geojson['properties']['full_code']);
        $this->assertSame(route('api.geojson.area', ['id' => $area->id]), $geojson['properties']['geojson_url']);
        $this->assertSame(route('api.shapefile.area', ['id' => $area->id]), $geojson['properties']['shapefile_url']);
        $this->assertArrayHasKey('features', $geojson);
        $this->assertIsArray($geojson['features']);
        $this->assertCount(count($area->sectorsIds()), $geojson['features']);
    }

    public function testSectorShapefileGeneration() {
        Region::factory()->has(
            Province::factory()->has(
                Area::factory()->has(
                    Sector::factory()->count(1)
                )->count(1)
            )->count(1)
        )->create()->first();

        $sector = Sector::first();

        $result = $this->get(route('api.geojson.sector', ['id' => $sector->id]));
        $geojson = $result->json();
        $this->assertIsArray($geojson);
        $this->assertArrayHasKey('type', $geojson);
        $this->assertSame('Feature', $geojson['type']);
        $this->assertArrayHasKey('properties', $geojson);
        $this->assertIsArray($geojson['properties']);
        $this->assertArrayHasKey('id', $geojson['properties']);
        $this->assertArrayHasKey('name', $geojson['properties']);
        $this->assertArrayHasKey('code', $geojson['properties']);
        $this->assertArrayHasKey('full_code', $geojson['properties']);
        $this->assertArrayHasKey('geojson_url', $geojson['properties']);
        $this->assertArrayHasKey('shapefile_url', $geojson['properties']);
        $this->assertSame($sector->id, $geojson['properties']['id']);
        $this->assertSame($sector->name, $geojson['properties']['name']);
        $this->assertSame($sector->code, $geojson['properties']['code']);
        $this->assertSame($sector->full_code, $geojson['properties']['full_code']);
        $this->assertSame(route('api.geojson.sector', ['id' => $sector->id]), $geojson['properties']['geojson_url']);
        $this->assertSame(route('api.shapefile.sector', ['id' => $sector->id]), $geojson['properties']['shapefile_url']);
        $this->assertArrayHasKey('geometry', $geojson);
        $this->assertIsArray($geojson['geometry']);
        $this->assertArrayHasKey('type', $geojson['geometry']);
        $this->assertArrayHasKey('coordinates', $geojson['geometry']);
        $this->assertIsString($geojson['geometry']['type']);
        $this->assertIsArray($geojson['geometry']['coordinates']);
    }
}
