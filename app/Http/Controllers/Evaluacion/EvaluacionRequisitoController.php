<?php
namespace App\Http\Controllers\Evaluacion;

use App\Http\Controllers\Controller;
use App\Models\Evaluacion\EvaluacionRequisito;
use App\Http\Resources\Evaluacion\EvaluacionRequisitoResource;
use Illuminate\Http\Request;

class EvaluacionRequisitoController extends Controller
{
    // Listar todos los requisitos evaluados de una evaluación
    public function index($evaluacionId)
    {
        $requisitos = EvaluacionRequisito::where('evaluacion_id', $evaluacionId)->get();
        return EvaluacionRequisitoResource::collection($requisitos);
    }

    // Guardar o actualizar (upsert) un requisito evaluado
    public function store(Request $request, $evaluacionId)
    {
        $data = $request->validate([
            'requisito_id' => 'required|exists:postulacion_documentos,requisito_id',
            'estado' => 'string|max:30',
            'comentarios' => 'nullable|string|max:255',
            'es_requisito_ley' => 'boolean'
        ]);

        $requisito = EvaluacionRequisito::updateOrCreate(
            [
                'evaluacion_id' => $evaluacionId,
                'requisito_id' => $data['requisito_id'],
            ],
            array_merge($data, ['evaluacion_id' => $evaluacionId])
        );

        return new EvaluacionRequisitoResource($requisito);
    }

    // Actualizar un requisito evaluado
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'estado' => 'required|string|max:30',
            'comentarios' => 'nullable|string|max:255',
            'es_requisito_ley' => 'boolean'
        ]);

        $requisito = EvaluacionRequisito::findOrFail($id);
        $requisito->update($data);

        return new EvaluacionRequisitoResource($requisito);
    }

    // Eliminar un requisito evaluado
    public function destroy($id)
    {
        $requisito = EvaluacionRequisito::findOrFail($id);
        $requisito->delete();

        return response()->json(['success' => true]);
    }
}