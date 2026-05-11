<?php

namespace App\Http\Controllers;

use App\Models\Ups;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpsController extends Controller
{
    // GET /ups
    public function index(): JsonResponse
    {
        $ups = Ups::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'UPS list fetched successfully.',
            'data'    => $ups,
        ], 200);
    }

    // POST /ups
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'ip'       => 'required|ip',
            'slave_id' => 'required|integer|min:0',
        ]);

        $ups = Ups::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'UPS created successfully.',
            'data'    => $ups,
        ], 201);
    }

    // GET /ups/{id}
    public function show(int $id): JsonResponse
    {
        $ups = Ups::find($id);

        if (!$ups) {
            return response()->json([
                'success' => false,
                'message' => 'UPS not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'UPS fetched successfully.',
            'data'    => $ups,
        ], 200);
    }

    // PUT /ups/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $ups = Ups::find($id);

        if (!$ups) {
            return response()->json([
                'success' => false,
                'message' => 'UPS not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'ip'       => 'sometimes|required|ip',
            'slave_id' => 'sometimes|required|integer|min:0',
        ]);

        $ups->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'UPS updated successfully.',
            'data'    => $ups->fresh(),
        ], 200);
    }

    // DELETE /ups/{id}
    public function destroy(int $id): JsonResponse
    {
        $ups = Ups::find($id);

        if (!$ups) {
            return response()->json([
                'success' => false,
                'message' => 'UPS not found.',
            ], 404);
        }

        $ups->delete();

        return response()->json([
            'success' => true,
            'message' => 'UPS deleted successfully.',
        ], 200);
    }
}