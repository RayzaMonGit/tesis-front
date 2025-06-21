<?php

namespace App\Http\Requests\Postulacion;
use App\Models\Postulacion;


use Illuminate\Foundation\Http\FormRequest;

class StorePostulacionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'postulante_id' => 'required|exists:postulantes,id',
            'convocatoria_id' => 'required|exists:convocatorias,id',
        ];
    }
    public function withValidator($validator)
{
    $validator->after(function ($validator) {
        if (Postulacion::where('postulante_id', $this->postulante_id)
            ->where('convocatoria_id', $this->convocatoria_id)->exists()) {
            $validator->errors()->add('convocatoria_id', 'Ya estás postulado a esta convocatoria.');
        }
    });
}

}
