<?php

namespace App\Http\Resources\Comision;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VistoBuenoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_comision' => $this->id_comision,
            'id_documento' => $this->id_documento,
            'estado' => $this->estado,
            'observacion' => $this->observacion,
            'comision' => [
                'id' => $this->comision->id ?? null,
                'name' => $this->comision->name ?? null,
                'surname' => $this->comision->surname ?? null,
            ],
        ];
    }
}
