<?php

namespace App\Http\Controllers\Convocatorias;

use App\Http\Controllers\Controller;
use App\Models\Convocatorias\ConvocatoriaAudit;
use App\Http\Resources\Convocatorias\ConvocatoriaAuditResource;

class ConvocatoriaAuditController extends Controller
{
    // Listar historial de una convocatoria
    public function index($id)
    {
         $auditoria = ConvocatoriaAudit::with('user')
        ->where('convocatoria_id', $id)
        ->orderBy('created_at', 'desc')
        ->get();

    return ConvocatoriaAuditResource::collection($auditoria);
    }
    public function all()
{
    $auditoria = ConvocatoriaAudit::with('user')
        ->orderBy('created_at', 'desc')
        ->get();

    return ConvocatoriaAuditResource::collection($auditoria);
}
}