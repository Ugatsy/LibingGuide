<?php

namespace Database\Factories;

use App\Models\PathNode;
use Illuminate\Database\Eloquent\Factories\Factory;

class PathNodeFactory extends Factory
{
    protected $model = PathNode::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'label' => fake()->word(),
            'lat' => fake()->latitude(16.52, 16.53),
            'lng' => fake()->longitude(121.18, 121.20),
            'type' => fake()->randomElement(['entrance', 'waypoint', 'facility', 'section']),
        ];
    }
}
