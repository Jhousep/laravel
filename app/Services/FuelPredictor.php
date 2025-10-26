<?php

namespace App\Services;

use App\Models\SensorData;

class FuelPredictor
{
    /**
     * Calcula la autonomía restante en horas según nivel de combustible y consumo promedio.
     *
     * @param float $fuelLevel Porcentaje actual 0-100
     * @param float $avgConsumption Consumo promedio litros/hora
     * @return float Horas restantes
     */
    public static function calculateAutonomy(float $fuelLevel, float $avgConsumption): float
    {
        if ($avgConsumption <= 0) return INF; // Evita división por cero

        // Suponiendo que fuelLevel está en porcentaje de un tanque completo
        return ($fuelLevel / 100) * 1 / $avgConsumption; // Devuelve horas restantes
    }

    /**
     * Revisa si se debe activar alerta.
     *
     * @param float $autonomy Horas restantes
     * @param float $threshold Umbral para alerta (1 hora)
     * @return bool
     */
    public static function checkAlert(float $autonomy, float $threshold = 1.0): bool
    {
        return $autonomy < $threshold;
    }
}
