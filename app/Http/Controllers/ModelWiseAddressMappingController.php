<?php

namespace App\Http\Controllers;

use App\Models\ModelWiseAddressMapping;
use App\Models\UpsModel;
use App\Models\RegisterAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ModelWiseAddressMappingController extends Controller
{
    /**
     * GET /api/model-wise-address-mappings
     * List all mappings with joined ups_models and register_addresses data.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ModelWiseAddressMapping::query()
            ->join('ups_models', 'model_wise_address_mapping.model_id', '=', 'ups_models.id')
            ->join('register_addresses', 'model_wise_address_mapping.address_id', '=', 'register_addresses.id')
            ->select([
                'model_wise_address_mapping.id',
                'model_wise_address_mapping.model_id',
                'model_wise_address_mapping.address_id',
                'model_wise_address_mapping.created_at',
                'model_wise_address_mapping.updated_at',
                'ups_models.name        as model_name',
                'ups_models.protocol    as model_protocol',
                'register_addresses.name as address_name',
            ]);

        // Optional filters
        if ($request->filled('model_id')) {
            $query->where('model_wise_address_mapping.model_id', $request->integer('model_id'));
        }

        if ($request->filled('address_id')) {
            $query->where('model_wise_address_mapping.address_id', $request->integer('address_id'));
        }

        $mappings = $query->orderBy('model_wise_address_mapping.id', 'desc')->get();

        return response()->json([
            'success' => true,
            'data'    => $mappings,
        ]);
    }

    /**
     * POST /api/model-wise-address-mappings
     * Create a new mapping.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'model_id'   => [
                'required',
                'integer',
                Rule::exists('ups_models', 'id'),
            ],
            'address_id' => [
                'required',
                'integer',
                Rule::exists('register_addresses', 'id'),
                // Prevent duplicate mapping for the same model+address pair
                Rule::unique('model_wise_address_mapping')->where(function ($q) use ($request) {
                    return $q->where('model_id', $request->model_id);
                }),
            ],
        ]);

        $mapping = ModelWiseAddressMapping::create($validated);

        // Return with joined data
        return response()->json([
            'success' => true,
            'message' => 'Mapping created successfully.',
            'data'    => $this->findWithJoin($mapping->id),
        ], 201);
    }

    /**
     * GET /api/model-wise-address-mappings/{id}
     * Show a single mapping with joined data.
     */
    public function show(int $id): JsonResponse
    {
        $mapping = $this->findWithJoin($id);

        if (! $mapping) {
            return response()->json([
                'success' => false,
                'message' => 'Mapping not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $mapping,
        ]);
    }

    /**
     * PUT/PATCH /api/model-wise-address-mappings/{id}
     * Update an existing mapping.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $mapping = ModelWiseAddressMapping::find($id);

        if (! $mapping) {
            return response()->json([
                'success' => false,
                'message' => 'Mapping not found.',
            ], 404);
        }

        $validated = $request->validate([
            'model_id'   => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('ups_models', 'id'),
            ],
            'address_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('register_addresses', 'id'),
                Rule::unique('model_wise_address_mapping')
                    ->where(function ($q) use ($request, $mapping) {
                        return $q->where('model_id', $request->model_id ?? $mapping->model_id);
                    })
                    ->ignore($mapping->id),
            ],
        ]);

        $mapping->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Mapping updated successfully.',
            'data'    => $this->findWithJoin($mapping->id),
        ]);
    }

    /**
     * DELETE /api/model-wise-address-mappings/{id}
     * Delete a mapping.
     */
    public function destroy(int $id): JsonResponse
    {
        $mapping = ModelWiseAddressMapping::find($id);

        if (! $mapping) {
            return response()->json([
                'success' => false,
                'message' => 'Mapping not found.',
            ], 404);
        }

        $mapping->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mapping deleted successfully.',
        ]);
    }

    /**
     * Internal helper — fetch a single row with joined columns.
     */
    private function findWithJoin(int $id): ?object
    {
        return DB::table('model_wise_address_mapping')
            ->join('ups_models', 'model_wise_address_mapping.model_id', '=', 'ups_models.id')
            ->join('register_addresses', 'model_wise_address_mapping.address_id', '=', 'register_addresses.id')
            ->select([
                'model_wise_address_mapping.id',
                'model_wise_address_mapping.model_id',
                'model_wise_address_mapping.address_id',
                'model_wise_address_mapping.created_at',
                'model_wise_address_mapping.updated_at',
                'ups_models.name        as model_name',
                'ups_models.protocol    as model_protocol',
                'register_addresses.name as address_name',
            ])
            ->where('model_wise_address_mapping.id', $id)
            ->first();
    }
}