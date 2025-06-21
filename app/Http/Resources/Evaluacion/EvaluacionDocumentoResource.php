<?php
namespace App\Http\Resources\Evaluacion;

use Illuminate\Http\Resources\Json\JsonResource;

class EvaluacionDocumentoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'documento_id' => $this->postulacion_documento_id,
            'nombre_documento' => $this->documento->nombre,
            'url' => $this->documento && $this->documento->url
    ? rtrim(env("APP_URL"), '/') . '/storage/' . ltrim($this->documento->url, '/')
    : null,
            'estado' => $this->estado,
            'puntaje' => (float) $this->puntaje,
            'comentarios' => $this->comentarios
        ];
    }
}