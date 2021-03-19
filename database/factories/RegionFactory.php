<?php

namespace Database\Factories;

use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use MStaack\LaravelPostgis\Geometries\Point;
use MStaack\LaravelPostgis\Geometries\Polygon;

class RegionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Region::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $result = Region::select(DB::raw('MAX(id) as max'))->first();
        $id = $result->max + 1;
        return [
            'id' => $id,
            'name' => $this->faker->name,
            'geometry' => (new Point($this->faker->latitude, $this->faker->longitude))->toWKT(),
            'code' => chr($id)
        ];
    }
}
