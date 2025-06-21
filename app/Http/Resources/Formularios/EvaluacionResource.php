<?php

namespace App\Http\Resources\Formularios;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluacionResource extends JsonResource {
    public function toArray($request) {
        return [
            'id' => $this->id,
            'puntaje_total' => $this->puntaje_total,
            'porcentaje' => $this->porcentaje,
            'secciones' => $this->secciones // Si tienes relación en el modelo
        ];
    }
}