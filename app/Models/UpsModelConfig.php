<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpsModelConfig extends Model
{
    protected $table = 'ups_model_config';

    protected $fillable = [
        'ups_id',
        'model_id',
        'datacenter_id',
    ];

    protected $casts = [
        'ups_id'        => 'integer',
        'model_id'      => 'integer',
        'datacenter_id' => 'integer',
    ];

    public function ups(): BelongsTo
    {
        return $this->belongsTo(Ups::class, 'ups_id');
    }

    public function upsModel(): BelongsTo
    {
        return $this->belongsTo(UpsModel::class, 'model_id');
    }

    public function dataCenterCreation(): BelongsTo
    {
        return $this->belongsTo(DataCenterCreation::class, 'datacenter_id');
    }
}