<?php

namespace App\Http\Controllers;

use App\Models\CodigoVerificacion;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;


class CodigoVerificacionController extends Controller
{
     public function enviar(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Generar código aleatorio de 6 dígitos
        $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Guardar o actualizar el código
        CodigoVerificacion::updateOrCreate(
            ['email' => $request->email],
            [
                'codigo' => $codigo,
                'expira_en' => Carbon::now()->addMinutes(10),
            ]
        );

        // Llamar al webhook de n8n
        try {
            Http::post('https://primary-production-a98a1.up.railway.app/webhook/enviar-codigo', [
                'email' => $request->email,
                'codigo' => $codigo
            ]);
        } catch (\Exception $e) {
            \Log::error('Error enviando a n8n: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Código enviado al correo',
        ]);
    }
    
public function verificar(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'code' => 'required|string'
    ]);

    $registro = CodigoVerificacion::where('email', $request->email)
        ->where('codigo', $request->code)
        ->first();

    if (!$registro) {
        return response()->json(['success' => false, 'message' => 'Código inválido'], 200);
    }

    // Verificar si el código expiró
    if (Carbon::now()->greaterThan($registro->expira_en)) {
        return response()->json(['success' => false, 'message' => 'Código expirado'], 200);
    }

    // (Opcional) eliminar el código una vez verificado
    $registro->delete();

    return response()->json(['success' => true, 'message' => 'Correo verificado']);
}
}
