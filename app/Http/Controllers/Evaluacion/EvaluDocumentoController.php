<?php

namespace App\Http\Controllers\Evaluacion;

use App\Http\Controllers\Controller;
use App\Models\Evaluacion\Evaluacion;
use App\Models\Evaluacion\EvaluacionRequisito;
use App\Models\Postulacion\PostulacionDocumento;
use Illuminate\Http\Request;

class EvaluDocumentoController extends Controller
{
    public function store(Request $request, $evaluacionId)
    {
        try {
            $validated = $this->validate($request, [
                'postulacion_documento_id' => 'required|exists:postulacion_documentos,id',
                'estado' => 'required|in:aprobado,rechazado,pendiente',
                'puntaje' => 'required|numeric|min:0',
                'comentarios' => 'nullable|string|max:500'
            ]);

            $evaluacion = Evaluacion::findOrFail($evaluacionId);
            $documento = PostulacionDocumento::findOrFail($validated['postulacion_documento_id']);

            $evalDocumento = $evaluacion->documentos()->create([
                'postulacion_documento_id' => $documento->id,
                'estado' => $validated['estado'],
                'puntaje' => $validated['puntaje'],
                'comentarios' => $validated['comentarios'] ?? null
            ]);

            $this->actualizarPuntajeTotal($evaluacion);

            return response()->json([
                'success' => true,
                'data' => $evalDocumento->load('documento')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al evaluar documento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $this->validate($request, [
                'estado' => 'required|in:aprobado,rechazado,pendiente',
                'puntaje' => 'required|numeric|min:0',
                'comentarios' => 'nullable|string|max:500'
            ]);

            $evalDocumento = EvaluacionDocumento::with('evaluacion')->findOrFail($id);
            $evalDocumento->update($validated);

            $this->actualizarPuntajeTotal($evalDocumento->evaluacion);

            return response()->json([
                'success' => true,
                'data' => $evalDocumento->fresh()->load('documento')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar evaluación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $evalDocumento = EvaluacionDocumento::with('evaluacion')->findOrFail($id);
            $evaluacion = $evalDocumento->evaluacion;
            $evalDocumento->delete();

            $this->actualizarPuntajeTotal($evaluacion);

            return response()->json([
                'success' => true,
                'message' => 'Documento de evaluación eliminado'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar evaluación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function actualizarPuntajeTotal(Evaluacion $evaluacion)
    {
        $evaluacion->update([
            'puntaje_total' => $evaluacion->documentos()->sum('puntaje')
        ]);
    }
}