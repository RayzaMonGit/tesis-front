<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
class UserResource extends JsonResource
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
            'name'=>$this->resource->name,
            'surname'=>$this->resource->surname,
            "full_name" => $this->resource->name.' '.$this->resource->surname,
            'email' => $this->resource->email,
            'role_id' => $this->resource->role_id,
            "role" => [
                "name" => $this->resource->role->name,
            ],
            "role_name" => $this->resource->role->name,
                //http://127.0.0.1:8000/storage/calamardo.png
            'avatar' => $this->resource->avatar ? rtrim(env("APP_URL"), '/') . '/storage/' . $this->resource->avatar : NULL,
            'telefono'=>$this->resource->telefono,
            'designacion'=>$this->resource->designacion,
            'gender'=>$this->resource->gender,
            'tipo_doc'=>$this->resource->tipo_doc,
            'n_doc'=>$this->resource->n_doc,
            
           'grado_academico' => optional($this->postulante)->grado_academico,
'experiencia_años' => optional($this->postulante)->experiencia_años,

            ];
    }
}
