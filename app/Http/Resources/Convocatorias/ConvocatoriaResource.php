<?php

namespace App\Http\Resources\Convocatorias;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Http\Resources\Formularios\FormularioEvaluacionResource;

class ConvocatoriaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->resource->id,
            "estado" => $this->resource->estado,
            "titulo" => $this->resource->titulo,
            "descripcion" => $this->resource->descripcion,
            "area" => $this->resource->area,
            "fecha_inicio" => $this->resource->fecha_inicio?Carbon::parse($this->resource->fecha_inicio)->format("Y/m/d"):null,
            "fecha_fin" => $this->resource->fecha_fin?Carbon::parse($this->resource->fecha_fin)->format("Y/m/d"):null,
            "plazas_disponibles" => $this->resource->plazas_disponibles,
            "sueldo_referencial" => $this->resource->sueldo_referencial,
            'documento' => $this->resource->documento ? env("APP_URL")."storage/".$this->resource->documento : NULL,
            "formulario_id" => $this->resource->formulario_id,
            "requisitos" => collect($this->resource->requisitos)->map(function($requisitos) {
    return [
        "id" => $requisitos->id,
        "descripcion" => $requisitos->descripcion,
        "tipo" => $requisitos->tipo,
        "req_sec" => $requisitos->req_sec,
        "id_convocatoria" => $requisitos->id_convocatoria,
    ];
}),
"requisitos_ley" => collect($this->resource->requisitosLey)->map(function($reqLey) {
    return [
        "id" => $reqLey->id,
        "descripcion" => $reqLey->descripcion,
        "num" => $reqLey->num,
        "req" => $reqLey->req, // obligatorio u opcional
    ];
}),
"evaluadores" => $this->evaluadores->map(function($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'surname' => $user->surname,
        'email' => $user->email,
        'avatar' => $user->avatar ? env("APP_URL") . 'storage/' . $user->avatar : null,
    ];
}),
//añadir e formulario
"formulario" => $this->whenLoaded('formulario', function () {
    return new FormularioEvaluacionResource($this->formulario);
}),




        ];
    }
}