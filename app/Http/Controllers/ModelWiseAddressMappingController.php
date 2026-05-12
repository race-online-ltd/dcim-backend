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
                'model_wise_address_mapping.model_id',
                'ups_models.name        as model_name',
                'ups_models.protocol    as model_protocol',
                DB::raw('GROUP_CONCAT(register_addresses.name SEPARATOR ", ") as address_name'),
                DB::raw('MIN(model_wise_address_mapping.id) as id'),
                DB::raw('MAX(model_wise_address_mapping.created_at) as created_at'),
                DB::raw('MAX(model_wise_address_mapping.updated_at) as updated_at'),
            ])
            ->groupBy('model_wise_address_mapping.model_id', 'ups_models.name', 'ups_models.protocol');

        // Optional filters
        if ($request->filled('model_id')) {
            $query->where('model_wise_address_mapping.model_id', $request->integer('model_id'));
        }

        // Note: Filter by address_id might be less effective in grouped results, but keeping for compatibility
        if ($request->filled('address_id')) {
            $query->where('model_wise_address_mapping.address_id', $request->integer('address_id'));
        }

        $mappings = $query->orderBy('id', 'desc')->get();

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
            'model_id'    => 'required|integer|exists:ups_models,id',
            'address_ids' => 'required|array',
            'address_ids.*' => [
                'integer',
                'exists:register_addresses,id',
            ],
        ]);

        $createdCount = 0;
        foreach ($validated['address_ids'] as $addressId) {
            // Only create if it doesn't exist already to prevent 500 errors from unique constraint if it exists
            $exists = ModelWiseAddressMapping::where('model_id', $validated['model_id'])
                ->where('address_id', $addressId)
                ->exists();

            if (!$exists) {
                ModelWiseAddressMapping::create([
                    'model_id'   => $validated['model_id'],
                    'address_id' => $addressId,
                ]);
                $createdCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully created $createdCount mappings.",
        ], 201);
    }

    /**
     * GET /api/model-wise-address-mappings/{id}
     * Show a single mapping group.
     */
    public function show(int $id): JsonResponse
    {
        $mapping = ModelWiseAddressMapping::find($id);

        if (!$mapping) {
            return response()->json([
                'success' => false,
                'message' => 'Mapping not found.',
            ], 404);
        }

        $addressIds = ModelWiseAddressMapping::where('model_id', $mapping->model_id)
            ->pluck('address_id')
            ->toArray();

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $mapping->id,
                'model_id'    => $mapping->model_id,
                'address_ids' => $addressIds,
                'address_id'  => $mapping->address_id, // keep for compatibility
            ],
        ]);
    }

    /**
     * PUT/PATCH /api/model-wise-address-mappings/{id}
     * Update mappings for a model.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $mapping = ModelWiseAddressMapping::find($id);

        if (!$mapping) {
            return response()->json([
                'success' => false,
                'message' => 'Mapping not found.',
            ], 404);
        }

        $validated = $request->validate([
            'model_id'      => 'required|integer|exists:ups_models,id',
            'address_ids'   => 'required|array',
            'address_ids.*' => 'integer|exists:register_addresses,id',
        ]);

        // Sync logic: delete existing mappings for the model and create new ones
        ModelWiseAddressMapping::where('model_id', $mapping->model_id)->delete();

        foreach ($validated['address_ids'] as $addressId) {
            ModelWiseAddressMapping::create([
                'model_id'   => $validated['model_id'],
                'address_id' => $addressId,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mapping updated successfully.',
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

        // Delete all mappings for this model since they are shown as a single row in the UI
        ModelWiseAddressMapping::where('model_id', $mapping->model_id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Model mappings deleted successfully.',
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