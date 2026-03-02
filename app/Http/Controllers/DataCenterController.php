<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\DashboardTabList;
use App\Models\DataCenter;
use App\Models\DataCenterCreation;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UserType;
use App\Traits\ApiResponseTrait;

class DataCenterController extends Controller
{
    use ApiResponseTrait;
    public function getUserDataCenters($userId)
    {
        if (!User::find($userId)) {
            return $this->errorResponse('User not found', 404);
        }

        $dataCenters =DB::select("SELECT dcc.id,dcc.name
            FROM dc_owner_mappings dom
            INNER JOIN data_center_creations dcc ON dcc.id = dom.data_center_id
            WHERE dom.user_id = '$userId' ");

        return $this->successResponse($dataCenters, 'Data centers fetched successfully');
    }

    public function getTabDataCenters($tabId)
    {
        if (!DashboardTabList::find($tabId)) {
            return $this->errorResponse('User not found', 404);
        }

        $dataCenters =DB::select("SELECT dtl.id ,dtl.name
        FROM tab_list_mappings tlm
        INNER JOIN dashboard_tab_lists dtl ON dtl.id = tlm.tab_id
        WHERE tlm.data_center_id = '$tabId'");

        return $this->successResponse($dataCenters, 'Data centers fetched successfully');
    }

    public function getDataCenters(Request $request)
    {
        $userTypeId = $request->user_type_id;

        // Get user_type name from user_types table
        $userType = DB::table('user_types')
            ->where('id', $userTypeId)
            ->value('name'); // Only need the 'name' field

        if (!$userType) {
            return response()->json(['message' => 'Invalid user type ID'], 400);
        }

        if ($userType === 'Super-Admin') {
            // Super Admin gets all data centers
            $dataCenters = DB::table('data_centers')
                ->select('id as dc_id', 'name as dc_name')
                ->get();
        } elseif (in_array($userType, ['Admin', 'Agent'])) {
            // Admin/Agent get only assigned data centers
            $permittedDcIds = DB::table('data_center_user')
                ->where('user_type_id', $userTypeId)
                ->pluck('data_center_id')
                ->toArray();

            $dataCenters = DB::table('data_centers')
                ->select('id as dc_id', 'name as dc_name')
                ->whereIn('id', $permittedDcIds)
                ->get();
        } elseif ($userType === 'Client') {
            // Client gets data centers with permitted devices
            $permittedDcIds = DB::table('data_center_user')
                ->where('user_type_id', $userTypeId)
                ->pluck('data_center_id')
                ->toArray();

            $permittedDeviceIds = DB::table('device_user')
                ->where('user_type_id', $userTypeId)
                ->pluck('device_id')
                ->toArray();

            $dataCenters = DB::table('data_centers')
                ->select('id as dc_id', 'name as dc_name')
                ->whereIn('id', $permittedDcIds)
                ->whereExists(function ($query) use ($permittedDeviceIds) {
                    $query->select(DB::raw(1))
                        ->from('devices')
                        ->whereColumn('devices.data_center_id', 'data_centers.id')
                        ->whereIn('devices.id', $permittedDeviceIds);
                })
                ->get();
        } else {
            return response()->json(['message' => 'Unauthorized user type'], 403);
        }

        return response()->json([
            'data_centers' => $dataCenters,
            'user_type' => $userType
        ]);
    }



    public function getDataCenterDevices(Request $request, $dcId)
    {
        $userTypeId = $request->user_type_id;

        // Fetch the user_type name
        $userType = DB::table('user_types')
            ->where('id', $userTypeId)
            ->value('name');

        if (!$userType) {
            return response()->json(['message' => 'Invalid user type ID'], 400);
        }

        // Only verify access for non-Super-Admin users
        if ($userType !== 'Super-Admin') {
            $hasDcAccess = DB::table('data_center_user')
                ->where('user_type_id', $userTypeId)
                ->where('data_center_id', $dcId)
                ->exists();

            if (!$hasDcAccess) {
                return response()->json(['message' => 'Unauthorized access to this data center'], 403);
            }
        }

        // Fetch devices based on user type
        if (in_array($userType, ['Super-Admin', 'Admin', 'Agent'])) {
            $devices = DB::table('devices')
                ->select('id as device_id', 'name as device_name')
                ->where('data_center_id', $dcId)
                ->get();
        } elseif ($userType === 'Client') {
            $permittedDeviceIds = DB::table('device_user')
                ->where('user_type_id', $userTypeId)
                ->pluck('device_id')
                ->toArray();

            $devices = DB::table('devices')
                ->select('id as device_id', 'name as device_name')
                ->where('data_center_id', $dcId)
                ->whereIn('id', $permittedDeviceIds)
                ->get();
        } else {
            return response()->json(['message' => 'Unauthorized user type'], 403);
        }

        return response()->json([
            'devices' => $devices,
            'data_center_id' => $dcId,
            'user_type' => $userType
        ]);
    }


    public function getDataCenterMapping()
    {
        // return 'hi';
        $dataCenters = DataCenterCreation::select('id', 'name')
            ->orderByDesc('created_at')
            ->get();


            // $dataCenters = DB::connection('mysql')->select("SELECT id, name FROM data_center_creations");
        return response()->json($dataCenters);
    }

    // public function index()
    // {
    //     $dataCenters = DataCenterCreation::with('ownerType')
    //         ->orderByDesc('created_at')
    //         ->get();
    //     return response()->json($dataCenters);
    // }


    public function index()
    {
        $dataCenters = DataCenterCreation::with('ownerType')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($dataCenters);
    }

    /**
     * Display the specified data center
     */
    public function show($id)
    {
        $dataCenter = DataCenterCreation::with('ownerType')->findOrFail($id);
        return response()->json($dataCenter);
    }

    /**
     * Update the specified data center
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'division' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'email_notification' => 'required|boolean',
            'sms_notification' => 'required|boolean',
            'owner_type_id' => 'required|exists:owner_types,id', // Changed from boolean to exists validation
            'status' => 'required|boolean'
        ]);

        $dataCenter = DataCenterCreation::findOrFail($id);
        $dataCenter->update($validated);

        return response()->json([
            'success' => true,
            'data' => $dataCenter,
            'message' => 'Data center updated successfully'
        ]);
    }

    public function destroy($id)
    {
        try {
            $dataCenter = DataCenterCreation::findOrFail($id);
            $dataCenter->delete();

            return response()->json(['success' => true, 'message' => 'Data center deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete data center'], 500);
        }
    }





//    public function getDataCenterDevices(Request $request, $dcId)
//    {
//        $user = $request->user();
//        $userType = $user->user_type;
//
//        // First verify data center access
//        if ($userType !== 'Super-Admin') {
//            $hasDcAccess = DB::table('data_center_user')
//                ->where('user_id', $user->id)
//                ->where('data_center_id', $dcId)
//                ->exists();
//
//            if (!$hasDcAccess) {
//                return response()->json(['message' => 'Unauthorized access to this data center'], 403);
//            }
//        }
//
//        // Now get devices based on user type
//        if ($userType === 'Super-Admin' || $userType === 'Admin' || $userType === 'Agent') {
//            $devices = DB::table('devices')
//                ->select('id as device_id', 'name as device_name')
//                ->where('data_center_id', $dcId)
//                ->get();
//        }
//        elseif ($userType === 'Client') {
//            $devices = DB::table('devices')
//                ->select('id as device_id', 'name as device_name')
//                ->where('data_center_id', $dcId)
//                ->whereIn('id', function($query) use ($user) {
//                    $query->select('device_id')
//                        ->from('device_user')
//                        ->where('user_id', $user->id);
//                })
//                ->get();
//        }
//        else {
//            return response()->json(['message' => 'Unauthorized user type'], 403);
//        }
//
//        return response()->json([
//            'devices' => $devices,
//            'data_center_id' => $dcId
//        ]);
//    }
}
