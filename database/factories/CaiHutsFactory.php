<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CaiHutsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'unico_id' => $this->faker->unique()->randomNumber(8),
            'name' => $this->faker->name,
            'second_name' => $this->faker->name,
            'description' => $this->faker->text,
            'elevation' => $this->faker->randomNumber(4),
            'owner' => $this->faker->name,
            'geometry' => 'SRID=4326;POINT(7.858085 45.91364)',
        ];
    }
}
