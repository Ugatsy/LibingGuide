<?php

namespace App\Http\Controllers;

use App\Models\PathEdge;
use App\Models\PathNode;
use App\Services\PathManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PathController extends Controller
{
    public function __construct(
        private PathManagerService $pathManager,
    ) {}

    public function index(): View
    {
        $graphData = $this->pathManager->getGraphData();
        $nodes = PathNode::orderBy('id')->get();
        return view('paths.index', compact('graphData', 'nodes'));
    }

    public function storeNode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'name' => 'nullable|string|max:255',
            'label' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:entrance,waypoint,facility,section',
        ]);

        $node = $this->pathManager->createNode($data);

        return response()->json($node, 201);
    }

    public function storeEdge(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from_node_id' => 'required|exists:path_nodes,id',
            'to_node_id' => 'required|exists:path_nodes,id|different:from_node_id',
            'weight' => 'nullable|numeric|min:0',
            'path_type' => 'nullable|string|in:walkway,road,stairs,ramp',
            'is_bidirectional' => 'nullable|boolean',
        ]);

        $edge = $this->pathManager->createEdge(
            $data['from_node_id'],
            $data['to_node_id'],
            $data['weight'] ?? null,
            $data['path_type'] ?? 'walkway',
            $data['is_bidirectional'] ?? true,
        );

        return response()->json($edge, 201);
    }

    public function findPath(Request $request): JsonResponse
    {
        $data = $request->validate([
            'start_id' => 'required|exists:path_nodes,id',
            'end_id' => 'required|exists:path_nodes,id|different:start_id',
        ]);

        $result = $this->pathManager->findPath($data['start_id'], $data['end_id']);

        if ($result === null) {
            return response()->json(['error' => 'No path found between the selected nodes.'], 404);
        }

        return response()->json($result);
    }

    public function destroyNode(PathNode $pathNode): JsonResponse
    {
        $this->pathManager->deleteNode($pathNode);
        return response()->json(['message' => 'Node deleted.']);
    }

    public function destroyEdge(PathEdge $pathEdge): JsonResponse
    {
        $this->pathManager->deleteEdge($pathEdge);
        return response()->json(['message' => 'Edge deleted.']);
    }

    public function export(): JsonResponse
    {
        return response()->json($this->pathManager->exportJson());
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'nodes' => 'required|array',
            'edges' => 'required|array',
        ]);

        $this->pathManager->importJson($request->only(['nodes', 'edges']));

        return response()->json(['message' => 'Data imported successfully.']);
    }

    public function reset(): JsonResponse
    {
        $this->pathManager->resetAll();
        return response()->json(['message' => 'All pathway data has been reset.']);
    }
}
