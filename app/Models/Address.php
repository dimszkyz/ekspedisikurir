<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'locality',
        'address',
        'city',
        'state',
        'country',
        'landmark',
        'postal_code',
        'type',
        'isdefault'
    ];
}
