<?php

namespace App\Http\Controllers;

use App\Models\UpsModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpsModelController extends Controller
{
    // GET /ups-models
    public function index(): JsonResponse
    {
        $models = UpsModel::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'UPS model list fetched successfully.',
            'data'    => $models,
        ], 200);
    }

    // POST /ups-models
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'protocol' => 'required|string|max:255',
        ]);

        $model = UpsModel::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'UPS model created successfully.',
            'data'    => $model,
        ], 201);
    }

    // GET /ups-models/{id}
    public function show(int $id): JsonResponse
    {
        $model = UpsModel::find($id);

        if (!$model) {
            return response()->json([
                'success' => false,
                'message' => 'UPS model not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'UPS model fetched successfully.',
            'data'    => $model,
        ], 200);
    }

    // PUT /ups-models/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $model = UpsModel::find($id);

        if (!$model) {
            return response()->json([
                'success' => false,
                'message' => 'UPS model not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'protocol' => 'sometimes|required|string|max:255',
        ]);

        $model->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'UPS model updated successfully.',
            'data'    => $model->fresh(),
        ], 200);
    }

    // DELETE /ups-models/{id}
    public function destroy(int $id): JsonResponse
    {
        $model = UpsModel::find($id);

        if (!$model) {
            return response()->json([
                'success' => false,
                'message' => 'UPS model not found.',
            ], 404);
        }

        $model->delete();

        return response()->json([
            'success' => true,
            'message' => 'UPS model deleted successfully.',
        ], 200);
    }
}