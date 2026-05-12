<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelWiseAddressMapping extends Model
{
    protected $table = 'model_wise_address_mapping';

    protected $fillable = [
        'model_id',
        'address_id',
    ];

    protected $casts = [
        'model_id'   => 'integer',
        'address_id' => 'integer',
    ];

    /**
     * The UPS model this mapping belongs to.
     */
    public function upsModel(): BelongsTo
    {
        return $this->belongsTo(UpsModel::class, 'model_id');
    }

    /**
     * The register address this mapping belongs to.
     */
    public function registerAddress(): BelongsTo
    {
        return $this->belongsTo(RegisterAddress::class, 'address_id');
    }
}