<?php

namespace Database\Factories;

use App\Models\PathEdge;
use App\Models\PathNode;
use Illuminate\Database\Eloquent\Factories\Factory;

class PathEdgeFactory extends Factory
{
    protected $model = PathEdge::class;

    public function definition(): array
    {
        return [
            'from_node_id' => PathNode::factory(),
            'to_node_id' => PathNode::factory(),
            'weight' => fake()->randomFloat(4, 0.01, 1),
            'path_type' => fake()->randomElement(['walkway', 'road', 'stairs', 'ramp']),
            'is_bidirectional' => true,
        ];
    }
}
