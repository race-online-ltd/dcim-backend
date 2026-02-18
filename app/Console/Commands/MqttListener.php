<?php

namespace App\Console\Commands;

use App\Events\MQTTPublishEvent;
use App\Models\SensorRealTimeValue;
use App\Models\SensorLogValue;
use Illuminate\Console\Command;
use Bluerhinos\phpMQTT;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MqttListener extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Listen to MQTT topics';

//    public function handle()
//    {
//        $server = env('MQTT_HOST', '182.48.80.230');
//        $port = env('MQTT_PORT', 1883);
//        $username = env('MQTT_USERNAME', 'test');
//        $password = env('MQTT_PASSWORD', 'test');
//        $client_id = 'laravel_mqtt_listener_' . uniqid();
//
//        $mqtt = new phpMQTT($server, $port, $client_id);
//
//        if ($mqtt->connect(true, null, $username, $password)) {
//            $this->info("Connected to MQTT broker");
//
//            $topics = ['dc_1/device_1/json' => ['qos' => 0, 'function' => function($topic, $msg) {
//                $data = json_decode($msg, true);
//                event(new MQTTPublishEvent($data));
//                Log::info("mqtt event fire");
//                $this->info("Received: " . print_r($data, true));
//            }]];
//
////            $topics = [
////                'dc_1/device_1/json' => [
////                    'qos' => 0,
////                    'function' => function ($topic, $msg) {
////                        $data = json_decode($msg, true);
////                        event(new MQTTPublishEvent($data));
////                        Log::info("mqtt event fire");
////
////                        // Check and insert each sensor value
////                        foreach ($data['sensor_types'] as $sensorType) {
////                            foreach ($sensorType as $key => $sensorArray) {
////                                if (is_array($sensorArray)) {
////                                    foreach ($sensorArray as $sensor) {
////                                        if (isset($sensor['id']) && isset($sensor['val'])) {
////                                            // Insert into database
////                                            SensorRealTimeValue::create([
////                                                'sensor_id' => $sensor['id'],
////                                                'value' => $sensor['val'],
////                                            ]);
////
////                                            // Print to console
////                                            echo "Inserted Sensor ID: {$sensor['id']} with Value: {$sensor['val']}\n";
////                                        }
////                                    }
////                                }
////                            }
////                        }
////                    }
////                ]
////            ];
//
//
//
//            $mqtt->subscribe($topics, 0);
//
//            while ($mqtt->proc()) {
//                // Keep processing messages
//            }
//
//            $mqtt->close();
//        } else {
//            $this->error("Could not connect to MQTT broker");
//        }
//    }
//}

// last work is below one

    // public function handle()
    // {
    //     $server = env('MQTT_HOST', '182.48.80.230');
    //     $port = env('MQTT_PORT', 1883);
    //     $username = env('MQTT_USERNAME', 'test');
    //     $password = env('MQTT_PASSWORD', 'test');
    //     $client_id = 'laravel_mqtt_listener_' . uniqid();

    //     $mqtt = new phpMQTT($server, $port, $client_id);

    //     if ($mqtt->connect(true, null, $username, $password)) {
    //         $this->info("Connected to MQTT broker");

    //         $topicList = DB::table('device_lists as dl')
    //             ->select(DB::raw("dl.secret_key as topic"))
    //             ->pluck('topic')
    //             ->toArray();

    //         $this->info("Subscribing to topics: " . implode(', ', $topicList));

    //         $topics = [];

    //         foreach ($topicList as $topic) {
    //             $topics[$topic] = [
    //                 'qos' => 0,
    //                 'function' => function ($topic, $msg) {
    //                     $data = json_decode($msg, true);

    //                     event(new MQTTPublishEvent($data));

    //                     Log::info("MQTT Message Received on topic [$topic]:", $data);

    //                     echo "Received from $topic: " . print_r($data, true) . "\n";

    //                     if (isset($data['sensor_types'])) {
    //                         foreach ($data['sensor_types'] as $sensorType) {
    //                             foreach ($sensorType as $key => $sensorArray) {
    //                                 if (is_array($sensorArray)) {
    //                                     foreach ($sensorArray as $sensor) {
    //                                         if (isset($sensor['id']) && isset($sensor['val'])) {
    //                                             try {
    //                                                 DB::beginTransaction();

    //                                                 $existingSensor = SensorRealTimeValue::where('sensor_id', $sensor['id'])->first();

    //                                                 if ($existingSensor) {
                                                        
    //                                                     SensorLogValue::create([
    //                                                         'sensor_id' => $existingSensor->sensor_id,
    //                                                         'value' => $existingSensor->value,
    //                                                         'created_at' => now(),
    //                                                         'updated_at' => now()
    //                                                     ]);

    //                                                     $existingSensor->delete();

    //                                                     echo "Logged and deleted previous data for Sensor ID: {$sensor['id']}\n";
    //                                                 }

    //                                                 SensorRealTimeValue::create([
    //                                                     'sensor_id' => $sensor['id'],
    //                                                     'value' => $sensor['val'],
    //                                                     'received_at' => now(),
    //                                                     'topic' => $topic
    //                                                 ]);

    //                                                 echo "Inserted Sensor ID: {$sensor['id']} with Value: {$sensor['val']} from topic: $topic\n";

    //                                                 DB::commit();

    //                                             } catch (\Exception $e) {
                                                    
    //                                                 DB::rollBack();
    //                                                 Log::error("Failed to process sensor data: " . $e->getMessage());
    //                                                 echo "Error processing sensor data: " . $e->getMessage() . "\n";
    //                                             }
    //                                         }
    //                                     }
    //                                 }
    //                             }
    //                         }
    //                     }
    //                 }
    //             ];
    //         }

    //         // Subscribe to all topics
    //         $mqtt->subscribe($topics, 0);

    //         // Keep listening
    //         while ($mqtt->proc()) {
    //             // Process incoming messages
    //         }

    //         $mqtt->close();
    //     } else {
    //         $this->error("Could not connect to MQTT broker");
    //     }
    // }

