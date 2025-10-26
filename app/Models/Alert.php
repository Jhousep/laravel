<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    //campos permitidos para el guardado o edición de un registro
    protected $fillable = [
        'device_id',
        'type',
        'message',
        'read',
    ];

    //definimos una relación del control de inventario Iot
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
