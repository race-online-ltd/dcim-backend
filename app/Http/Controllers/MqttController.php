<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Bluerhinos\phpMQTT; // Make sure to import your phpMQTT library
use App\Events\SensorDataUpdated;

class MqttController extends Controller
{
    private $mqtt;
    private $latestData = [];

    public function __construct()
    {
        // Configure MQTT connection parameters
        $server = '182.48.80.230';
        $port = 1883;
        $username = 'test';
        $password = 'test';
        $client_id = 'laravelClient';

        // Initialize MQTT Client
        $this->mqtt = new phpMQTT($server, $port, $client_id);
        $this->mqtt->connect(true, NULL, $username, $password);
    }

    public function __constructListner()
    {
        $server = '182.48.80.230';
        $port = 1883;
        $username = 'test';
        $password = 'test';
        $client_id = 'laravelClient_' . uniqid();

        $this->mqtt = new phpMQTT($server, $port, $client_id);
        if (!$this->mqtt->connect(true, NULL, $username, $password)) {
            Log::error('Failed to connect to MQTT broker');
        } else {
            // Subscribe in background
            $this->subscribeToTopics();
        }
    }

    private function subscribeToTopicsListner()
    {
        $topics = [
            'dc_1/device_1/json' => ['qos' => 0, 'function' => [$this, 'messageReceived']]
        ];

        $this->mqtt->subscribe($topics, 0);

        // Run MQTT loop in background
        register_shutdown_function(function() {
            while ($this->mqtt->proc()) {
                // Keep processing messages
            }
            $this->mqtt->close();
        });
    }

    public function index(Request $request)
    {
        // Subscribe to MQTT only when accessing this route
        $this->mqtt->subscribe(['dc_1/device_1/json' => ['qos' => 0, 'function' => [$this, 'messageReceived']]]);
        $this->mqtt->proc();

        $sensorData = DB::table('sensor_data')->orderBy('created_at', 'desc')->get();
        Log::info('Sensor Data Retrieved:', ['data' => $sensorData]);

        return view('mqtt.index', ['sensorData' => $sensorData]);
    }

//    public function messageReceived($topic, $msg)
//    {
//        // Decode the incoming JSON message
//        $data = json_decode($msg, true);
//
//        // Example: Save the data to the database (assumes you have a model set up)
//        DB::table('sensor_data')->insert([
//            'temperature' => $data['temperature'],
//            'humidity' => $data['humidity'],
//            'speed' => $data['speed'],
//            'status' => $data['status'],
//            'uptime' => $data['uptime'],
//            'created_at' => now(),
//            'updated_at' => now(),
//        ]);
//
//        // Log the received message (optional)
//        Log::info("Received message on topic {$topic}: " . print_r($data, true));
//    }



    public function messageReceived($topic, $msg)
    {
        $data = json_decode($msg, true);
        DB::table('sensor_data')->insert([
            'temperature' => $data['temperature'],
            'humidity' => $data['humidity'],
            'speed' => $data['speed'],
            'status' => $data['status'],
            'uptime' => $data['uptime'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Broadcast the new sensor data
        broadcast(new SensorDataUpdated($data));
    }

    public function messageReceivedListner($topic, $msg)
    {
        $data = json_decode($msg, true);
        $this->latestData = $data;

        // Broadcast immediately without database storage
        broadcast(new \App\Events\SensorDataUpdated($data));

        Log::info("Received MQTT message", ['data' => $data]);
    }

    public function indexListner()
    {
        return view('sensor-data', [
            'initialData' => [
                'temperature' => '--',
                'humidity' => '--',
                'speed' => '--',
                'status' => 'offline',
                'uptime' => '--'
            ]
        ]);
    }


    public function showControlLed()
    {
        return view('control-led');
    }

    public function controlLed(Request $request)
    {
        $server = '182.48.80.230';
        $port = 1883;
        $username = 'test';
        $password = 'test';
        $client_id = 'laravelClientPub';

        $mqtt = new phpMQTT($server, $port, $client_id);
        if ($mqtt->connect(true, NULL, $username, $password)) {
            $message = $request->input('ledState'); // 1 for ON, 0 for OFF
            $mqtt->publish('dc_1/device_1/led_1', $message, 0);
            $mqtt->close();
            return response()->json(['status' => 'success', 'message' => 'LED state updated']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Could not connect to MQTT broker']);
        }
    }

    public function getSensorData()
    {

//        return 'here';
        $sensorData = DB::table('sensor_data')
            ->orderBy('created_at', 'desc')
            ->take(10) // Limit number of records to display
            ->get();

        return response()->json($sensorData);
    }

    public function showSensorData()
    {
        // Retrieve sensor data from the database
        $sensorData = DB::table('sensor_data')->orderBy('created_at', 'desc')->get();

        // Check if data is being retrieved
        if ($sensorData->isEmpty()) {
            // You can use dd() to debug
            dd('No data found in the sensor_data table.');
        }

        return view('sensor-data', ['sensorData' => $sensorData]);
    }



//    public function saveSensorData(Request $request)
//    {
//
////        return 'again';
//        // Insert the new sensor data into the database
//        $sensorData = DB::table('sensor_data')->insert([
//            'temperature' => $request->temperature,
//            'humidity' => $request->humidity,
//            'speed' => $request->speed,
//            'status' => $request->status,
//            'uptime' => $request->uptime,
//            'created_at' => now(),
//            'updated_at' => now(),
//        ]);
//
//        // Get all sensor data for the event
//        $allSensorData = DB::table('sensor_data')->orderBy('created_at', 'desc')->get();
//
//        // Dispatch the event with the latest sensor data
//        event(new \App\Events\SensorDataUpdated($allSensorData));
//    }


    public function saveSensorData(Request $request)
    {
        try {
//            dd('hi');
            // Insert the new sensor data into the database
            $sensorData = DB::table('sensor_data')->insert([
                'temperature' => $request->input('temperature'),
                'humidity' => $request->input('humidity'),
                'speed' => $request->input('speed'),
                'status' => $request->input('status'),
                'uptime' => $request->input('uptime'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Get all sensor data for the event
            $allSensorData = DB::table('sensor_data')->orderBy('created_at', 'desc')->get();

            // Dispatch the event with the latest sensor data
            event(new \App\Events\SensorDataUpdated($allSensorData));

            return response()->json(['message' => 'Data saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to save data', 'error' => $e->getMessage()], 500);
        }
    }

    //    public function showSensorData()
//    {
//        // Retrieve only the most recent sensor data
//        $sensorData = DB::table('sensor_data')->orderBy('created_at', 'desc')->first();
//
//        // Check if data is being retrieved
//        if (!$sensorData) {
//            dd('No data found in the sensor_data table.');
//        }
//
//        return view('sensor-data', ['sensorData' => $sensorData]);
//    }



//    public function showSensorData()
//    {
//        // Retrieve the most recent sensor data from the database
//        $sensorData = DB::table('sensor_data')->orderBy('created_at', 'desc')->first();
//
//        // Check if data is being retrieved
//        if (!$sensorData) {
//            // You can use dd() to debug
//            dd('No data found in the sensor_data table.');
//        }
//
//        return view('sensor-data', ['sensorData' => $sensorData]);
//    }



}
