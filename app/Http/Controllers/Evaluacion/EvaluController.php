<?php
namespace App\Http\Controllers\Evaluacion;

use App\Http\Controllers\Controller;
use App\Models\Evaluacion\Evaluacion;
use App\Models\Postulacion\Postulacion;
use Illuminate\Http\Request;
use App\Models\Evaluacion\EvaluacionRequisito;
use App\Models\Evaluacion\EvaluacionDocumento;
use Illuminate\Support\Facades\DB;

use App\Models\Convocatorias\Requisitos;
use App\Models\Convocatorias\RequisitosLey;
use App\Models\Postulacion\PostulacionDocumento;

use App\Http\Resources\Postulante\PostulanteResource;



//use App\Models\Postulacion\PostulacionDocumento;
use Illuminate\Support\Facades\Auth;


class EvaluController extends Controller
{
    
    /**
     * Crear o actualizar una evaluación completa
     */
    public function store(Request $request, $postulacionId)
    {
        $request->validate([
            'requisitos' => 'required|array',
            'requisitos.*.requisito_id' => 'required|integer',
            'requisitos.*.es_requisito_ley' => 'required|boolean',
            'requisitos.*.estado' => 'required|in:aprobado,rechazado,pendiente',
            'requisitos.*.comentarios' => 'nullable|string|max:500',
            'requisitos.*.postulacion_documento_id' => 'nullable|integer',
            'documentos' => 'nullable|array',
            'documentos.*.postulacion_documento_id' => 'required|integer',
            'documentos.*.estado' => 'required|in:aprobado,rechazado,pendiente',
            'documentos.*.puntaje' => 'required|numeric|min:0',
            'documentos.*.comentarios' => 'nullable|string|max:500',
            'comentarios_generales' => 'nullable|string|max:1000',
            'finalizar' => 'boolean'
        ]);

        DB::beginTransaction();

        try {
            // Verificar que la postulación existe
            $postulacion = Postulacion::findOrFail($postulacionId);
            
            // Buscar evaluación existente del evaluador actual
            $evaluacion = Evaluacion::where('postulacion_id', $postulacionId)
                ->where('evaluador_id', Auth::id())
                ->first();

            // Crear o actualizar evaluación principal
            if (!$evaluacion) {
                $evaluacion = Evaluacion::create([
                    'postulacion_id' => $postulacionId,
                    'evaluador_id' => Auth::id(),
                    'puntaje_total' => 0,
                    'comentarios_generales' => $request->comentarios_generales ?? '',
                    'estado' => $request->finalizar ? 'finalizada' : 'borrador'
                ]);
            } else {
                $evaluacion->update([
                    'comentarios_generales' => $request->comentarios_generales ?? '',
                    'estado' => $request->finalizar ? 'finalizada' : 'borrador'
                ]);
            }

            // Procesar requisitos
            $this->procesarRequisitos($evaluacion, $request->requisitos, $postulacionId);

            // Procesar documentos del formulario/CV
            if (!empty($request->documentos)) {
                $this->procesarDocumentos($evaluacion, $request->documentos, $postulacionId);
            }

            // Calcular y actualizar puntaje total
            $this->actualizarPuntajeTotal($evaluacion);

            // Si se finaliza, actualizar estado de la postulación
            if ($request->finalizar) {
                $this->verificarYActualizarPostulacion($postulacion);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Evaluación guardada exitosamente',
                'data' => [
                    'evaluacion_id' => $evaluacion->id,
                    'puntaje_total' => $evaluacion->fresh()->puntaje_total,
                    'estado' => $evaluacion->estado
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la evaluación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener evaluación existente
     */
    public function show($postulacionId)
    {
        try {
            $evaluacion = Evaluacion::where('postulacion_id', $postulacionId)
                ->where('evaluador_id', Auth::id())
                ->with(['requisitos', 'documentos'])
                ->first();

            if (!$evaluacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró evaluación para este usuario'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $evaluacion
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la evaluación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesar requisitos de la evaluación
     */
    private function procesarRequisitos($evaluacion, $requisitos, $postulacionId)
{
    foreach ($requisitos as $reqData) {
        // Validar existencia del requisito
        $this->validarRequisito($reqData['requisito_id'], $reqData['es_requisito_ley']);

        // Validar documento asociado si existe
        if ($reqData['postulacion_documento_id']) {
            $this->validarDocumentoRequisito(
                $reqData['postulacion_documento_id'], 
                $reqData['requisito_id'], 
                $postulacionId
            );
        }

        // Guardar en la columna correcta según el tipo
        EvaluacionRequisito::updateOrCreate(
            [
                'evaluacion_id' => $evaluacion->id,
                'requisito_id' => $reqData['es_requisito_ley'] ? null : $reqData['requisito_id'],
                'requisito_ley_id' => $reqData['es_requisito_ley'] ? $reqData['requisito_id'] : null,
                'es_requisito_ley' => $reqData['es_requisito_ley']
            ],
            [
                'estado' => $reqData['estado'],
                'comentarios' => $reqData['comentarios'] ?? '',
                'postulacion_documento_id' => $reqData['postulacion_documento_id']
            ]
        );
    }
}

    /**
     * Procesar documentos del formulario/CV
     */
    private function procesarDocumentos($evaluacion, $documentos, $postulacionId)
    {
        foreach ($documentos as $docData) {
            // Validar que el documento pertenece a la postulación
            $documento = PostulacionDocumento::where('id', $docData['postulacion_documento_id'])
                ->where('postulacion_id', $postulacionId)
                ->first();

            if (!$documento) {
                throw new \Exception("Documento {$docData['postulacion_documento_id']} no pertenece a la postulación");
            }

            // Crear o actualizar evaluación del documento
            EvaluacionDocumento::updateOrCreate(
                [
                    'evaluacion_id' => $evaluacion->id,
                    'postulacion_documento_id' => $docData['postulacion_documento_id']
                ],
                [
                    'estado' => $docData['estado'],
                    'puntaje' => $docData['puntaje'],
                    'comentarios' => $docData['comentarios'] ?? ''
                ]
            );
        }
    }

    /**
     * Validar existencia del requisito
     */
    private function validarRequisito($requisitoId, $esRequisitoLey)
    {
        $existe = \App\Models\Convocatorias\Requisitos::where('id', $requisitoId)->exists()
        || \App\Models\Convocatorias\RequisitosLey::where('id', $requisitoId)->exists();

    if (!$existe) {
        throw new \Exception("Requisito ID {$requisitoId} no existe en ninguna tabla de requisitos");
    }
    }

    /**
     * Validar documento asociado a requisito
     */
    private function validarDocumentoRequisito($documentoId, $requisitoId, $postulacionId)
    {
        $documento = PostulacionDocumento::where('id', $documentoId)
            ->where('postulacion_id', $postulacionId)
            ->where('requisito_id', $requisitoId)
            ->first();

        if (!$documento) {
            throw new \Exception("Documento ID {$documentoId} no está asociado al requisito {$requisitoId}");
        }
    }

    /**
     * Actualizar puntaje total de la evaluación
     */
    private function actualizarPuntajeTotal($evaluacion)
    {
        $puntajeDocumentos = $evaluacion->documentos()
            ->where('estado', 'aprobado')
            ->sum('puntaje');

        $evaluacion->update(['puntaje_total' => $puntajeDocumentos]);
    }

    /**
     * Verificar si todos los evaluadores terminaron y actualizar postulación
     */
    private function verificarYActualizarPostulacion($postulacion)
    {
        // Contar evaluaciones finalizadas vs total de evaluadores asignados
        $evaluacionesFinalizadas = Evaluacion::where('postulacion_id', $postulacion->id)
            ->where('estado', 'finalizada')
            ->count();

        // Aquí deberías tener la lógica para saber cuántos evaluadores están asignados
        // Por ahora, solo actualizo si hay al menos una evaluación finalizada
        if ($evaluacionesFinalizadas > 0) {
            $puntajePromedio = Evaluacion::where('postulacion_id', $postulacion->id)
                ->where('estado', 'finalizada')
                ->avg('puntaje_total');

            $postulacion->update([
                'estado' => 'evaluada',
                'nota_final' => round($puntajePromedio, 2)
            ]);
        }
    }

    /**
     * Listar todas las evaluaciones de una postulación
     */
    public function index($postulacionId)
    {
        try {
            $evaluaciones = Evaluacion::where('postulacion_id', $postulacionId)
                ->with(['evaluador:id,name,email', 'requisitos', 'documentos'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $evaluaciones
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener evaluaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar evaluación (solo borradores)
     */
    public function destroy($evaluacionId)
    {
        try {
            $evaluacion = Evaluacion::where('id', $evaluacionId)
                ->where('evaluador_id', Auth::id())
                ->where('estado', 'borrador')
                ->firstOrFail();

            $evaluacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Evaluación eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar evaluación',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function miEvaluacion($postulacionId)
{
    $userId = auth()->id(); // O el evaluador actual según tu lógica

    $evaluacion = \App\Models\Evaluacion\Evaluacion::where('postulacion_id', $postulacionId)
        ->where('evaluador_id', $userId)
        ->with([
            'requisitos.requisito',      // Para personalizados
    'requisitos.requisitoLey',   // Para ley
    'documentos'
        ])
        ->first();

    if (!$evaluacion) {
        return response()->json([
            'success' => false,
            'message' => 'No hay evaluación previa para esta postulación'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $evaluacion
    ]);
}

public function postulantesPorConvocatoria($convocatoriaId)
{
    $postulantes = Postulacion::with('postulante.user')
        ->where('convocatoria_id', $convocatoriaId)
        ->get()
        ->map(function($postulacion) {
            // Calcula el promedio de las notas finales de las evaluaciones finalizadas
            $notaFinal = \App\Models\Evaluacion\Evaluacion::where('postulacion_id', $postulacion->id)
                ->where('estado', 'finalizada')
                ->avg('puntaje_total');

            return [
                'id' => $postulacion->id,
                'usuario' => $postulacion->postulante && $postulacion->postulante->user
                    ? [
                        'name' => $postulacion->postulante->user->name,
                        'surname' => $postulacion->postulante->user->surname,
                        'email' => $postulacion->postulante->user->email,
                    ]
                    : null,
                'nota_preliminar' => $postulacion->nota_preliminar,
                'nota_final' => $notaFinal ,
                'estado' => $postulacion->estado,
            ];
        })
        ->sortByDesc('nota_final')
        ->values();

    return response()->json([
        'success' => true,
        'data' => $postulantes
    ]);
}

}