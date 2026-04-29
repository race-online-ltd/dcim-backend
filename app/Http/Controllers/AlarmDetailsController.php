<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Models\AlarmAcknowledgement;
use App\Models\AlarmAcknowledgementLog;
use App\Jobs\StoreSensorFaultJob;
use Illuminate\Support\Facades\DB;
use App\Models\SensorFaultTable;


class AlarmDetailsController extends Controller
{
    use ApiResponseTrait;

    // public function acknowledgementStore(Request $request){

    //     $store = AlarmAcknowledgement::create([
    //                 'sensor_id'=> $request->sensorId,
    //                 'alarm_value'=> $request->alarmValue,
    //                 'checked_by'=> $request->userId,
    //                 'description'=> $request->message
    //                 ]);

    //             AlarmAcknowledgementLog::create([
    //                 'sensor_id' => $request->sensorId,
    //                 'alarm_value' => $request->alarmValue,
    //                 'checked_by' => $request->userId,
    //                 'description' => $request->message
    //             ]);


    //             DB::table('sensor_fault_tables')
    //                 ->where('sensor_id', $request->sensorId)
    //                 ->orderBy('id', 'desc')
    //                 ->limit(1)
    //                 ->update(['alarm_severity' => 1]);

    //     return $this->successResponse($store, 'Acknowledged Successfully');
    // }




public function acknowledgementStore(Request $request)
{
    try {

        DB::beginTransaction();

        // 🔥 1. Store acknowledgement (main)
        $store = AlarmAcknowledgement::create([
            'sensor_id'   => $request->sensorId,
            'alarm_value' => $request->alarmValue,
            'checked_by'  => $request->userId,
            'description' => $request->message
        ]);

        // 🔥 2. Store log (history)
        AlarmAcknowledgementLog::create([
            'sensor_id'   => $request->sensorId,
            'alarm_value' => $request->alarmValue,
            'checked_by'  => $request->userId,
            'description' => $request->message
        ]);

        // 🔥 3. Get latest sensor fault row
        $latestFault = SensorFaultTable::where('sensor_id', $request->sensorId)
            ->orderBy('id', 'desc')
            ->first();

        if ($latestFault) {
            $latestFault->update([
                'acknowledgement_status'=> 1,
                'checked_by'            => $request->userId,
                'description'           => $request->message,
            ]);
        }

        DB::commit();

        return $this->successResponse($store, 'Acknowledged Successfully');

    } catch (\Exception $e) {

        DB::rollback();

        return response()->json([
            'success' => false,
            'message' => 'Failed to acknowledge',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    public function syncAndCountAcknowledgements(Request $request)
    {
        try {
            $sensorIds = $request->sensor_ids;

            if (!is_array($sensorIds) || empty($sensorIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or empty sensorIds provided',
                ], 422);
            }

            // Get rows that will be deleted
        $rowsToDelete = AlarmAcknowledgement::whereNotIn('sensor_id', $sensorIds)->get();

        // Insert deleted rows into log table
        foreach ($rowsToDelete as $row) {
            AlarmAcknowledgementLog::create([
                'sensor_id' => $row->sensor_id,
                'alarm_value' => $row->alarm_value,
                'checked_by' => $row->checked_by,
                'description' => $row->description,
                'created_at' => $row->created_at,
                'updated_at' => now()
            ]);
        }

           AlarmAcknowledgement::whereNotIn('sensor_id', $sensorIds)->delete();


            // Count remaining acknowledged sensors that are still in the active list
            $remainingCount = AlarmAcknowledgement::whereIn('sensor_id', $sensorIds)->distinct('sensor_id')->count();

            return response()->json([
                'success' => true,
                'message' => 'Acknowledgements synced successfully',
                'acknowledged_count' => $remainingCount,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync acknowledgements',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function storeSensorFault(Request $request)
    {
        try {
            $request->validate([
            'sensors' => 'required|array',
            'sensors.*.sensor_id' => 'required|integer',
            'sensors.*.value' => 'required',
            'sensors.*.alarm_time' => 'required|date'
        ]);

        StoreSensorFaultJob::dispatch($request->sensors);
        return response()->json([
            'success' => true,
            'message' => 'Sensor fault reported successfully',
        ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to report sensor fault',
                'error'   => $e->getMessage(),
            ], 500);
        }


    }


//     public function alarmLogs(Request $request)
// {
//     $query = DB::table('sensor_lists as sl')
//         ->select(
//             'dc.name as datacenter_name',
//             'stl.name as sensor_type',
//             'sl.id as sensor_id',
//             'sl.sensor_name',
//             'sft.value as alarm_value',
//             'sft.created_at as alarm_raised_at',
//             'sl.location',
//             'acl.alarm_value as acknowledge_alarm_value',
//             'acl.description',
//             'u.username as acknowledge_by',
//             'acl.created_at as acknowledge_at',
//             DB::raw("
//                 CASE
//                     WHEN acl.id IS NOT NULL THEN 'Acknowledged'
//                     ELSE 'Not Acknowledged'
//                 END AS acknowledge_status
//             ")
//         )
//         ->join('data_center_creations as dc', 'dc.id', '=', 'sl.data_center_id')
//         ->join('sensor_type_lists as stl', 'stl.id', '=', 'sl.sensor_type_list_id')
//         ->join('sensor_fault_tables as sft', 'sft.sensor_id', '=', 'sl.id')

//         // ✅ FIXED JOIN (NO DUPLICATE + MATCH sensor_id + value)
//         ->leftJoin(DB::raw("
//             (
//                 SELECT *
//                 FROM alarm_acknowledgement_logs a1
//                 WHERE id = (
//                     SELECT MAX(id)
//                     FROM alarm_acknowledgement_logs a2
//                     WHERE a2.sensor_id = a1.sensor_id
//                       AND a2.alarm_value = a1.alarm_value
//                 )
//             ) as acl
//         "), function ($join) {
//             $join->on('acl.sensor_id', '=', 'sft.sensor_id')
//                  ->on('acl.alarm_value', '=', 'sft.value');
//         })

//         ->leftJoin('users as u', 'acl.checked_by', '=', 'u.id');

//     // 🔥 FILTERS (UNCHANGED)

//     if ($request->filled('datacenter')) {
//         $query->where('dc.id', $request->datacenter);
//     }

//     if ($request->filled('sensor_type')) {
//         $query->where('stl.id', $request->sensor_type);
//     }

//     if ($request->filled('sensor_id')) {
//         $query->where('sl.id', $request->sensor_id);
//     }

//     if ($request->filled('acknowledge_by')) {
//         $query->where('u.id', $request->acknowledge_by);
//     }

//     if ($request->filled('status')) {
//         if ($request->status == 'Acknowledged') {
//             $query->whereNotNull('acl.id');
//         } else {
//             $query->whereNull('acl.id');
//         }
//     }

//     // 🔥 PAGINATION
//     $perPage = $request->get('per_page', 10);

//     // $data = $query->orderBy('sl.id', 'asc')
//     //               ->paginate($perPage);
//     $data = $query
//     ->orderByRaw('acl.id IS NULL DESC') // 🔴 Not Acknowledged first
//     ->orderByRaw('COALESCE(acl.created_at, sft.created_at) DESC') // 🔥 Latest activity
//     ->paginate($perPage);

//     return response()->json([
//         'success' => true,
//         'message' => 'Alarm report fetched successfully',
//         'data' => $data
//     ]);
// }




// public function alarmLogs(Request $request)
// {
//     $query = DB::table('sensor_lists as sl')
//         ->select(
//             'dc.name as datacenter_name',
//             'stl.name as sensor_type',
//             'sl.id as sensor_id',
//             'sl.sensor_name',
//             'sft.value as alarm_value',
//             'sft.created_at as alarm_raised_at',
//             'sl.location',
//             'acl.alarm_value as acknowledge_alarm_value',
//             'acl.description',
//             'u.username as acknowledge_by',
//             'acl.created_at as acknowledge_at',
//             DB::raw("
//                 CASE
//                     WHEN sft.alarm_severity = 1 THEN 'Acknowledged'
//                     WHEN acl.id IS NOT NULL THEN 'Acknowledged'
//                     ELSE 'Not Acknowledged'
//                 END AS acknowledge_status
//             ")
//         )
//         ->join('data_center_creations as dc', 'dc.id', '=', 'sl.data_center_id')
//         ->join('sensor_type_lists as stl', 'stl.id', '=', 'sl.sensor_type_list_id')
//         ->join('sensor_fault_tables as sft', 'sft.sensor_id', '=', 'sl.id')
//         ->leftJoin('alarm_acknowledgement_logs as acl', function ($join) {
//             $join->on('acl.sensor_id', '=', 'sft.sensor_id')
//                  ->on('acl.alarm_value', '=', 'sft.value');
//         })
//         ->leftJoin('users as u', 'acl.checked_by', '=', 'u.id');

//     // 🔍 FILTERS
//     if ($request->filled('datacenter')) {
//         $query->where('dc.id', $request->datacenter);
//     }

//     if ($request->filled('sensor_type')) {
//         $query->where('stl.id', $request->sensor_type);
//     }

//     if ($request->filled('sensor_id')) {
//         $query->where('sl.id', $request->sensor_id);
//     }

//     if ($request->filled('acknowledge_by')) {
//         $query->where('u.id', $request->acknowledge_by);
//     }

//     if ($request->filled('status')) {
//         if ($request->status == 'Acknowledged') {
//             $query->where(function ($q) {
//                 $q->whereNotNull('acl.id')
//                   ->orWhere('sft.alarm_severity', 1);
//             });
//         } else {
//             $query->whereNull('acl.id')
//                   ->where(function ($q) {
//                       $q->whereNull('sft.alarm_severity')
//                         ->orWhere('sft.alarm_severity', '!=', 1);
//                   });
//         }
//     }

//     // 🔥 PAGINATION + SORT
//     $perPage = $request->get('per_page', 10);

//     $data = $query
//         ->orderByRaw('acl.id IS NULL DESC')
//         ->orderBy('sft.created_at', 'desc')
//         ->orderBy('acl.created_at', 'desc')
//         ->paginate($perPage);

//     return response()->json([
//         'success' => true,
//         'message' => 'Alarm report fetched successfully',
//         'data'    => $data
//     ]);
// }


    public function alarmLogs(Request $request)
    {
        $query = DB::table('sensor_lists as sl')
            ->select(
                'dc.name as datacenter_name',
                'stl.name as sensor_type',
                'sl.id as sensor_id',
                'sl.sensor_name',
                'sft.value as alarm_value',
                'sft.created_at as alarm_raised_at',
                'sl.location',
                'sft.value as acknowledge_alarm_value',
                'sft.acknowledgement_status',
                'sft.description',
                'sft.checked_by',
                'u.username as acknowledge_by',

                // ✅ Updated condition here
                DB::raw("
                    CASE
                        WHEN sft.acknowledgement_status IS NULL THEN NULL
                        ELSE sft.updated_at
                    END AS acknowledge_at
                "),

                DB::raw("
                    CASE
                        WHEN sft.acknowledgement_status = 1 THEN 'Acknowledged'
                        ELSE 'Not Acknowledged'
                    END AS acknowledge_status
                ")
            )
            ->join('data_center_creations as dc', 'dc.id', '=', 'sl.data_center_id')
            ->join('sensor_type_lists as stl', 'stl.id', '=', 'sl.sensor_type_list_id')
            ->join('sensor_fault_tables as sft', 'sft.sensor_id', '=', 'sl.id')
            ->leftJoin('users as u', 'sft.checked_by', '=', 'u.id');

        // 🔍 FILTERS
        if ($request->filled('datacenter')) {
            $query->where('dc.id', $request->datacenter);
        }

        if ($request->filled('sensor_type')) {
            $query->where('stl.id', $request->sensor_type);
        }

        if ($request->filled('sensor_id')) {
            $query->where('sl.id', $request->sensor_id);
        }

        if ($request->filled('acknowledge_by')) {
            $query->where('sft.checked_by', $request->acknowledge_by);
        }

        if ($request->filled('status')) {
            if ($request->status == 'Acknowledged') {
                $query->where('sft.acknowledgement_status', 1);
            } else {
                $query->where(function ($q) {
                    $q->whereNull('sft.acknowledgement_status')
                    ->orWhere('sft.acknowledgement_status', '!=', 1);
                });
            }
        }

        $perPage = $request->get('per_page', 10);

        $data = $query
            ->orderByRaw('sft.acknowledgement_status IS NULL DESC')
            ->orderBy('sft.created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Alarm report fetched successfully',
            'data'    => $data
        ]);
    }


}