// with corn job
    // public function handle()
    // {
    //     $server = env('MQTT_HOST', '182.48.80.230');
    //     $port = env('MQTT_PORT', 1883);
    //     $username = env('MQTT_USERNAME', 'test');
    //     $password = env('MQTT_PASSWORD', 'test');
    //     $client_id = 'laravel_mqtt_listener_' . uniqid();

    //     $mqtt = new phpMQTT($server, $port, $client_id);

    //     if ($mqtt->connect(true, null, $username, $password)) {
    //         $this->info("Connected to MQTT broker");

    //         $topicList = DB::table('device_lists as dl')
    //             ->select(DB::raw("dl.secret_key as topic"))
    //             ->pluck('topic')
    //             ->toArray();

    //         $this->info("Subscribing to topics: " . implode(', ', $topicList));

    //         $topics = [];

    //         foreach ($topicList as $topic) {
    //             $topics[$topic] = [
    //                 'qos' => 0,
    //                 'function' => function ($topic, $msg) {
    //                     $data = json_decode($msg, true);

    //                     event(new MQTTPublishEvent($data));

    //                     Log::info("MQTT Message Received on topic [$topic]:", $data);

    //                     echo "Received from $topic: " . print_r($data, true) . "\n";
                        
    //                     if (isset($data['device_id']) && isset($data['state'])) {
    //                         try {
    //                             $updated = \App\Models\DeviceList::where('id', $data['device_id'])
    //                                 ->update(['is_active' => $data['state']]);

    //                             if ($updated) {
    //                                 echo "✅ Device ID {$data['device_id']} is_active updated to: {$data['state']}\n";
    //                                 Log::info("Device ID {$data['device_id']} is_active updated to {$data['state']}");
    //                             } else {
    //                                 echo "⚠️ Device ID {$data['device_id']} not found in device_lists\n";
    //                                 Log::warning("Device ID {$data['device_id']} not found in device_lists");
    //                             }
    //                         } catch (\Exception $e) {
    //                             Log::error("Failed to update device is_active: " . $e->getMessage());
    //                             echo "❌ Error updating device is_active: " . $e->getMessage() . "\n";
    //                         }
    //                     }
                    

    //                     if (isset($data['sensor_types'])) {
    //                         foreach ($data['sensor_types'] as $sensorType) {
    //                             foreach ($sensorType as $key => $sensorArray) {
    //                                 if (is_array($sensorArray)) {
    //                                     foreach ($sensorArray as $sensor) {
    //                                         if (isset($sensor['id']) && isset($sensor['val'])) {
    //                                             try {
    //                                                 DB::beginTransaction();

    //                                                 $existingSensor = SensorRealTimeValue::where('sensor_id', $sensor['id'])->first();

    //                                                 if ($existingSensor) {
    //                                                     SensorLogValue::create([
    //                                                         'sensor_id' => $existingSensor->sensor_id,
    //                                                         'value' => $existingSensor->value,
    //                                                         'created_at' => now(),
    //                                                         'updated_at' => now()
    //                                                     ]);

    //                                                     $existingSensor->delete();

    //                                                     echo "Logged and deleted previous data for Sensor ID: {$sensor['id']}\n";
    //                                                 }

    //                                                 SensorRealTimeValue::create([
    //                                                     'sensor_id' => $sensor['id'],
    //                                                     'value' => $sensor['val'],
    //                                                     'received_at' => now(),
    //                                                     'topic' => $topic
    //                                                 ]);

    //                                                 echo "Inserted Sensor ID: {$sensor['id']} with Value: {$sensor['val']} from topic: $topic\n";

    //                                                 DB::commit();

    //                                             } catch (\Exception $e) {
    //                                                 DB::rollBack();
    //                                                 Log::error("Failed to process sensor data: " . $e->getMessage());
    //                                                 echo "Error processing sensor data: " . $e->getMessage() . "\n";
    //                                             }
    //                                         }
    //                                     }
    //                                 }
    //                             }
    //                         }
    //                     }
    //                 }
    //             ];
    //         }

    //         $mqtt->subscribe($topics, 0);

    //         while ($mqtt->proc()) {
    //             // Process incoming messages
    //         }

    //         $mqtt->close();
    //     } else {
    //         $this->error("Could not connect to MQTT broker");
    //     }
    // }


    

    public function handle()
    {
        $server = env('MQTT_HOST', '182.48.80.230');
        $port = env('MQTT_PORT', 1883);
        $username = env('MQTT_USERNAME', 'test');
        $password = env('MQTT_PASSWORD', 'test');
        $client_id = 'laravel_mqtt_listener_' . uniqid();

        $mqtt = new phpMQTT($server, $port, $client_id);

        if ($mqtt->connect(true, null, $username, $password)) {
            $this->info("Connected to MQTT broker");

            $topicList = DB::table('device_lists as dl')
                ->select(DB::raw("dl.secret_key as topic"))
                ->pluck('topic')
                ->toArray();

            $this->info("Subscribing to topics: " . implode(', ', $topicList));

            $topics = [];

            foreach ($topicList as $topic) {
                $topics[$topic] = [
                    'qos' => 0,
                    'function' => function ($topic, $msg) {
                        $data = json_decode($msg, true);

                        event(new MQTTPublishEvent($data));

                        Log::info("MQTT Message Received on topic [$topic]:", $data);

                        echo "Received from $topic: " . print_r($data, true) . "\n";

                        // ✅ Update device is_active from live MQTT state payload
                        // Payload example: {"data_type":"live","dc_id":1,"device_id":1,"state":1,...}
                        if (isset($data['device_id']) && isset($data['state'])) {
                            \App\Models\DeviceList::where('id', $data['device_id'])
                                ->update(['is_active' => $data['state']]);

                            echo "Updated Device ID {$data['device_id']} is_active = {$data['state']}\n";
                        }

                        if (isset($data['sensor_types'])) {
                            foreach ($data['sensor_types'] as $sensorType) {
                                foreach ($sensorType as $key => $sensorArray) {
                                    if (is_array($sensorArray)) {
                                        foreach ($sensorArray as $sensor) {
                                            if (isset($sensor['id']) && isset($sensor['val'])) {
                                                try {
                                                    DB::beginTransaction();

                                                    $existingSensor = SensorRealTimeValue::where('sensor_id', $sensor['id'])->first();

                                                    if ($existingSensor) {
                                                        SensorLogValue::create([
                                                            'sensor_id' => $existingSensor->sensor_id,
                                                            'value' => $existingSensor->value,
                                                            'created_at' => now(),
                                                            'updated_at' => now()
                                                        ]);

                                                        $existingSensor->delete();

                                                        echo "Logged and deleted previous data for Sensor ID: {$sensor['id']}\n";
                                                    }

                                                    SensorRealTimeValue::create([
                                                        'sensor_id' => $sensor['id'],
                                                        'value' => $sensor['val'],
                                                        'received_at' => now(),
                                                        'topic' => $topic
                                                    ]);

                                                    echo "Inserted Sensor ID: {$sensor['id']} with Value: {$sensor['val']} from topic: $topic\n";

                                                    DB::commit();

                                                } catch (\Exception $e) {
                                                    DB::rollBack();
                                                    Log::error("Failed to process sensor data: " . $e->getMessage());
                                                    echo "Error processing sensor data: " . $e->getMessage() . "\n";
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                ];
            }

            // Subscribe to all topics
            $mqtt->subscribe($topics, 0);

            // Keep listening
            while ($mqtt->proc()) {
                // Process incoming messages
            }

            $mqtt->close();
        } else {
            $this->error("Could not connect to MQTT broker");
        }
    }
    
}
