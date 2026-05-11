<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ups extends Model
{
    protected $table = 'ups';

    protected $fillable = [
        'name',
        'ip',
        'slave_id',
    ];

    protected $casts = [
        'slave_id' => 'integer',
    ];
}