<?php

namespace App\Http\Resources\Formularios;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeccionResource  extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'formulario_id' => $this->formulario_id,
            'titulo' => $this->titulo,
            'puntaje_max' => $this->puntaje_max,
            'orden' => $this->orden,
            'criterios' => CriterioResource::collection($this->whenLoaded('criterios')),
        ];
    }
}
