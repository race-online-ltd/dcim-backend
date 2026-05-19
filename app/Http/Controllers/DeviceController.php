<?php

namespace App\Http\Controllers;

use App\Models\DataCenterCreation;
use App\Models\DeviceList;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = DeviceList::with(['dataCenter', 'model'])->get();
        return response()->json($devices);
    }

    // Show single device
    public function show($id)
    {
        $device = DeviceList::with('dataCenter')->findOrFail($id);
        return response()->json($device);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'data_center_id' => 'required|exists:data_center_creations,id',
            'location' => 'required|string|max:255',
            'secret_key' => 'nullable|string|max:255',
            'control_topic' => 'nullable|string|max:255',
            'status' => 'required|integer|between:0,2',
            'ip' => 'nullable|string|max:255',
            'slave_id' => 'nullable|integer',
            'type' => 'integer|between:0,99',
            'model_id' => 'nullable|integer|exists:ups_models,id'
        ]);

        $device = DeviceList::create($validated);
        return response()->json($device, 201);
    }

    public function update(Request $request, $id)
    {
        $device = DeviceList::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'data_center_id' => 'sometimes|exists:data_center_creations,id',
            'location' => 'sometimes|string|max:255',
            'secret_key' => 'nullable|string|max:255',
            'control_topic' => 'nullable|string|max:255',
            'status' => 'sometimes|integer|between:0,2',
            'ip' => 'nullable|string|max:255',
            'slave_id' => 'nullable|integer',
            'type' => 'integer|between:0,99',
            'model_id' => 'nullable|integer|exists:ups_models,id'
        ]);

        $device->update($validated);
        return response()->json($device);
    }

    public function destroy($id)
    {
        $device = DeviceList::findOrFail($id);
        $device->delete();
        return response()->json(null, 204);
    }

    public function getDataCenters()
    {
        $dataCenters = DataCenterCreation::all();
        return response()->json($dataCenters);
    }

    public function getByDataCenter($dataCenterId)
    {
        $devices = DeviceList::where('data_center_id', $dataCenterId)->get();
        return response()->json($devices);
    }
}

// namespace App\Http\Controllers;

// use App\Models\DataCenterCreation;
// use App\Models\DeviceList;
// use Illuminate\Http\Request;

// class DeviceController extends Controller
// {
//     public function index()
//     {
//         $devices = DeviceList::with('dataCenter')->get();
//         return response()->json($devices);
//     }

//     // Show single device
//     public function show($id)
//     {
//         $device = DeviceList::with('dataCenter')->findOrFail($id);
//         return response()->json($device);
//     }

//     public function store(Request $request)
//     {
//         $validated = $request->validate([
//             'name' => 'required|string|max:255',
//             'data_center_id' => 'required|exists:data_center_creations,id',
//             'location' => 'required|string|max:255',
//             'secret_key' => 'nullable|string|max:255',
//             'control_topic' => 'nullable|string|max:255',
//             'status' => 'required|integer|between:0,2'
//         ]);

//         $device = DeviceList::create($validated);
//         return response()->json($device, 201);
//     }

//     public function update(Request $request, $id)
//     {
//         $device = DeviceList::findOrFail($id);

//         $validated = $request->validate([
//             'name' => 'sometimes|string|max:255',
//             'data_center_id' => 'sometimes|exists:data_center_creations,id',
//             'location' => 'sometimes|string|max:255',
//             'secret_key' => 'nullable|string|max:255',
//             'control_topic' => 'nullable|string|max:255',
//             'status' => 'sometimes|integer|between:0,2'
//         ]);

//         $device->update($validated);
//         return response()->json($device);
//     }

//     public function destroy($id)
//     {
//         $device = DeviceList::findOrFail($id);
//         $device->delete();
//         return response()->json(null, 204);
//     }

//     public function getDataCenters()
//     {
//         $dataCenters = DataCenterCreation::all();
//         return response()->json($dataCenters);
//     }

//     public function getByDataCenter($dataCenterId)
//     {
//         $devices = DeviceList::where('data_center_id', $dataCenterId)->get();
//         return response()->json($devices);
//     }
// }
