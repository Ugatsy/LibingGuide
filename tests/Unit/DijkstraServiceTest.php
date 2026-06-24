<?php

namespace Tests\Unit;

use App\Models\PathEdge;
use App\Models\PathNode;
use App\Services\DijkstraService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DijkstraServiceTest extends TestCase
{
    use RefreshDatabase;

    private DijkstraService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DijkstraService;
    }

    public function test_calculates_haversine_distance(): void
    {
        $distance = $this->service->calculateDistance(16.5253, 121.1906, 16.5260, 121.1910);

        $this->assertIsFloat($distance);
        $this->assertGreaterThan(0, $distance);
        $this->assertLessThan(1, $distance);
    }

    public function test_finds_shortest_path_in_simple_graph(): void
    {
        $a = PathNode::create(['lat' => 16.5250, 'lng' => 121.1900, 'type' => 'waypoint']);
        $b = PathNode::create(['lat' => 16.5260, 'lng' => 121.1910, 'type' => 'waypoint']);
        $c = PathNode::create(['lat' => 16.5270, 'lng' => 121.1920, 'type' => 'waypoint']);

        PathEdge::create(['from_node_id' => $a->id, 'to_node_id' => $b->id, 'weight' => 0.5, 'is_bidirectional' => true]);
        PathEdge::create(['from_node_id' => $b->id, 'to_node_id' => $c->id, 'weight' => 0.5, 'is_bidirectional' => true]);

        $result = $this->service->findShortestPath($a, $c);

        $this->assertNotNull($result);
        $this->assertCount(3, $result['path']);
        $this->assertEquals([$a->id, $b->id, $c->id], $result['nodeIds']);
        $this->assertEquals(1.0, $result['distance']);
    }

    public function test_returns_null_when_no_path_exists(): void
    {
        $a = PathNode::create(['lat' => 16.5250, 'lng' => 121.1900, 'type' => 'waypoint']);
        $b = PathNode::create(['lat' => 16.5260, 'lng' => 121.1910, 'type' => 'waypoint']);

        $result = $this->service->findShortestPath($a, $b);

        $this->assertNull($result);
    }

    public function test_finds_cheaper_path_when_multiple_routes_exist(): void
    {
        $a = PathNode::create(['lat' => 16.5250, 'lng' => 121.1900, 'type' => 'waypoint']);
        $b = PathNode::create(['lat' => 16.5260, 'lng' => 121.1910, 'type' => 'waypoint']);
        $c = PathNode::create(['lat' => 16.5270, 'lng' => 121.1920, 'type' => 'waypoint']);
        $d = PathNode::create(['lat' => 16.5280, 'lng' => 121.1930, 'type' => 'waypoint']);

        PathEdge::create(['from_node_id' => $a->id, 'to_node_id' => $b->id, 'weight' => 10, 'is_bidirectional' => true]);
        PathEdge::create(['from_node_id' => $b->id, 'to_node_id' => $d->id, 'weight' => 10, 'is_bidirectional' => true]);
        PathEdge::create(['from_node_id' => $a->id, 'to_node_id' => $c->id, 'weight' => 1, 'is_bidirectional' => true]);
        PathEdge::create(['from_node_id' => $c->id, 'to_node_id' => $d->id, 'weight' => 1, 'is_bidirectional' => true]);

        $result = $this->service->findShortestPath($a, $d);

        $this->assertNotNull($result);
        $this->assertEquals([$a->id, $c->id, $d->id], $result['nodeIds']);
        $this->assertEquals(2, $result['distance']);
    }

    public function test_handles_bidirectional_edges(): void
    {
        $a = PathNode::create(['lat' => 16.5250, 'lng' => 121.1900, 'type' => 'waypoint']);
        $b = PathNode::create(['lat' => 16.5260, 'lng' => 121.1910, 'type' => 'waypoint']);

        PathEdge::create(['from_node_id' => $a->id, 'to_node_id' => $b->id, 'weight' => 0.3, 'is_bidirectional' => true]);

        $resultAb = $this->service->findShortestPath($a, $b);
        $resultBa = $this->service->findShortestPath($b, $a);

        $this->assertNotNull($resultAb);
        $this->assertNotNull($resultBa);
        $this->assertEquals($resultAb['distance'], $resultBa['distance']);
    }

    public function test_auto_calculates_weight(): void
    {
        $a = PathNode::create(['lat' => 16.5250, 'lng' => 121.1900, 'type' => 'waypoint']);
        $b = PathNode::create(['lat' => 16.5260, 'lng' => 121.1910, 'type' => 'waypoint']);

        $weight = $this->service->autoCalculateWeight($a, $b);

        $this->assertIsFloat($weight);
        $this->assertGreaterThan(0, $weight);
    }
}
