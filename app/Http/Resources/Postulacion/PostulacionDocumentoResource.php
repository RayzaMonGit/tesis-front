<?php

namespace App\Http\Resources\Postulacion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PostulacionDocumentoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'postulacion_id' => $this->postulacion_id,
            'nombre' => $this->nombre,
            'requisito_id' => $this->requisito_id,
            
            'archivo' => $this->archivo ? rtrim(env("APP_URL"), '/') . '/storage/' . ltrim($this->archivo, '/') : null,
            'es_requisito_ley' => $this->es_requisito_ley,
            'es_requisito_personalizado' => $this->es_requisito_personalizado,
            'created_at' => $this->created_at->toDateTimeString(),
            'seccion_id' => $this->seccion_id,
            'criterio_id' => $this->criterio_id,
            
        ];
    }
}
