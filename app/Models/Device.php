<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    //campos permitidos para el guardado o edición de un registro
    protected $fillable = [
        'imei',
        'masked_id',
        'user_id',
        'status',
    ];

    //definimos la relación con la entidad user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //definimos la relación para la ingesta de datos de sensores
    public function sensorData()
    {
        return $this->hasMany(SensorData::class);
    }

}
