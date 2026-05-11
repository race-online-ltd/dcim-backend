<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegisterAddress extends Model
{
    protected $table = 'register_addresses';

    protected $fillable = [
        'name',
    ];
}