<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpsModel extends Model
{
    protected $table = 'ups_models';

    protected $fillable = [
        'name',
        'protocol',
    ];
}