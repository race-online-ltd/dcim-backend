<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceList extends Model
{
    use HasFactory;

    protected $table = 'device_lists';
    protected $fillable = [
        'name', 'data_center_id','location', 'secret_key','control_topic','status','is_active','ip','slave_id','type','model_id'
    ];

    public function dataCenter(): BelongsTo
    {
        return $this->belongsTo(DataCenterCreation::class, 'data_center_id');
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(UpsModel::class, 'model_id');
    }

}

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Relations\BelongsTo;

// class DeviceList extends Model
// {
//     use HasFactory;

//     protected $table = 'device_lists';
//     protected $fillable = [
//         'name', 'data_center_id','location', 'secret_key','control_topic','status','is_active',
//     ];

//     public function dataCenter(): BelongsTo
//     {
//         return $this->belongsTo(DataCenterCreation::class, 'data_center_id');
//     }

// }
