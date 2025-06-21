<?php

namespace App\Http\Resources\Formularios;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CriterioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'seccion_id' => $this->seccion_id,
            'nombre' => $this->nombre,
            //'puntaje' => $this->puntaje,
            //'max_items' => $this->max_items,
            //'orden' => $this->orden,
            'puntaje_por_item' => $this->puntaje_por_item,
            'puntaje_maximo' => $this->puntaje_maximo,
        ];
    }
}