<?php

namespace Database\Factories;

use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;
use MStaack\LaravelPostgis\Geometries\Point;

class ProvinceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Province::class;

    private static $id = 1;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $id = self::$id;
        $code = chr($id) . chr($id);
        self::$id = self::$id + 1;
        return [
            'id' => $id,
            'name' => $this->faker->name,
            'geometry' => (new Point($this->faker->latitude, $this->faker->longitude))->toWKT(),
            'code' => $code,
            'full_code' => chr($id) . $code
        ];
    }
}
