<?php

namespace App\Services;

use App\Models\PathEdge;
use App\Models\PathNode;
use Illuminate\Support\Collection;

class PathManagerService
{
    public function __construct(
        private DijkstraService $dijkstra,
    ) {}

    public function createNode(array $data): PathNode
    {
        return PathNode::create([
            'name' => $data['name'] ?? null,
            'label' => $data['label'] ?? null,
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'type' => $data['type'] ?? 'waypoint',
        ]);
    }

    public function createEdge(
        int $fromId,
        int $toId,
        ?float $weight = null,
        string $pathType = 'walkway',
        bool $bidirectional = true,
    ): PathEdge {
        if ($weight === null) {
            $from = PathNode::findOrFail($fromId);
            $to = PathNode::findOrFail($toId);
            $weight = $this->dijkstra->autoCalculateWeight($from, $to);
        }

        $edge = PathEdge::create([
            'from_node_id' => $fromId,
            'to_node_id' => $toId,
            'weight' => $weight,
            'path_type' => $pathType,
            'is_bidirectional' => $bidirectional,
        ]);

        return $edge->load('fromNode', 'toNode');
    }

    public function deleteNode(PathNode $node): void
    {
        $node->delete();
    }

    public function deleteEdge(PathEdge $edge): void
    {
        $edge->delete();
    }

    public function findPath(int $startId, int $endId): ?array
    {
        $start = PathNode::findOrFail($startId);
        $end = PathNode::findOrFail($endId);
        return $this->dijkstra->findShortestPath($start, $end);
    }

    public function getGraphData(): array
    {
        return [
            'nodes' => PathNode::orderBy('id')->get()->toArray(),
            'edges' => PathEdge::with('fromNode', 'toNode')->orderBy('id')->get()->toArray(),
        ];
    }

    public function exportJson(): array
    {
        return $this->getGraphData();
    }

    public function importJson(array $data): void
    {
        if (isset($data['nodes'])) {
            foreach ($data['nodes'] as $nodeData) {
                PathNode::updateOrCreate(
                    ['id' => $nodeData['id'] ?? null],
                    [
                        'name' => $nodeData['name'] ?? null,
                        'label' => $nodeData['label'] ?? null,
                        'lat' => $nodeData['lat'],
                        'lng' => $nodeData['lng'],
                        'type' => $nodeData['type'] ?? 'waypoint',
                    ]
                );
            }
        }

        if (isset($data['edges'])) {
            foreach ($data['edges'] as $edgeData) {
                PathEdge::updateOrCreate(
                    ['id' => $edgeData['id'] ?? null],
                    [
                        'from_node_id' => $edgeData['from_node_id'],
                        'to_node_id' => $edgeData['to_node_id'],
                        'weight' => $edgeData['weight'] ?? null,
                        'path_type' => $edgeData['path_type'] ?? 'walkway',
                        'is_bidirectional' => $edgeData['is_bidirectional'] ?? true,
                    ]
                );
            }
        }
    }

    public function resetAll(): void
    {
        PathEdge::truncate();
        PathNode::truncate();
    }
}
