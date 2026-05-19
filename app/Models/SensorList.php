<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorList extends Model
{
    use HasFactory;

    protected $table = 'sensor_lists';


    protected $fillable = [
        'data_center_id',
        'device_id',
        'sensor_type_list_id',
        'unique_id',
        'trigger_type_id',
        'sound_status',
        'blink_status',
        'sensor_name',
        'location',
        'model_id',
        'ip_address',
        'register_address',
        'parameter_name',
        'multiplication_factor',
        'unit',
        'status',
        'timestamp'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function dataCenter()
    {
        return $this->belongsTo(DataCenterCreation::class, 'data_center_id');
    }

    public function device()
    {
        return $this->belongsTo(DeviceList::class, 'device_id');
    }

    public function sensorType()
    {
        return $this->belongsTo(SensorTypeList::class, 'sensor_type_list_id');
    }

    public function triggerType()
    {
        return $this->belongsTo(TriggerType::class, 'trigger_type_id');
    }

    public function model()
    {
        return $this->belongsTo(UpsModel::class, 'model_id');
    }

}
