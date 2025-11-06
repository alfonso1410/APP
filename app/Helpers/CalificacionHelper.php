<?php

namespace App\Helpers;

/**
 * Clase Helper para lógicas de negocio relacionadas con Calificaciones.
 */
class CalificacionHelper
{
    public static function convertirANivelPreescolar($calificacionNumerica): string
    {
        // Si la calificación es nula, vacía o no es un número, devolvemos un string vacío.
        if (!is_numeric($calificacionNumerica) || $calificacionNumerica === null || $calificacionNumerica === '') {
            // Se ve mejor una celda vacía que 'NA' si no hay calificación.
            return ''; 
        }

        // 1. Redondear al entero más cercano.
        $cal = round((float)$calificacionNumerica);

        // 2. Aplicar el mapeo de la tabla
        if ($cal == 10) {
            return 'E'; // Excelente
        }
        if ($cal == 9) {
            return 'MB'; // Muy Bien
        }
        if ($cal == 8) {
            return 'B'; // Regular
        }
        if ($cal == 7 || $cal == 6) {
            return 'R'; // Suficiente
        }
        // 5 o menos es No Acreditado
        return 'NA';
    }
}