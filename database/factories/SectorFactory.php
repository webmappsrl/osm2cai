<?php

namespace Database\Factories;

use App\Models\Sector;
use App\Models\Area;
use GeoJson\Geometry\Polygon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use MStaack\LaravelPostgis\Geometries\Point;

class SectorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sector::class;


    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $coords = [[[0, 0], [0, 2], [1, 1], [0, 0]]];
        $poly = new Polygon($coords);
        $res = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . json_encode($poly->jsonSerialize()) . '\') as geom'));

        return [
            'name' => $this->faker->name(),
            'geometry' => $res[0]->geom,
            'code' => $this->faker->lexify('?'),
            'full_code' => $this->faker->lexify('?????'),
            'area_id' => Area::factory()
        ];
    }
}
