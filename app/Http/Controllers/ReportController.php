<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ReportController extends Controller
{
    public function getSensorData(Request $request)
    {
        // Validate required date parameters
        $validated = $request->validate([
            'from_date' => 'required|date_format:Y-m-d',
            'to_date' => 'required|date_format:Y-m-d',
            'data_center_id' => 'nullable|integer|min:1',
            'sensor_type_list_id' => 'nullable|integer|min:1',
        ]);
 
        $fromDate = Carbon::createFromFormat('Y-m-d', $validated['from_date'])->startOfDay();
        $toDate = Carbon::createFromFormat('Y-m-d', $validated['to_date'])->endOfDay();
        $dataCenterId = $validated['data_center_id'] ?? null;
        $sensorTypeListId = $validated['sensor_type_list_id'] ?? null;
 
        try {
            // Build the WHERE clause dynamically
            $whereConditions = [
                "v.created_at BETWEEN '{$fromDate}' AND '{$toDate}'"
            ];
 
            if ($dataCenterId !== null) {
                $whereConditions[] = "s.data_center_id = {$dataCenterId}";
            }
 
            if ($sensorTypeListId !== null) {
                $whereConditions[] = "s.sensor_type_list_id = {$sensorTypeListId}";
            }
 
            $whereClause = implode(' AND ', $whereConditions);
 
            $query = "
                SELECT 
                    dcc.name AS datacenter_name,
                    s.data_center_id,
                    v.sensor_id,
                    s.sensor_name,
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
                    ) AS status,
                    s.sensor_type_list_id as sensor_type,
                    l.name AS sensor_type_name,
                    s.location,
                    v.created_at
                FROM sensor_log_values v
                JOIN sensor_lists s ON v.sensor_id = s.id
                JOIN data_center_creations dcc ON s.data_center_id = dcc.id
                JOIN sensor_type_lists l ON s.sensor_type_list_id = l.id
                LEFT JOIN state_configs sc ON v.sensor_id = sc.sensor_id AND v.value = sc.value
                LEFT JOIN threshold_values tv ON v.sensor_id = tv.sensor_id
                WHERE {$whereClause}
                GROUP BY 
                    v.sensor_id,
                    v.value,
                    s.data_center_id,
                    s.sensor_type_list_id,
                    s.sensor_name,
                    s.location,
                    sc.name,
                    l.name,
                    v.created_at,
                    dcc.name
                ORDER BY s.data_center_id, s.sensor_name
            ";
 
            $results = DB::select($query);
 
            return response()->json([
                'status' => 'success',
                'message' => 'Sensor data retrieved successfully',
                'count' => count($results),
                'filters_applied' => [
                    'from_date' => $validated['from_date'],
                    'to_date' => $validated['to_date'],
                    'data_center_id' => $dataCenterId,
                    'sensor_type_list_id' => $sensorTypeListId,
                ],
                'data' => $results,
            ], 200);
 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error retrieving sensor data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
