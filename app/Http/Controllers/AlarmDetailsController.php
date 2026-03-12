<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Models\AlarmAcknowledgement;
use App\Models\AlarmAcknowledgementLog;

class AlarmDetailsController extends Controller
{
    use ApiResponseTrait;

    public function acknowledgementStore(Request $request){

        $store = AlarmAcknowledgement::create([
                    'sensor_id'=> $request->sensorId,
                    'alarm_value'=> $request->alarmValue,
                    'checked_by'=> $request->userId,
                    'description'=> $request->message
                    ]);

                AlarmAcknowledgementLog::create([
                    'sensor_id' => $request->sensorId,
                    'alarm_value' => $request->alarmValue,
                    'checked_by' => $request->userId,
                    'description' => $request->message
                ]);

        return $this->successResponse($store, 'Acknowledged Successfully');
    }

    public function syncAndCountAcknowledgements(Request $request)
    {
        try {
            $sensorIds = $request->sensorIds;

            if (!is_array($sensorIds) || empty($sensorIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or empty sensorIds provided',
                ], 422);
            }

            // Delete acknowledgements for sensors NOT in the current active alarm list
            AlarmAcknowledgement::whereNotIn('sensor_id', $sensorIds)->delete();

            // Count remaining acknowledged sensors that are still in the active list
            $remainingCount = AlarmAcknowledgement::whereIn('sensor_id', $sensorIds)->count();

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

    
}
