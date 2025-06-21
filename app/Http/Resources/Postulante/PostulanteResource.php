<?php

namespace App\Http\Resources\postulante;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostulanteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->resource->id,
            'user_id'=>$this->resource->user_id,
            'convocatoria_id'=>$this->resource->convocatoria_id,
            'grado_academico'=>$this->resource->grado_academico,
            'experiencia_años'=>$this->resource->experiencia_años,
            'fecha_nacimiento'=>$this->resource->fecha_nacimiento,
            'user'=>[
                'id'=>$this->resource->id,
            'name'=>$this->resource->name,
            'surname'=>$this->resource->surname,
            "full_name" => $this->resource->name.' '.$this->resource->surname,
            'email' => $this->resource->email,
            'avatar' => $this->resource->avatar ? env("APP_URL")."storage/".$this->resource->avatar : NULL,
            'telefono'=>$this->resource->telefono,
            'designacion'=>$this->resource->designacion,
            'gender'=>$this->resource->gender,
            'tipo_doc'=>$this->resource->tipo_doc,
            'n_doc'=>$this->resource->n_doc,
            ]
         

        ];
    }
}
