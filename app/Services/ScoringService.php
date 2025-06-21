<?php
namespace App\Services;


use App\Models\Evaluacion; // Asume que guarda las respuestas

class ScoringService {
    public function calcularPuntajeSeccion($seccion, $respuestas) {
        $puntajeSeccion = 0;
        $puntajeMaxSeccion = $seccion->puntaje_max;

        foreach ($seccion->criterios as $criterio) {
            $respuesta = $respuestas->where('criterio_id', $criterio->id)->first();
            $puntajeCriterio = $this->calcularPuntajeCriterio($criterio, $respuesta);
            
            // Validación: No exceder el puntaje máximo de la sección
            if (($puntajeSeccion + $puntajeCriterio) > $puntajeMaxSeccion) {
                $puntajeCriterio = $puntajeMaxSeccion - $puntajeSeccion; // Ajustar al límite
            }
            
            $puntajeSeccion += $puntajeCriterio;
        }

        return [
            'puntaje' => $puntajeSeccion,
            'maximo' => $puntajeMaxSeccion,
            'porcentaje' => ($puntajeMaxSeccion > 0) ? round(($puntajeSeccion / $puntajeMaxSeccion) * 100, 2) : 0
        ];
    }

    private function calcularPuntajeCriterio($criterio, $respuesta) {
        if (!$respuesta) return 0;

        $valor = $respuesta->valor; // Asume que es numérico o un índice de opción

        // Si el criterio tiene un puntaje máximo definido
        if (!is_null($criterio->puntaje_maximo)) {
            return min($valor * $criterio->puntaje_por_item, $criterio->puntaje_maximo);
        } else {
            return $valor * $criterio->puntaje_por_item;
        }
    }
}