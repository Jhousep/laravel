<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
     //campos permitidos para el guardado o edición de un registro
    protected $fillable = [
        'device_id',
        'latitude',
        'longitude',
        'fuel_level',
        'temperature',
        'speed',
        'recorded_at',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
