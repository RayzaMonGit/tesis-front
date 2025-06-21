<?php

namespace App\Http\Controllers\Postulacion;

use App\Http\Controllers\Controller;
use App\Models\Postulacion\PostulacionDocumento;
use Illuminate\Http\Request;
use App\Http\Resources\Postulacion\PostulacionDocumentoResource;
use App\Http\Resources\Postulacion\PostulacionDocumentoCollection;
use App\Models\Postulacion\Postulacion;
use App\Models\Postulante\Postulante;
use App\Models\Convocatorias\Convocatoria;
use App\Http\Resources\Postulante\PostulanteResource;
use App\Http\Resources\Postulante\PostulanteCollection;
use App\Http\Resources\Convocatoria\ConvocatoriaResource;
use App\Http\Resources\Convocatoria\ConvocatoriaCollection;


use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PostulacionDocumentoController extends Controller
{
    public function index($id)
{
    // Busca todos los documentos asociados a la postulación con el ID dado
    $documentos = PostulacionDocumento::where('postulacion_id', $id)->get();
    return PostulacionDocumentoResource::collection($documentos);
}

public function all()
{
    $documentos = PostulacionDocumento::all();
    return PostulacionDocumentoResource::collection($documentos);
}

    public function store(Request $request)
{
    $request->validate([
        'postulacion_id'             => 'required|exists:postulaciones,id',
        'seccion_id' => 'nullable|integer', // Para documentos de currículum
        'criterio_id' => 'nullable|integer', // Para documentos de currículum
        'requisito_id' => 'nullable|integer', // Para requisitos básicos
        'nombre'                     => 'required|string',
        'archivo'                    => 'required|file',
        'es_requisito_ley'           => 'boolean',
        'es_requisito_personalizado' => 'boolean',
        
    ]);

    if ($request->es_requisito_ley) {
        if (!\DB::table('requisitos_ley')->where('id', $request->requisito_id)->exists()) {
            return response()->json(['error' => 'Requisito de ley no válido'], 422);
        }
    }

    if ($request->es_requisito_personalizado) {
        if (!\DB::table('requisitos')->where('id', $request->requisito_id)->exists()) {
            return response()->json(['error' => 'Requisito personalizado no válido'], 422);
        }
    }

    $tipo = $request->es_requisito_ley ? 'ley' : 'personalizado';
    $ruta = $request->file('archivo')->store(
        "postulacion-documentos/{$request->postulacion_id}/requisitos/{$tipo}",
        'public'
    );

    PostulacionDocumento::create([
        'postulacion_id'             => $request->postulacion_id,
        'requisito_id'               => $request->requisito_id,
        'seccion_id'                 => $request->seccion_id,
        'criterio_id'                => $request->criterio_id,
        'nombre'                     => $request->nombre,
        'archivo'                    => $ruta,
        'es_requisito_ley'           => $request->es_requisito_ley,
        'es_requisito_personalizado' => $request->es_requisito_personalizado,
       
    ]);

    return response()->json(['message' => 'Documento guardado correctamente']);
}
public function destroyByPostulacionAndRequisito(Request $request)
{
    $request->validate([
        'postulacion_id' => 'required|integer',
        'requisito_id' => 'required|integer',
    ]);

    $doc = PostulacionDocumento::where('postulacion_id', $request->postulacion_id)
        ->where('requisito_id', $request->requisito_id)
        ->first();

    if ($doc) {
        // Elimina archivo físico si deseas
        if (Storage::exists($doc->archivo)) {
            Storage::delete($doc->archivo);
        }
        $doc->delete();
        return response()->json(['message' => 'Documento eliminado'], 200);
    }

    return response()->json(['message' => 'Documento no encontrado'], 404);
}


/*
    public function storeMultiple(Request $request)
    {
        Log::info('=== INICIO storeMultiple ===');
        Log::info('Método HTTP:', $request->method());
        Log::info('URL completa:', $request->fullUrl());
        Log::info('Datos recibidos en storeMultiple:', $request->all());
        Log::info('Archivos recibidos:', $request->allFiles());
        Log::info('Headers:', $request->headers->all());

        $request->validate([
            'postulacion_id' => 'required|exists:postulaciones,id',
            'archivos' => 'required|array',
            'archivos.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'requisito_ids' => 'required|array',
            'requisito_ids.*' => 'required|integer',
        ]);

        $postulacionId = $request->postulacion_id;
        $archivos = $request->file('archivos');
        $requisitoIds = $request->requisito_ids;

        if (count($archivos) !== count($requisitoIds)) {
            return response()->json([
                'error' => 'El número de archivos debe coincidir con el número de requisitos'
            ], 400);
        }

        $documentosCreados = [];

        DB::beginTransaction();
        
        try {
            foreach ($archivos as $index => $archivo) {
                $requisitoId = $requisitoIds[$index];
                
                // Generar nombre único para el archivo
                $nombreArchivo = time() . '_' . $requisitoId . '_' . $archivo->getClientOriginalName();
                $ruta = $archivo->storeAs('postulacion_documentos', $nombreArchivo);

                // Determinar si es requisito de ley o personalizado
                // Esto depende de tu lógica de negocio
                $esRequisitoLey = $this->esRequisitoLey($requisitoId);
                $esRequisitoPersonalizado = !$esRequisitoLey;

                $documento = PostulacionDocumento::create([
                    'postulacion_id' => $postulacionId,
                    'requisito_id' => $requisitoId, // Asumiendo que tienes esta columna
                    'nombre' => $archivo->getClientOriginalName(),
                    'archivo' => $ruta,
                    'es_requisito_ley' => $esRequisitoLey,
                    'es_requisito_personalizado' => $esRequisitoPersonalizado,
                ]);

                $documentosCreados[] = $documento;
                
                Log::info("Documento creado: ID {$documento->id}, Requisito ID: {$requisitoId}");
            }

            DB::commit();
            
            return response()->json([
                'message' => 'Documentos guardados correctamente',
                'documentos' => PostulacionDocumentoResource::collection($documentosCreados)
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('❌ Error al guardar documentos: ' . $e->getMessage());
            Log::error('❌ Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Error al guardar los documentos',
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }
*/
    // Método auxiliar para determinar si un requisito es de ley
    private function esRequisitoLey($requisitoId)
    {
        // Implementa tu lógica aquí
        // Por ejemplo, consultar si existe en la tabla de requisitos_ley
        // return DB::table('requisitos_ley')->where('id', $requisitoId)->exists();
        
        // Por ahora retornamos false, ajusta según tu lógica
        return false;
    }
    public function destroy($id)
    {
        $documento = PostulacionDocumento::findOrFail($id);
        Storage::delete($documento->archivo);
        $documento->delete();

        return response()->json(['message' => 'Documento eliminado']);
    }
}
