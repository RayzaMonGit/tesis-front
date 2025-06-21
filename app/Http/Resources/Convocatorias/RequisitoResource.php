<?php

namespace App\Http\Resources\Convocatorias;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequisitoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "descripcion" => $this->descripcion,
            "tipo" => $this->tipo,
            "req_sec"=>$this->req_sec,
            "id_convocatoria" => $this->id_convocatoria,
        ];
    }
}

