<?php

// app/Http/Controllers/DiagramController.php
namespace App\Http\Controllers;

use App\Models\Edge;
use App\Models\Node;
use App\Models\Svg;
use Illuminate\Http\Request;
use App\Models\Diagram;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\ApiResponseTrait;

class DiagramController extends Controller
{

    use ApiResponseTrait;
    // DiagramController.php
//    public function store(Request $request)
//    {
//        try {
//            $validated = $request->validate([
//                'data_center_id' => 'required|exists:data_center_creations,id',
//                'name' => 'required|string|max:255',
//                'svg_content' => 'required|string',
//                'nodes' => 'nullable|json',
//                'edges' => 'nullable|json',
//            ]);
//
//            $diagram = Diagram::create($validated);
//
//            return response()->json([
//                'message' => 'Diagram saved successfully',
//                'diagram' => $diagram
//            ], 201);
//        } catch (\Illuminate\Validation\ValidationException $e) {
//            return response()->json([
//                'message' => 'Validation failed',
//                'errors' => $e->errors()
//            ], 422);
//        } catch (\Exception $e) {
//            return response()->json([
//                'message' => 'Failed to save diagram',
//                'error' => $e->getMessage()
//            ], 500);
//        }
//    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'data_center_id' => 'required|exists:data_center_creations,id',
                'name' => 'required|string|max:255',
                'svg_content' => 'required|string',
                'nodes' => 'nullable|json',
                'edges' => 'nullable|json',
            ]);

            // Create the diagram first
            $diagram = Diagram::create($validated);

            // Process nodes if they exist
            if (!empty($validated['nodes'])) {
                $nodes = json_decode($validated['nodes'], true);

                foreach ($nodes as $node) {
                    if (!isset($node['id'])) continue; // Prevent errors if ID is missing

                    $nodeType = $this->determineNodeType($node);

                    Node::create([
                        'diagram_id' => $diagram->id,
                        'data_center_id' => $validated['data_center_id'],
                        'node_id' => $node['id'],
                        'node_type' => $nodeType,
                        'position' => json_encode($node['position'] ?? null),
                        'data' => json_encode($node['data'] ?? null),
                        'style' => json_encode($node['style'] ?? null),
                    ]);
                }
            }

            // Process edges if they exist
            if (!empty($validated['edges'])) {
                $edges = json_decode($validated['edges'], true);

                foreach ($edges as $edge) {
                    if (!isset($edge['id']) || !isset($edge['source']) || !isset($edge['target'])) {
                        Log::warning("Skipping invalid edge during save: " . json_encode($edge));
                        continue;
                    }

                    Edge::create([
                        'diagram_id' => $diagram->id,
                        'data_center_id' => $validated['data_center_id'],
                        'edge_id' => $edge['id'],
                        'source' => $edge['source'],
                        'target' => $edge['target'],
                        'source_handle' => $edge['sourceHandle'] ?? null,
                        'target_handle' => $edge['targetHandle'] ?? null,
                        'type' => $edge['type'] ?? 'default',
                        'style' => json_encode($edge['style'] ?? null),
                        'marker_end' => json_encode($edge['markerEnd'] ?? null),
                        'data' => json_encode($edge['data'] ?? null),
                    ]);
                }
            }

            return response()->json([
                'message' => 'Diagram and associated elements saved successfully',
                'diagram' => $diagram
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Error saving diagram: " . $e->getMessage());
            return response()->json([
                'message' => 'Failed to save diagram',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    protected function determineNodeType(array $node): string
    {
        if (isset($node['type']) && $node['type'] === 'line') {
            return 'line';
        }

        if (isset($node['style']['borderRadius'])) {
            if ($node['style']['borderRadius'] === '50%') {
                return 'circle';
            }
            return 'rounded_rectangle';
        }

        if (isset($node['style']['width']) && isset($node['style']['height'])) {
            return 'rectangle';
        }

        if (isset($node['data']['label'])) {
            return 'text';
        }

        return 'unknown';
    }
    public function index($dataCenterId)
    {
        try {
            $diagrams = Diagram::where('data_center_id', $dataCenterId)
                ->orderBy('created_at', 'desc')
                ->get(); 

            $formattedDiagrams = $diagrams->map(function ($diagram) {
                return [
                    'id' => $diagram->id,
                    'data_center_id' => $diagram->data_center_id,
                    'name' => $diagram->name,
                    'created_at' => $diagram->created_at,
                    'updated_at' => $diagram->updated_at,
                    'svg_content' => $diagram->svg_content,
                ];
            })->values()->all();

            return response()->json([
                'success' => true,
                'diagrams' => $formattedDiagrams // Now this will be a proper JSON array
            ]);

        } catch (\Exception $e) {
            Log::error("Error fetching diagrams list for data center ID {$dataCenterId}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch diagrams for this data center.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Your existing show method (which is fine for single diagram fetch)
    // public function show($id)
    // {
    //     try {
    //         $diagram = Diagram::where('data_center_id', $id)->firstOrFail();


    //         return response()->json([
    //             'success' => true,
    //             'diagram' => [ // IMPORTANT: The key here is 'diagram' (singular object)
    //                 'id' => $diagram->id,
    //                 'data_center_id' => $diagram->data_center_id,
    //                 'name' => $diagram->name,
    //                 'nodes' => $diagram->nodes, // Already JSON string, frontend will parse
    //                 'edges' => $diagram->edges, // Already JSON string, frontend will parse
    //                 'svg_content' => $diagram->svg_content, // Full SVG content for ReactFlow rendering context
    //                 'created_at' => $diagram->created_at,
    //                 'updated_at' => $diagram->updated_at
    //             ]
    //         ], 200, [], JSON_UNESCAPED_SLASHES);

    //     } catch (ModelNotFoundException $e) {
    //         return response()->json(['success' => false, 'message' => 'Diagram not found'], 404);
    //     } catch (\Exception $e) {
    //         Log::error("Error fetching diagram ID {$id}: " . $e->getMessage());
    //         return response()->json(['success' => false, 'message' => 'Failed to fetch diagram', 'error' => $e->getMessage()], 500);
    //     }
    // }

    public function show($id)
    {

        try {
            $diagram = Svg::where('datacenter_id', $id)->first();
            return $this->successResponse($diagram, 'Success');

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request, Diagram $diagram)
    {
        $request->validate([
            'data_center_id' => 'required',
            'data_center_name' => 'required',
            'structure' => 'required|array',
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($diagram->image_path);
            $path = $request->file('image')->store('diagrams', 'public');
            $diagram->image_path = $path;
        }

        $diagram->update([
            'data_center_id' => $request->data_center_id,
            'data_center_name' => $request->data_center_name,
            'structure' => $request->structure,
        ]);

        return $diagram;
    }

    public function destroy(Diagram $diagram)
    {
        Storage::disk('public')->delete($diagram->image_path);
        $diagram->delete();
        return response()->noContent();
    }

}

