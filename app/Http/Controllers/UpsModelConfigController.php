<?php

namespace App\Http\Controllers;

use App\Models\UpsModelConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpsModelConfigController extends Controller
{
    /**
     * GET /api/ups-model-config
     * List all configs with joined ups, ups_models, data_center_creations data.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DB::table('ups_model_config')
            ->join('ups',                   'ups_model_config.ups_id',        '=', 'ups.id')
            ->join('ups_models',            'ups_model_config.model_id',      '=', 'ups_models.id')
            ->join('data_center_creations', 'ups_model_config.datacenter_id', '=', 'data_center_creations.id')
            ->select([
                'ups_model_config.id',
                'ups_model_config.ups_id',
                'ups_model_config.model_id',
                'ups_model_config.datacenter_id',
                'ups_model_config.created_at',
                'ups_model_config.updated_at',

                // ups
                'ups.name       as ups_name',
                'ups.ip         as ups_ip',
                'ups.slave_id   as ups_slave_id',

                // ups_models
                'ups_models.name        as model_name',
                'ups_models.protocol    as model_protocol',

                // data_center_creations
                'data_center_creations.name      as datacenter_name',
                'data_center_creations.division  as datacenter_division',
                'data_center_creations.address   as datacenter_address',
                'data_center_creations.status    as datacenter_status',
            ]);

        // Optional filters
        if ($request->filled('ups_id')) {
            $query->where('ups_model_config.ups_id', $request->integer('ups_id'));
        }

        if ($request->filled('model_id')) {
            $query->where('ups_model_config.model_id', $request->integer('model_id'));
        }

        if ($request->filled('datacenter_id')) {
            $query->where('ups_model_config.datacenter_id', $request->integer('datacenter_id'));
        }

        $configs = $query->orderBy('ups_model_config.id', 'desc')->get();

        return response()->json([
            'success' => true,
            'data'    => $configs,
        ]);
    }

    /**
     * POST /api/ups-model-config
     * Create a new config.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ups_id' => [
                'required',
                'integer',
                Rule::exists('ups', 'id'),
            ],
            'model_id' => [
                'required',
                'integer',
                Rule::exists('ups_models', 'id'),
            ],
            'datacenter_id' => [
                'required',
                'integer',
                Rule::exists('data_center_creations', 'id'),
                // Prevent duplicate config for the same ups+model+datacenter combo
                Rule::unique('ups_model_config')->where(function ($q) use ($request) {
                    return $q
                        ->where('ups_id',   $request->ups_id)
                        ->where('model_id', $request->model_id);
                }),
            ],
        ]);

        $config = UpsModelConfig::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'UPS model config created successfully.',
            'data'    => $this->findWithJoin($config->id),
        ], 201);
    }

    /**
     * GET /api/ups-model-config/{id}
     * Show a single config with joined data.
     */
    public function show(int $id): JsonResponse
    {
        $config = $this->findWithJoin($id);

        if (! $config) {
            return response()->json([
                'success' => false,
                'message' => 'Config not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $config,
        ]);
    }

    /**
     * PUT /api/ups-model-config/{id}
     * Update an existing config.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $config = UpsModelConfig::find($id);

        if (! $config) {
            return response()->json([
                'success' => false,
                'message' => 'Config not found.',
            ], 404);
        }

        $validated = $request->validate([
            'ups_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('ups', 'id'),
            ],
            'model_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('ups_models', 'id'),
            ],
            'datacenter_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('data_center_creations', 'id'),
                Rule::unique('ups_model_config')
                    ->where(function ($q) use ($request, $config) {
                        return $q
                            ->where('ups_id',   $request->ups_id   ?? $config->ups_id)
                            ->where('model_id', $request->model_id ?? $config->model_id);
                    })
                    ->ignore($config->id),
            ],
        ]);

        $config->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'UPS model config updated successfully.',
            'data'    => $this->findWithJoin($config->id),
        ]);
    }

    /**
     * DELETE /api/ups-model-config/{id}
     * Delete a config.
     */
    public function destroy(int $id): JsonResponse
    {
        $config = UpsModelConfig::find($id);

        if (! $config) {
            return response()->json([
                'success' => false,
                'message' => 'Config not found.',
            ], 404);
        }

        $config->delete();

        return response()->json([
            'success' => true,
            'message' => 'UPS model config deleted successfully.',
        ]);
    }

    /**
     * Internal helper — fetch a single row with all joined columns.
     */
    private function findWithJoin(int $id): ?object
    {
        return DB::table('ups_model_config')
            ->join('ups',                   'ups_model_config.ups_id',        '=', 'ups.id')
            ->join('ups_models',            'ups_model_config.model_id',      '=', 'ups_models.id')
            ->join('data_center_creations', 'ups_model_config.datacenter_id', '=', 'data_center_creations.id')
            ->select([
                'ups_model_config.id',
                'ups_model_config.ups_id',
                'ups_model_config.model_id',
                'ups_model_config.datacenter_id',
                'ups_model_config.created_at',
                'ups_model_config.updated_at',

                // ups
                'ups.name       as ups_name',
                'ups.ip         as ups_ip',
                'ups.slave_id   as ups_slave_id',

                // ups_models
                'ups_models.name        as model_name',
                'ups_models.protocol    as model_protocol',

                // data_center_creations
                'data_center_creations.name      as datacenter_name',
                'data_center_creations.division  as datacenter_division',
                'data_center_creations.address   as datacenter_address',
                'data_center_creations.status    as datacenter_status',
            ])
            ->where('ups_model_config.id', $id)
            ->first();
    }
}