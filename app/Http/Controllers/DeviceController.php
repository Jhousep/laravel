<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Device;
use App\Models\SensorData;
use Illuminate\Http\Request;
use App\Services\FuelPredictor;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
    // Lista dispositivos del usuario autenticado
    public function index(Request $request)
    {
        $user = $request->user; // Inyectado por middleware IoTSafeAuth
        $devices = $user->devices()->get();

        return response()->json($devices);
    }

    // Consulta datos de sensores de un dispositivo específico
    public function sensorData(Request $request, $id)
    {
        $user = $request->user;

        $device = $user->devices()->find($id);
        if (!$device) {
            return response()->json(['error' => 'Dispositivo no encontrado'], 404);
        }

        $data = $device->sensorData()->orderBy('recorded_at', 'desc')->get();

        return response()->json($data);
    }

    // Ingesta de datos desde un dispositivo IoT
    public function ingest(Request $request, $id)
    {
        $device = Device::find($id);
        if (!$device || !$device->status) {
            return response()->json(['error' => 'Dispositivo inválido o inactivo'], 400);
        }

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'fuel_level' => 'required|numeric',
            'temperature' => 'nullable|numeric',
            'speed' => 'nullable|numeric',
            'recorded_at' => 'required|date',
        ]);

        // Guardar el registro de sensor
        $sensor = SensorData::create([
            'device_id' => $device->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'fuel_level' => $request->fuel_level,
            'temperature' => $request->temperature,
            'speed' => $request->speed,
            'recorded_at' => $request->recorded_at,
        ]);

        // ---------------------------
        // CALCULO DE AUTONOMÍA
        // ---------------------------
        $avgConsumption = 5; // litros/hora, ejemplo, se puede mejorar con historial real
        $autonomy = FuelPredictor::calculateAutonomy($sensor->fuel_level, $avgConsumption);
        $alert = FuelPredictor::checkAlert($autonomy);

        // Si hay alerta
        if ($alert) {
            Alert::create([
                'device_id' => $device->id,
                'type' => 'low_fuel',
                'message' => "Autonomía baja: menos de 1 hora de combustible.",
            ]);
        }

        return response()->json([
            'message' => 'Datos ingresados correctamente',
            'data' => $sensor,
            'autonomy_hours' => $autonomy,
            'alert' => $alert,
        ]);
    }
}
