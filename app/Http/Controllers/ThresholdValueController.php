<?php

namespace App\Http\Controllers;

use App\Models\ThresholdValue;
use Illuminate\Http\Request;

class ThresholdValueController extends Controller
{
//    public function index()
//    {
//        return response()->json(ThresholdValue::with(['sensor', 'thresholdType'])->get());
//    }

    public function index()
    {
        return response()->json(
            ThresholdValue::with([
                'sensor.device',
                'sensor.dataCenter', // ensure you use the correct relationship name
                'thresholdType',
                'sensor.sensorType'
            ])
            ->orderBy('id', 'desc')
            ->get()
        );
    }

    // Create new threshold value
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sensor_id' => 'required|exists:sensor_lists,id',
            'threshold_type_id' => 'required|exists:threshold_types,id',
            'threshold' => 'required|numeric',
            'timestamp' => 'nullable|date'
        ]);

        $thresholdValue = ThresholdValue::create($validated);
        return response()->json($thresholdValue->load(['sensor', 'thresholdType']), 201);
    }

    // Get single threshold value
    public function show(ThresholdValue $thresholdValue)
    {
        return response()->json($thresholdValue->load(['sensor', 'thresholdType']));
    }

    // Update threshold value
    public function update(Request $request, ThresholdValue $thresholdValue)
    {
        $validated = $request->validate([
            'sensor_id' => 'sometimes|exists:sensor_lists,id',
            'threshold_type_id' => 'sometimes|exists:threshold_types,id',
            'threshold' => 'sometimes|numeric',
            'timestamp' => 'nullable|date'
        ]);

        $thresholdValue->update($validated);
        return response()->json($thresholdValue->load(['sensor', 'thresholdType']));
    }

    // Delete threshold value
    public function destroy(ThresholdValue $thresholdValue)
    {
        $thresholdValue->delete();
        return response()->json(null, 204);
    }
}
