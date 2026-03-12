<?php

namespace App\Http\Controllers;

use App\Models\SensorList;
use App\Models\SensorTypeList;
use App\Models\StateConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SensorListController extends Controller
{
//    public function index()
//    {
//        $sensorLists = SensorList::with(['dataCenter', 'device', 'sensorType', 'triggerType'])->get();
//        return response()->json($sensorLists);
//    }

    public function index(Request $request)
    {
        $query = SensorList::with([
            'dataCenter',
            'device',
            'sensorType:id,name',
            'triggerType'
        ]);

        // Apply filters if 'device_id' is present in the request
        if ($request->has('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        // Apply filters if 'trigger_type_id' is present in the request
        if ($request->has('trigger_type_id')) {
            $query->where('trigger_type_id', $request->trigger_type_id);
        }

        // Get the results from the database
        $sensorLists = $query
        ->orderBy('id', 'desc')
        ->get();

        // Return the sensor lists as a JSON response
        return response()->json($sensorLists);
    }

//    public function store(Request $request)
//    {
//        $validated = $request->validate([
//            'data_center_id' => 'required|integer',
//            'device_id' => 'required|integer',
//            'sensor_type_list_id' => 'required|integer',
//            'trigger_type_id' => 'required|integer',
//            'sound_status' => 'sometimes|integer',
//            'blink_status' => 'sometimes|integer',
//            'location' => 'required|string|max:255',
//            'status' => 'sometimes|integer',
//            'timestamp' => 'sometimes|date'
//        ]);
//
//        // Generate a 7-digit unique ID
//        do {
//            $uniqueId = mt_rand(1000000, 9999999);
//        } while (SensorList::where('unique_id', $uniqueId)->exists());
//
//        $validated['unique_id'] = $uniqueId;
//
//        $sensorList = SensorList::create($validated);
//        return response()->json($sensorList, 201);
//    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'data_center_id' => 'required|integer',
            'device_id' => 'required|integer',
            'sensor_type_list_id' => 'required|integer',
            'trigger_type_id' => 'required|integer',
            'sound_status' => 'sometimes|integer',
            'blink_status' => 'sometimes|integer',
            'sensor_name' => 'nullable|string|max:255',
            'location' => 'required|string|max:255',
            'status' => 'sometimes|integer',
            'timestamp' => 'sometimes|date'
        ]);

        try {
            // Generate a 7-digit unique ID
            do {
                $uniqueId = mt_rand(1000000, 9999999);
            } while (SensorList::where('unique_id', $uniqueId)->exists());

            $validated['unique_id'] = $uniqueId;

            // Start database transaction
            DB::beginTransaction();

            $sensorList = SensorList::create($validated);

            // Check if sensor type is Water (4) or Smoke (5)
            if (in_array($validated['sensor_type_list_id'], [3, 4, 5, 6])) {
                switch ($validated['sensor_type_list_id']) {
                    case 3:
                        $stateConfigs = [
                            [
                                'value' => 0,
                                'name' => 'Alarm',
                                'attache_sound' => null,
                                'url' => null,
                                'color' => '#ff0000',
                                'sound' => 1,
                                'blink' => 1,
                                'created_at' => now(),
                                'updated_at' => now()
                            ],
                            [
                                'value' => 1,
                                'name' => 'Normal',
                                'attache_sound' => null,
                                'url' => null,
                                'color' => '#00ff00',
                                'sound' => 0,
                                'blink' => 0,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]
                        ];
                        break;

                    case 4:
                        $stateConfigs = [
                            [
                                'value' => 0,
                                'name' => 'Normal',
                                'attache_sound' => null,
                                'url' => null,
                                'color' => '#00ff00',
                                'sound' => 0,
                                'blink' => 0,
                                'created_at' => now(),
                                'updated_at' => now()
                            ],
                            [
                                'value' => 1,
                                'name' => 'Warning',
                                'attache_sound' => null,
                                'url' => null,
                                'color' => '#ffff00',
                                'sound' => 0,
                                'blink' => 1,
                                'created_at' => now(),
                                'updated_at' => now()
                            ],
                            [
                                'value' => 2,
                                'name' => 'Leaking',
                                'attache_sound' => null,
                                'url' => null,
                                'color' => '#ff0000',
                                'sound' => 1,
                                'blink' => 1,
                                'created_at' => now(),
                                'updated_at' => now()
                            ],
                            [
                                'value' => 3,
                                'name' => 'Fault',
                                'attache_sound' => null,
                                'url' => null,
                                'color' => '#9c9c9c',
                                'sound' => 1,
                                'blink' => 1,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]
                        ];
                        break;

                    case 5:
                        $stateConfigs = [
                            [
                                'value' => 0,
                                'name' => 'Normal',
                                'attache_sound' => null,
                                'url' => null,
                                'color' => '#00ff00',
                                'sound' => 0,
                                'blink' => 0,
                                'created_at' => now(),
                                'updated_at' => now()
                            ],
                            [
                                'value' => 1,
                                'name' => 'Alarm',
                                'attache_sound' => null,
                                'url' => null,
                                'color' => '#ff0000',
                                'sound' => 1,
                                'blink' => 1,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]
                        ];
                        break;

                    case 6:
                        $stateConfigs = [
                            [
                                'value' => 0,
                                'name' => 'Close',
                                'attache_sound' => null,
                                'url' => null,
                                'color' => '#00ff00',
                                'sound' => 0,
                                'blink' => 0,
                                'created_at' => now(),
                                'updated_at' => now()
                            ],
                            [
                                'value' => 1,
                                'name' => 'Open',
                                'attache_sound' => null,
                                'url' => null,
                                'color' => '#ff0000',
                                'sound' => 0,
                                'blink' => 1,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]
                        ];
                        break;
                }

                // Add sensor_id to each config
                $stateConfigs = array_map(function($config) use ($sensorList) {
                    $config['sensor_id'] = $sensorList->id;
                    return $config;
                }, $stateConfigs);

                // Insert all configs at once
                $inserted = StateConfig::insert($stateConfigs);

                if (!$inserted) {
                    throw new \Exception('Failed to insert state configurations');
                }

                Log::info('State configs created for sensor', [
                    'sensor_id' => $sensorList->id,
                    'configs_count' => count($stateConfigs)
                ]);
            }

            // Commit transaction if everything is successful
            DB::commit();

            return response()->json($sensorList, 201);

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            Log::error('Sensor creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Sensor creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $sensorList = SensorList::with(['dataCenter', 'device', 'sensorType', 'triggerType'])->findOrFail($id);
        return response()->json($sensorList);
    }

    public function update(Request $request, $id)
    {
        $sensorList = SensorList::findOrFail($id);

        $validated = $request->validate([
            'data_center_id' => 'sometimes|integer',
            'device_id' => 'sometimes|integer',
            'sensor_type_list_id' => 'sometimes|integer',
            'unique_id' => 'sometimes|integer',
            'trigger_type_id' => 'sometimes|integer',
            'sound_status' => 'sometimes|integer',
            'blink_status' => 'sometimes|integer',
            'sensor_name' => 'nullable|string|max:255',
            'location' => 'sometimes|string|max:255',
            'status' => 'sometimes|integer',
            'timestamp' => 'sometimes|date'
        ]);

        $sensorList->update($validated);
        return response()->json($sensorList);
    }

    public function destroy($id)
    {
        SensorList::findOrFail($id)->delete();
        return response()->json(null, 204);
    }


    public function fetchSensorTypeList()
    {
        $sensorsType = SensorTypeList::all();

        return response()->json([
            'status' => true,
            'data' => $sensorsType
        ]);
    }

    public function getByDevice($deviceId,$sensorTypeId)
    {
        $sensors = SensorList::where('device_id', $deviceId)
        ->where('sensor_type_list_id',$sensorTypeId)
        ->where('trigger_type_id',2)
        ->get();
        return response()->json($sensors);
    }

    // public function getSensorByDataCenter($dataCenterId)
    // {
    //     $data = DB::select(
    //         "
    //         SELECT v.sensor_id,v.value,s.data_center_id, s.sensor_type_list_id as sensor_type,
    //             s.sensor_name,
    //             s.location,
    //             l.name AS sensor_type_name
    //         FROM sensor_real_time_values v
    //         JOIN sensor_lists s
    //             ON v.sensor_id = s.id
    //         JOIN sensor_type_lists l
    //             ON s.sensor_type_list_id = l.id
    //         WHERE s.data_center_id = ?
    //         ORDER BY s.sensor_name
    //         ",
    //         [$dataCenterId]
    //     );

    //     return response()->json([
    //         'success' => true,
    //         'data' => $data
    //     ]);
    // }

    public function getSensorByDataCenter($dataCenterIds)
    {
        try {
            $ids = explode(',', $dataCenterIds);
            $ids = array_map('intval', array_filter($ids));

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data center IDs'
                ], 400);
            }

            $data = DB::select(
                "
                SELECT
                    v.sensor_id,
                    v.value,
                    COALESCE(
                        sc.name,
                        CASE
                            WHEN v.value >= MAX(CASE WHEN tv.threshold_type_id = 1 THEN tv.threshold END)
                                THEN 'High'

                            WHEN v.value >= MAX(CASE WHEN tv.threshold_type_id = 2 THEN tv.threshold END)
                                THEN 'Normal'

                            ELSE 'Low'
                        END
                    ) AS name,
                    s.data_center_id,
                    s.sensor_type_list_id as sensor_type,
                    s.sensor_name,
                    s.location,
                    l.name AS sensor_type_name
                FROM sensor_real_time_values v
                JOIN sensor_lists s ON v.sensor_id = s.id

                JOIN sensor_type_lists l ON s.sensor_type_list_id = l.id

                LEFT JOIN state_configs sc ON v.sensor_id = sc.sensor_id AND v.value = sc.value

                LEFT JOIN threshold_values tv  ON v.sensor_id = tv.sensor_id
                WHERE s.data_center_id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")
                GROUP BY
                v.sensor_id,
                v.value,
                s.data_center_id,
                s.sensor_type_list_id,
                s.sensor_name,
                s.location,
                sc.name,
                l.name
                ORDER BY s.data_center_id, s.sensor_name
                ",
                $ids
            );

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching sensor real-time data: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sensor real-time data',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function getDevicesByDataCenter($dataCenterIds)
    {
        try {
            $ids = explode(',', $dataCenterIds);
            $ids = array_map('intval', array_filter($ids));

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data center IDs'
                ], 400);
            }

            $data = DB::select(
                "
                SELECT dcc.id AS datacenter_id, dcc.name AS datacenter,dl.id AS device_id, dl.name AS device, dl.is_active,
                    dl.location, dl.updated_at
                FROM device_lists dl
                JOIN data_center_creations dcc ON dl.data_center_id = dcc.id
                WHERE dl.status = 1
                AND dcc.id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")
                ORDER BY dcc.id, dl.id
                ",
                $ids
            );

            return response()->json([
                'success' => true,
                'data'    => $data,
                'count'   => count($data)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching devices by data center: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch devices',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function getOfflineDeviceBySensor($dataCenterIds)
    {
        try {
            $ids = explode(',', $dataCenterIds);
            $ids = array_map('intval', array_filter($ids));

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data center IDs'
                ], 400);
            }

            $data = DB::select(
                "
                SELECT dcc.id AS datacenter_id, dcc.name AS datacenter,dl.id AS device_id, dl.name AS device, dl.is_active,
                    dl.location, dl.updated_at
                FROM device_lists dl
                JOIN data_center_creations dcc ON dl.data_center_id = dcc.id
                WHERE dl.status = 1
                AND dl.id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")
                ORDER BY dcc.id, dl.id
                ",
                $ids
            );

            return response()->json([
                'success' => true,
                'data'    => $data,
                'count'   => count($data)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching devices by data center: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch devices',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function getByDeviceThreshold($deviceId)
    {
        $sensors = SensorList::where('device_id', $deviceId)
            ->where('trigger_type_id', 1)
            ->get();
        return response()->json($sensors);
    }
}
