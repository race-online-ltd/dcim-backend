<?php

namespace App\Http\Controllers;

use App\Models\DataCenterCreation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AlarmAcknowledgement;

class AllDashboardController extends Controller
{
    public function getAllDC()
    {

        $count = DataCenterCreation::count();

        return response()->json([
            'total_data_centers' => $count
        ]);
    }


    // public function getrDataCenterAlarmDetails(Request $request)
    // {
    //     try {
    //         $sensorIds = $request->sensorIds;

    //         if (!is_array($sensorIds) || empty($sensorIds)) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Invalid or empty sensorIds provided',
    //             ], 422);
    //         }

    //         $data = DB::table('sensor_lists as sl')
    //             ->join('data_center_creations as dc', 'sl.data_center_id', '=', 'dc.id')
    //             ->join('sensor_type_lists as stl', 'sl.sensor_type_list_id', '=', 'stl.id')
    //             ->join('device_lists as dl', 'sl.device_id', '=', 'dl.id')
    //             ->join('sensor_real_time_values as stv', 'sl.id', '=', 'stv.sensor_id')
    //             ->whereIn('sl.id', $sensorIds)
    //             ->select([
    //                 'dc.name as Data_Center',
    //                 'dl.name as Device_Name',
    //                 'stl.name as Sensor_type',
    //                 'sl.id as Sensor_Id',
    //                 'sl.location as Sensor_location',
    //                 'stv.value as Sensor_value',
    //                 'stv.created_at'
    //             ])
    //             ->get();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Data retrieved successfully',
    //             'data' => $data
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to retrieve data',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    // public function getrDataCenterAlarmDetails(Request $request)
    // {
    //     try {
    //         $sensorIds = $request->sensorIds;
    //         $dataCenterId = intval($request->dc_id);

    //         // dd($sensorIds, $dataCenterId);

    //         // return $sensorIds;

    //         if (!is_array($sensorIds) || empty($sensorIds)) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Invalid or empty sensorIds provided',
    //             ], 422);
    //         }

    //         // Get sensor data as before
    //         $data = DB::table('sensor_lists as sl')
    //             ->join('data_center_creations as dc', 'sl.data_center_id', '=', 'dc.id')
    //             ->join('sensor_type_lists as stl', 'sl.sensor_type_list_id', '=', 'stl.id')
    //             ->join('device_lists as dl', 'sl.device_id', '=', 'dl.id')
    //             ->join('sensor_real_time_values as stv', 'sl.id', '=', 'stv.sensor_id')
    //             ->leftjoin('alarm_acknowledgements as aa', 'sl.id', '=', 'aa.sensor_id')
    //             ->whereIn('sl.id', $sensorIds)
    //             ->select([
    //                 'dc.name as Data_Center',
    //                 'dl.name as Device_Name',
    //                 'stl.name as Sensor_type',
    //                 'sl.id as Sensor_Id',
    //                 'sl.location as Sensor_location',
    //                 'stv.value as Sensor_value',
    //                 'stv.created_at',
    //                 'aa.created_at as acknowledged_at',
    //             ])
    //             ->orderBy('Sensor_type')
    //             ->get();

    //             $existingAcknowledgedSensors = AlarmAcknowledgement::whereIn('sensor_id', $sensorIds)
    //             ->pluck('sensor_id')
    //             ->unique()
    //             ->values()
    //             ->toArray();

    //         // Find and delete acknowledgements for sensors not in the current request
    //         // $deletedCount = AlarmAcknowledgement::whereNotIn('sensor_id', $sensorIds)
            
    //         // ->delete();

    //             $data = $data->map(function ($item) use ($existingAcknowledgedSensors) {
    //             $item->is_acknowledged = in_array($item->Sensor_Id, $existingAcknowledgedSensors);
    //             return $item;
    //         });

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Data retrieved successfully',
    //             'data' => $data,
    //             // 'deleted_acknowledgements_count' => $deletedCount,
    //             // 'existing_acknowledged_sensors' => $existingAcknowledgedSensors
    //         ]);
            
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to retrieve data',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }


    public function getrDataCenterAlarmDetails(Request $request)
    {
        try {
            $sensorIds = $request->sensorIds;
            $dataCenterId = intval($request->dc_id);

            if (!is_array($sensorIds) || empty($sensorIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or empty sensorIds provided',
                ], 422);
            }

            // Get sensor data as before
            $data = DB::table('sensor_lists as sl')
                ->join('data_center_creations as dc', 'sl.data_center_id', '=', 'dc.id')
                ->join('sensor_type_lists as stl', 'sl.sensor_type_list_id', '=', 'stl.id')
                ->join('device_lists as dl', 'sl.device_id', '=', 'dl.id')
                ->join('sensor_real_time_values as stv', 'sl.id', '=', 'stv.sensor_id')
                ->leftjoin('alarm_acknowledgements as aa', 'sl.id', '=', 'aa.sensor_id')
                ->whereIn('sl.id', $sensorIds)
                ->select([
                    'dc.name as Data_Center',
                    'dl.name as Device_Name',
                    'stl.name as Sensor_type',
                    'sl.id as Sensor_Id',
                    'sl.location as Sensor_location',
                    'stv.value as Sensor_value',
                    'stv.created_at',
                    'aa.alarm_value as acknowledged_alarm_value',
                    'aa.created_at as acknowledged_at',
                ])
                ->orderBy('Sensor_type')
                ->get();

            $existingAcknowledgedSensors = AlarmAcknowledgement::whereIn('sensor_id', $sensorIds)
                ->select('sensor_id', 'alarm_value', 'created_at')
                ->get()
                ->keyBy('sensor_id');

            $data = $data->map(function ($item) use ($existingAcknowledgedSensors) {
                $ack = $existingAcknowledgedSensors->get($item->Sensor_Id);

                $item->is_acknowledged =
                    !is_null($ack)
                    && $ack->alarm_value == $item->acknowledged_alarm_value
                    && $ack->created_at  == $item->acknowledged_at;

                return $item;
            });

            return response()->json([
                'success' => true,
                'message' => 'Data retrieved successfully',
                'data' => $data,
                'existing_acknowledged_sensors' => $existingAcknowledgedSensors,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    
}
