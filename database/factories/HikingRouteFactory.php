<?php

namespace Database\Factories;

use App\Models\HikingRoute;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Polygon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use MStaack\LaravelPostgis\Geometries\Point;

class HikingRouteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = HikingRoute::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $line = new LineString([[0, 0], [1, 1]]);
        $res = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . json_encode($line->jsonSerialize()) . '\') as geom'));
        
        return [
            'relation_id' => $this->faker->numerify('#########'),
            'geometry' => $res[0]->geom

        ];
    }
}
