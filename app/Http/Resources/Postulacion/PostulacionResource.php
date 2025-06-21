<?php

namespace App\Http\Resources\Postulacion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Postulante\PostulanteResource; // Importa el namespace correcto
use App\Http\Resources\Convocatorias\ConvocatoriaResource;
use App\Http\Resources\Postulante\PostulanteCollection;
use App\Http\Resources\Convocatorias\ConvocatoriaCollection;





class PostulacionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'estado' => $this->estado,
            'postulante' => new PostulanteResource($this->whenLoaded('postulante')),
            'convocatoria' => new ConvocatoriaResource($this->whenLoaded('convocatoria')),
            'created_at' => $this->created_at,
            'nota_preliminar'=> $this->nota_preliminar,
        ];
    }
}
