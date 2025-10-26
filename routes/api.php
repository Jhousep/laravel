<?php

use Illuminate\Http\Request;
use App\Http\Middleware\IoTSafeAuth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceController;

// Rutas de la API
Route::get('/status', function () {
    return response()->json(['service' => 'IoTSafe API', 'status' => 'online']);
});

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware([IoTSafeAuth::class])->group(function () {
    Route::get('/devices', [DeviceController::class, 'index']);
    Route::get('/devices/{id}/data', [DeviceController::class, 'sensorData']);
    Route::post('/devices/{id}/ingest', [DeviceController::class, 'ingest']);
});
