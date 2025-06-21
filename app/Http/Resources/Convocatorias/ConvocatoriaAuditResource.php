<?php

namespace App\Http\Resources\Convocatorias;

use Illuminate\Http\Resources\Json\JsonResource;

class ConvocatoriaAuditResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'convocatoria_id' => $this->convocatoria_id,
            'user_id' => $this->user_id,
            'usuario' => $this->user ? $this->user->name . ' ' . $this->user->surname : null,
            'accion' => $this->accion,
            'cambios' => $this->cambios,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}