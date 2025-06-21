<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluacionRequest extends FormRequest {
    public function rules() {
        return [
            'formulario_id' => 'required|exists:formularios_evaluacion,id',
            'respuestas' => 'required|array',
            'respuestas.*.criterio_id' => 'required|exists:criterios_formulario,id',
            'respuestas.*.valor' => 'required|numeric',
        ];
    }
}