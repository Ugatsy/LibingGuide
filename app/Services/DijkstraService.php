<?php

namespace App\Services;

use App\Models\PathEdge;
use App\Models\PathNode;
use Illuminate\Support\Collection;

class DijkstraService
{
    public function findShortestPath(PathNode $start, PathNode $end): ?array
    {
        $edges = PathEdge::all();
        $graph = $this->buildAdjacencyList($edges);

        if (!isset($graph[$start->id]) || !isset($graph[$end->id])) {
            return null;
        }

        $distances = [];
        $previous = [];
        $unvisited = [];

        foreach ($graph as $nodeId => $neighbors) {
            $distances[$nodeId] = INF;
            $previous[$nodeId] = null;
            $unvisited[$nodeId] = true;
        }
        $distances[$start->id] = 0;

        while (!empty($unvisited)) {
            $current = $this->getMinDistanceNode($distances, $unvisited);
            if ($current === null) {
                break;
            }
            if ($current === $end->id || $distances[$current] === INF) {
                break;
            }
            unset($unvisited[$current]);

            foreach ($graph[$current] as $neighborId => $weight) {
                if (!isset($unvisited[$neighborId])) {
                    continue;
                }
                $alt = $distances[$current] + $weight;
                if ($alt < $distances[$neighborId]) {
                    $distances[$neighborId] = $alt;
                    $previous[$neighborId] = $current;
                }
            }
        }

        if ($distances[$end->id] === INF) {
            return null;
        }

        $pathIds = [];
        $current = $end->id;
        while ($current !== null) {
            array_unshift($pathIds, $current);
            $current = $previous[$current];
        }

        $unordered = PathNode::whereIn('id', $pathIds)->get()->keyBy('id');
        $pathNodes = collect();
        foreach ($pathIds as $id) {
            if ($unordered->has($id)) {
                $pathNodes->push($unordered->get($id));
            }
        }

        return [
            'path' => $pathNodes,
            'nodeIds' => $pathIds,
            'distance' => round($distances[$end->id], 4),
        ];
    }

    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function autoCalculateWeight(PathNode $from, PathNode $to): float
    {
        return $this->calculateDistance(
            $from->lat, $from->lng,
            $to->lat, $to->lng,
        );
    }

    private function buildAdjacencyList(Collection $edges): array
    {
        $graph = [];
        foreach ($edges as $edge) {
            $graph[$edge->from_node_id][$edge->to_node_id] = (float) $edge->weight;
            if ($edge->is_bidirectional) {
                $graph[$edge->to_node_id][$edge->from_node_id] = (float) $edge->weight;
            }
        }
        return $graph;
    }

    private function getMinDistanceNode(array $distances, array $unvisited): ?int
    {
        $minDist = INF;
        $minNode = null;
        foreach ($unvisited as $nodeId => $_) {
            if ($distances[$nodeId] < $minDist) {
                $minDist = $distances[$nodeId];
                $minNode = $nodeId;
            }
        }
        return $minNode;
    }
}
