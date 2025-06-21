<?php

namespace App\Http\Resources\Formularios;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormularioEvaluacionResource extends JsonResource
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
        'nombre' => $this->nombre,
        'descripcion' => $this->descripcion,
        //'resolucion' => $this->resolucion,
        'puntaje_total'=> $this->puntaje_total,
        'secciones' => SeccionResource::collection($this->whenLoaded('secciones')),
    ];
}
}
