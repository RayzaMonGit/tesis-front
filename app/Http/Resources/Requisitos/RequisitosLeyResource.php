<?php

namespace App\Http\Resources\Requisitos;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequisitosLeyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'descripcion' => $this->descripcion,
            'num'         => $this->num,
            'req'         => $this->req,
        ];
    }
}
