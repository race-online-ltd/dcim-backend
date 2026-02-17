<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardDataController extends Controller
{
    public function getThresholdsByDataCenter($dataCenterId)
    {
        // Validate the ID is numeric
        if (!is_numeric($dataCenterId)) {
            return response()->json(['error' => 'Invalid data center ID'], 400);
        }

        //        $thresholds = DB::table('threshold_values as tv')
        //            ->select(
        //                'tv.sensor_id',
        //                'tv.threshold_type_id',
        //                'tv.threshold',
        //                'ty.name as threshold_name',
        //                'ty.color',
        //                'sl.location',
        //                'dcc.id as dc_id',
        //                'dcc.name as dc_name'
        //            )
        //            ->join('threshold_types as ty', 'tv.threshold_type_id', '=', 'ty.id')
        //            ->join('sensor_lists as sl', 'tv.sensor_id', '=', 'sl.id')
        //            ->join('data_center_creations as dcc', 'sl.data_center_id', '=', 'dcc.id')
        //            ->where('dcc.id', $dataCenterId)
        //            ->get();

        $thresholds = DB::connection('mysql')->select("SELECT tv.sensor_id, tv.threshold_type_id, tv.threshold, ty.name threshold_name, ty.color, sl.location, stl.id sensor_type, stl.name sensor_type_name
            , dcc.id dc_id, dcc.name dc_name
            FROM threshold_values tv
            INNER JOIN threshold_types ty ON tv.threshold_type_id = ty.id
            INNER JOIN sensor_lists sl ON tv.sensor_id = sl.id
            INNER JOIN data_center_creations dcc ON sl.data_center_id = dcc.id
            INNER JOIN sensor_type_lists stl ON sl.sensor_type_list_id = stl.id
            WHERE dcc.id = '$dataCenterId'
            AND sl.status = 1");

        return response()->json($thresholds);
    }


    public function getSensorTypeByDataCenter($dataCenterId)
    {
        // Validate the ID is numeric
        if (!is_numeric($dataCenterId)) {
            return response()->json(['error' => 'Invalid data center ID'], 400);
        }

        $sensorTypes = DB::connection('mysql')->select("SELECT stl.id sensor_type, stl.name sensor_type_name
            FROM sensor_lists sl
			INNER JOIN data_center_creations dcc ON sl.data_center_id = dcc.id
            INNER JOIN sensor_type_lists stl ON sl.sensor_type_list_id = stl.id
            WHERE dcc.id IN ($dataCenterId)
            AND sl.status = 1
            GROUP BY stl.id,stl.name");

        return response()->json($sensorTypes);
    }


    public function getStatesByDataCenter($dataCenterId)
    {

        // Validate the ID is numeric
        if (!is_numeric($dataCenterId)) {
            return response()->json(['error' => 'Invalid data center ID'], 400);
        }
        $thresholds = DB::connection('mysql')->select("SELECT sc.sensor_id,sc.path, slt.id AS type_id, slt.name AS type_name, sc.value AS state_value, sc.name AS state_name,
                                        sl.location, sl.sensor_name, sc.color, dot.mode_id, dom.name AS mode_type, dot.repeat_id, dot.day_id,
                                        DATE_FORMAT(STR_TO_DATE(dot.on_time, '%H:%i'), '%h:%i %p') AS on_time,
                                        DATE_FORMAT(STR_TO_DATE(dot.off_time, '%H:%i'), '%h:%i %p') AS off_time,
                                            dot.duration AS runningDuration, dot.off_duration AS restDuration,
                                        CASE
                                            WHEN pl.sensor_id IS NOT NULL THEN 1
                                            ELSE 0
                                        END AS paired_status, pg.group AS paired_sensors,
                                        (
                                            SELECT GROUP_CONCAT(s.name SEPARATOR ', ')
                                            FROM schedullings s
                                            WHERE JSON_OVERLAPS(dot.day_id, CAST(JSON_ARRAY(s.id) AS JSON))
                                        ) AS day_names
                                    FROM state_configs sc
                                    LEFT JOIN sensor_lists sl ON sc.sensor_id = sl.id
                                    LEFT JOIN sensor_type_lists slt ON sl.sensor_type_list_id = slt.id AND slt.id IN (3,4,5,6)
                                    LEFT JOIN data_center_creations dcc ON sl.data_center_id = dcc.id
                                    LEFT JOIN do_operation_triggers dot ON sc.sensor_id = dot.sensor_id AND dot.status = 1
                                    LEFT JOIN do_operation_modes dom ON dot.mode_id = dom.id
                                    LEFT JOIN pair_lists pl ON sc.sensor_id = pl.sensor_id
                                    LEFT JOIN pair_groups pg ON pl.pair_group_id = pg.id
                                    WHERE dcc.id = '$dataCenterId'
                                    AND sl.status = 1");

        return response()->json($thresholds);
    }
}
