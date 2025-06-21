<?php
namespace App\Http\Resources\Evaluacion;

use Illuminate\Http\Resources\Json\JsonResource;

class EvaluacionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'postulacion_id' => $this->postulacion_id,
            'evaluador' => $this->evaluador->name,
            'puntaje_total' => (float) $this->puntaje_total,
            'estado' => $this->estado,
            'comentarios' => $this->comentarios,
            'fecha' => $this->created_at->format('d/m/Y H:i'),
            'documentos' => EvaluacionDocumentoResource::collection($this->whenLoaded('documentos'))
        ];
    }
}