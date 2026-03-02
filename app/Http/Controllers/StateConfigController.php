<?php

namespace App\Http\Controllers;

use App\Models\StateConfig;
use Illuminate\Http\Request;

class StateConfigController extends Controller
{
//    public function index()
//    {
//        $stateConfigs = StateConfig::with('sensor')->get();
//        return response()->json($stateConfigs);
//    }

    public function index()
    {

        return StateConfig::with(['sensor.device.dataCenter','sensor.sensorType'])
        ->orderBy('id', 'desc')
        ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sensor_id' => 'required|exists:sensor_lists,id',
            'path'=>'nullable|string|max:255',
            'value' => 'required|integer',
            'name' => 'required|string|max:255',
            'attache_sound' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:50',
            'sound' => 'nullable|boolean',
            'blink' => 'nullable|boolean',
        ]);

        $stateConfig = StateConfig::create($validated);
        return response()->json($stateConfig, 201);
    }

    public function show(StateConfig $stateConfig)
    {
        return response()->json($stateConfig->load([
            'sensor.device.dataCenter'
        ]));
    }

    public function update(Request $request, StateConfig $stateConfig)
    {
        $validated = $request->validate([
            'sensor_id' => 'required|exists:sensor_lists,id',
            'path'=>'nullable|string|max:255',
            'value' => 'required|integer',
            'name' => 'required|string|max:255',
            'attache_sound' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:50',
            'sound' => 'nullable',
            'blink' => 'nullable',
        ]);

        $stateConfig->update($validated);
        return response()->json($stateConfig);
    }

    public function destroy(StateConfig $stateConfig)
    {
        $stateConfig->delete();
        return response()->json(null, 204);
    }
}
