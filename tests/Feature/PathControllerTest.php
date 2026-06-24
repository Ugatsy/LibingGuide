<?php

namespace Tests\Feature;

use App\Models\PathNode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PathControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_pathways_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('paths.index'));

        $response->assertStatus(200);
        $response->assertSee('Pathways');
    }

    public function test_admin_can_create_node(): void
    {
        $response = $this->actingAs($this->user)->post(route('paths.nodes.store'), [
            'lat' => 16.5253,
            'lng' => 121.1906,
            'name' => 'Main Entrance',
            'type' => 'entrance',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('path_nodes', [
            'name' => 'Main Entrance',
            'type' => 'entrance',
        ]);
    }

    public function test_admin_can_create_edge(): void
    {
        $a = PathNode::factory()->create(['lat' => 16.5250, 'lng' => 121.1900]);
        $b = PathNode::factory()->create(['lat' => 16.5260, 'lng' => 121.1910]);

        $response = $this->actingAs($this->user)->post(route('paths.edges.store'), [
            'from_node_id' => $a->id,
            'to_node_id' => $b->id,
            'weight' => 0.5,
            'path_type' => 'walkway',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('path_edges', [
            'from_node_id' => $a->id,
            'to_node_id' => $b->id,
        ]);
    }

    public function test_admin_can_find_path(): void
    {
        $a = PathNode::factory()->create(['lat' => 16.5250, 'lng' => 121.1900]);
        $b = PathNode::factory()->create(['lat' => 16.5260, 'lng' => 121.1910]);

        $this->actingAs($this->user)->post(route('paths.edges.store'), [
            'from_node_id' => $a->id, 'to_node_id' => $b->id, 'weight' => 0.5,
        ]);

        $response = $this->actingAs($this->user)->get(route('paths.find', [
            'start_id' => $a->id, 'end_id' => $b->id,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['path', 'nodeIds', 'distance']);
    }

    public function test_unauthenticated_user_cannot_access(): void
    {
        $response = $this->get(route('paths.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_delete_node(): void
    {
        $node = PathNode::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('paths.nodes.destroy', $node));

        $response->assertStatus(200);
        $this->assertModelMissing($node);
    }

    public function test_admin_can_export_data(): void
    {
        PathNode::factory()->create(['lat' => 16.5250, 'lng' => 121.1900]);
        PathNode::factory()->create(['lat' => 16.5260, 'lng' => 121.1910]);

        $response = $this->actingAs($this->user)->get(route('paths.export'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['nodes', 'edges']);
    }
}
