<?php

namespace App\Http\Controllers\Postulacion;

use Illuminate\Support\Facades\Http;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Postulacion\Postulacion;
use App\Http\Requests\Postulacion\StorePostulacionRequest;
use App\Http\Resources\Postulacion\PostulacionResource;


use App\Models\Postulante\Postulante;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Postulante\PostulanteResource;
use App\Http\Resources\Postulante\PostulanteCollection;
use App\Models\Convocatorias\Convocatoria;
use App\Http\Resources\Convocatoria\ConvocatoriaResource;
use App\Http\Resources\Convocatoria\ConvocatoriaCollection;

use App\Http\Resources\Postulacion\PostulacionDocumentoResource;

use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserCollection;
use App\Models\User;

class PostulacionController extends Controller
{
    public function index()
{
    $postulaciones = Postulacion::with([
        'postulante.user',
        'convocatoria.formulario.secciones.criterios', 
        'convocatoria.requisitosLey',
        'convocatoria.evaluadores'
    ])->get();

    return PostulacionResource::collection($postulaciones);
}


    public function store(Request $request)
{
    $validated = $request->validate([
        'postulante_id' => 'required|exists:postulantes,id',
        'convocatoria_id' => 'required|exists:convocatorias,id',
    ]);

    // Verificar si ya existe una postulación para ese postulante y convocatoria
    $existe = Postulacion::where('postulante_id', $validated['postulante_id'])
        ->where('convocatoria_id', $validated['convocatoria_id'])
        ->first();

    if ($existe) {
        return response()->json([
            'message' => 'Ya estás postulado a esta convocatoria.',
            'redirect' => true, // bandera para frontend
            'postulacion_id' => $existe->id // para redireccionar a los documentos
        ], 200);
    }

    DB::beginTransaction();
    try {
        $postulacion = Postulacion::create($validated);

        // Aquí llamamos a n8n:
    try {
        \Illuminate\Support\Facades\Http::post('https://primary-production-a98a1.up.railway.app/webhook/nueva-postulacion', [
            'postulacion_id' => $postulacion->id
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error al notificar a n8n: ' . $e->getMessage());
    }

        DB::commit();
        return response()->json([
            'message' => 'Formulario creado correctamente.',
            'postulacion_id' => $postulacion->id
        ], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'error' => 'Error al crear la postulación',
            'details' => $e->getMessage(),
        ], 500);
    }
}


public function porPostulante($postulanteId)
{
    $postulaciones = Postulacion::with([
        'postulante.user',
        'convocatoria.formulario.secciones.criterios', 
        'convocatoria.requisitosLey',
        'convocatoria.evaluadores'
    ])
    ->where('postulante_id', $postulanteId)
    ->get();

    return PostulacionResource::collection($postulaciones);
}

    
public function show($id)
{
    $postulacion = Postulacion::with([
        'postulante.user',
        'convocatoria',
        'convocatoria.requisitosLey',
        'convocatoria.requisitos',
        'convocatoria.formulario.secciones.criterios',
        'documentos' => function($query) {
            $query->orderBy('es_requisito_ley', 'desc')
                  ->orderBy('es_requisito_personalizado', 'desc');
        },
        'documentosFormulario' => function($query) {
            $query->with(['seccion', 'criterio']);
        }
    ])->findOrFail($id);

    // Agrupar documentos según tipo y envolver en resource
    $documentosLey = PostulacionDocumentoResource::collection(
        $postulacion->documentos->where('es_requisito_ley', true)->values()
    );
    $documentosPersonalizados = PostulacionDocumentoResource::collection(
        $postulacion->documentos->where('es_requisito_personalizado', true)->values()
    );
    $documentosFormulario = PostulacionDocumentoResource::collection(
        $postulacion->documentosFormulario
    );

    return response()->json([
        'postulacion' => $postulacion,
        'postulante' => $postulacion->postulante,
        'requisitos' => [
            'ley' => $postulacion->convocatoria->requisitosLey,
            'personalizados' => $postulacion->convocatoria->requisitos
        ],
        'formulario' => $postulacion->convocatoria->formulario,
        'documentos' => [
            'requisitos_ley' => $documentosLey,
            'requisitos_personalizados' => $documentosPersonalizados,
            'formulario_curriculum' => $documentosFormulario
        ]
    ]);
}



    public function update(Request $request, $id)
{
    // Busca la postulación por ID
    $postulacion = Postulacion::findOrFail($id);

    // Valida los datos enviados
    $validated = $request->validate([
        'estado' => 'required|in:pendiente,en evaluación,evaluado,rechazado,aprobado',
        // Agrega otras validaciones si es necesario
    ]);

    // Actualiza la postulación con los datos validados
    $postulacion->update($validated);

    // Devuelve la postulación actualizada como un recurso
    return new PostulacionResource($postulacion);
}

    public function destroy($id)
    {
        $postulacion = Postulacion::findOrFail($id);
        DB::beginTransaction();
        try {
            $postulacion->delete();
            DB::commit();
            return response()->json(['message' => 'Postulación eliminada correctamente.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar la postulación.'], 500);
        }
    }

    public function porConvocatoria($convocatoriaId)
{
    $postulaciones = Postulacion::where('convocatoria_id', $convocatoriaId)
        ->with([
            'postulante.user',
            'convocatoria.formulario.secciones.criterios', 
            'convocatoria.requisitosLey',
            'convocatoria.evaluadores'
        ])
        ->get();

    return PostulacionResource::collection($postulaciones);
}


public function cambiarEstado(Request $request, $id)
{

    $postulacion = Postulacion::findOrFail($id);
    
    // Verificar que el usuario tiene permisos para cambiar el estado
    // Aquí deberías agregar tu lógica de autorización
    if($request->has('estado')) {
        $postulacion->estado = $request->estado;
    }
    // Si se envía una nota preliminar, también la actualizamos
    if ($request->has('nota_preliminar')) {
        $postulacion->nota_preliminar = $request->nota_preliminar;
    }
    $postulacion->save();

    return response()->json([
        'success' => true,
        'message' => 'Estado actualizado correctamente',
        'data' => $postulacion
    ]);
}


public function postulantesPorConvocatoria($id)
{
    $convocatoria = Convocatoria::with(['postulaciones.postulante.user'])->findOrFail($id);

    $postulantes = $convocatoria->postulaciones->map(function ($postulacion) {
        $user = $postulacion->postulante->user ?? null;

        return [
            'id_postulacion' => $postulacion->id,
            'estado' => $postulacion->estado,
            'postulante_id' => $postulacion->postulante_id,
            'user' => $user ? new UserResource($user) : null, // Aquí usas el UserResource
        ];
    });

    return response()->json([
        'convocatoria_id' => $id,
        'postulantes' => $postulantes,
    ]);
}

}