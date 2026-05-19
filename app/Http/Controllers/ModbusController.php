<?php

namespace App\Http\Controllers;

use App\Services\ModbusService;
use Illuminate\Support\Facades\DB;

class ModbusController extends Controller
{
    // public function index(ModbusService $modbus)
    // {
    //     // Step 1: Get all devices with IP and model_id
    //     $devices = DB::table('device_lists as dl')
    //         ->select('dl.ip', 'dl.slave_id', 'dl.model_id')
    //         ->whereNotNull('dl.ip')
    //         ->get();

    //     if ($devices->isEmpty()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'No devices found with a valid IP address.'
    //         ], 404);
    //     }

    //     // Step 2: Get all model -> address mappings
    //     $addressMappings = DB::table('model_wise_address_mapping as mp')
    //         ->select('mp.model_id', 'mp.address_id')
    //         ->get()
    //         ->groupBy('model_id'); // group by model_id for easy lookup

    //     $results = [];

    //     // Step 3: Loop each device
    //     foreach ($devices as $device) {
    //         $modelId  = $device->model_id;
    //         $ip       = $device->ip;
    //         $slaveId  = $device->slave_id;

    //         // Skip if no address mapping found for this model
    //         if (!isset($addressMappings[$modelId])) {
    //             $results[] = [
    //                 'ip'       => $ip,
    //                 'slave_id' => $slaveId,
    //                 'model_id' => $modelId,
    //                 'error'    => true,
    //                 'message'  => "No address mapping found for model_id: {$modelId}"
    //             ];
    //             continue;
    //         }

    //         $addresses = $addressMappings[$modelId];
    //         $deviceReadings = [];

    //         // Step 4: For each address mapped to this model, read the register
    //         foreach ($addresses as $mapping) {
    //             $addressId = $mapping->address_id;

    //             $reading = $modbus->readHoldingRegistersFromDevice(
    //                 $ip,
    //                 $slaveId,
    //                 $addressId,
    //                 1
    //             );

    //             $deviceReadings[] = [
    //                 'address_id' => $addressId,
    //                 'result'     => $reading
    //             ];
    //         }

    //         $results[] = [
    //             'ip'       => $ip,
    //             'slave_id' => $slaveId,
    //             'model_id' => $modelId,
    //             'readings' => $deviceReadings
    //         ];
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data'    => $results
    //     ]);
    // }

    public function index(ModbusService $modbus)
    {
        // Step 1: Get all devices with IP and model_id
        $devices = DB::table('device_lists as dl')
            ->select('dl.id as device_id', 'dl.ip', 'dl.slave_id', 'dl.model_id')
            ->whereNotNull('dl.ip')
            ->get();

        if ($devices->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No devices found with a valid IP address.'
            ], 404);
        }

        // Step 2: Get all model -> address mappings
        $addressMappings = DB::table('model_wise_address_mapping as mp')
            ->select('mp.model_id', 'mp.address_id')
            ->get()
            ->groupBy('model_id');

        $results = [];

        // Step 3: Loop each device
        foreach ($devices as $device) {
            $modelId  = $device->model_id;
            $deviceId = $device->device_id;
            $ip       = $device->ip;
            $slaveId  = $device->slave_id;

            // Step 4: Get active sensor_lists for this device and model
            $sensorLists = DB::table('sensor_lists as sl')
                ->select('sl.id', 'sl.register_address', 'sl.model_id', 'sl.multiplication_factor', 'sl.unit')
                ->where('sl.status', 1)
                ->where('sl.device_id', $deviceId)
                ->where('sl.model_id', $modelId)
                ->get()
                ->keyBy('register_address'); // key by register_address for easy lookup

            // Skip if no address mapping found for this model
            if (!isset($addressMappings[$modelId])) {
                $results[] = [
                    'ip'       => $ip,
                    'slave_id' => $slaveId,
                    'model_id' => $modelId,
                    'error'    => true,
                    'message'  => "No address mapping found for model_id: {$modelId}"
                ];
                continue;
            }

            $addresses      = $addressMappings[$modelId];
            $deviceReadings = [];

            // Step 5: For each address mapped to this model, read the register
            foreach ($addresses as $mapping) {
                $addressId = $mapping->address_id;

                $reading = $modbus->readHoldingRegistersFromDevice(
                    $ip,
                    $slaveId,
                    $addressId,
                    1
                );

                // Step 6: Apply multiplication_factor and unit if sensor exists for this address
                $rawValue        = null;
                $computedValue   = null;
                $unit            = null;
                $sensorId        = null;
                $multiplyFactor  = null;

                if (!$reading['error']) {
                    $rawData  = $reading['data'];

                    // Combine the two bytes: index 0 (high byte) and index 1 (low byte)
                    $rawValue = isset($rawData[0], $rawData[1])
                        ? ($rawData[0] << 8) | $rawData[1]
                        : null;

                    // Check if this address has a sensor with multiplication factor
                    if (isset($sensorLists[$addressId])) {
                        $sensor         = $sensorLists[$addressId];
                        $sensorId       = $sensor->id;
                        $multiplyFactor = $sensor->multiplication_factor;
                        $unit           = $sensor->unit;

                        $computedValue = $rawValue !== null && $multiplyFactor !== null
                            ? round($rawValue * $multiplyFactor, 4)
                            : $rawValue;
                    } else {
                        // No sensor mapping for this address, return raw value as-is
                        $computedValue = $rawValue;
                    }
                }

                $deviceReadings[] = [
                    'address_id'          => $addressId,
                    'sensor_id'           => $sensorId,
                    'raw_value'           => $rawValue,
                    'multiplication_factor' => $multiplyFactor,
                    'computed_value'      => $computedValue,
                    'unit'                => $unit,
                    'error'               => $reading['error'],
                    'message'             => $reading['error'] ? $reading['message'] : null,
                ];
            }

            $results[] = [
                'ip'       => $ip,
                'slave_id' => $slaveId,
                'model_id' => $modelId,
                'readings' => $deviceReadings
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $results
        ]);
    }
}