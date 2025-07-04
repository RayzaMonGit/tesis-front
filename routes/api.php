<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Rol\RoleController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Convocatorias\ConvocatoriaController;
use App\Http\Controllers\Postulante\PostulanteController;
use App\Http\Controllers\Postulante\RegisterController;
use App\Http\Controllers\Requisitos\RequisitosLeyController;
use App\Http\Controllers\Comision\ComisionController;
use App\Http\Controllers\Formulario\FormularioEvaluacionController;
use App\Http\Controllers\Postulacion\PostulacionController;
use App\Http\Controllers\Postulacion\PostulacionDocumentoController;
use App\Http\Controllers\Comision\VistoBuenoController;
use App\Http\Controllers\Evaluacion\EvaluController;
use App\Http\Controllers\Evaluacion\EvaluDocumentoController;
use App\Http\Controllers\Evaluacion\EvaluacionRequisitoController;
use App\Http\Controllers\Convocatorias\ConvocatoriaAuditController;
use App\Http\Controllers\CodigoVerificacionController;




//use App\Http\Controllers\Formulario\FormularioController;
use App\Http\Controllers\Formulario\EvaluacionController;
//para que se registre un usuario sin estar autentificado

Route::post('registerpos', [RegisterController::class, 'registerpos']);
//reguistrar solo el usuario
Route::post('/register-user', [RegisterController::class, 'registerUser']);
//Route::post("register/{id}",[RegisterController::class,"update"]);
 // Rutas para el Código de Verificación
    Route::post('verificacion/enviar', [CodigoVerificacionController::class, 'enviar']);
    Route::post('verificacion/verificar', [CodigoVerificacionController::class, 'verificar']);



 
Route::group([
   // 'middleware' => 'api',
    'prefix' => 'auth',
   //'middleware' => ['auth:api','role:admin']
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');//->middleware('auth:api')
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');//->middleware('auth:api')
    Route::post('/me', [AuthController::class, 'me'])->name('me');//->middleware('auth:api')
});
Route::group([
    'middleware' => ['auth:api']
],function($router) {
    Route::resource("role",RoleController::class);
    //ruta post para actualizar los datos del usuario
    Route::post("staffs/{id}",[StaffController::class,"update"]);
    //para e index de staff
    Route::resource("staffs",StaffController::class);
    Route::get('convocatorias/all', [ConvocatoriaController::class, 'all']);

    Route::post("convocatorias/{id}", [ConvocatoriaController::class, "update"]); // método POST para actualizar con archivos
    Route::resource("convocatorias", ConvocatoriaController::class);//esto en teoria ya estaba bien

    Route::get('convocatorias/{id}/requisitos', [ConvocatoriaController::class, 'requisitos']);

    //Route::resource('postulantes', [PostulanteController::class]);
    Route::resource("postulantes", PostulanteController::class);
    Route::post("postulantes/{id}", [PostulanteController::class,"update"]);

    //auditoria convocatoria
    
Route::get('convocatorias/{convocatoria}/auditoria', [ConvocatoriaAuditController::class, 'index']);
Route::get('convocatorias/auditoria/todas', [ConvocatoriaAuditController::class, 'all']);
    //para devolver el perfil del postulante
    Route::get('postulantes-perfil', [PostulanteController::class, 'miPerfil']);


    Route::resource("requisitosley", RequisitosLeyController::class);

    // Asignar requisitos ley a una convocatoria
    Route::post('/convocatorias/{id}/requisitos-ley', [ConvocatoriaController::class, 'asignarRequisitosLey']);
    // Obtener todos los requisitos (ley + personalizados)
    Route::get('convocatorias/{id}/todos-requisitos', [ConvocatoriaController::class, 'todosRequisitos']);
    Route::post('convocatorias/{id}/todos-requisitos', [ConvocatoriaController::class, 'updateRequisitos']);

    //comisiones
    Route::get('convocatorias/{id}/comision', [ComisionController::class, 'obtenerComision']);
    Route::post('convocatorias/{id}/comision', [ComisionController::class, 'asignarComision']);
    Route::get('comision/convocatorias-por-evaluador', [ComisionController::class, 'convocatoriasPorEvaluador']);

    //Formulario

    Route::get('formularios-evaluacion', [FormularioEvaluacionController::class, 'index']);
    Route::post('formularios-evaluacion', [FormularioEvaluacionController::class, 'store']);
    Route::get('formularios-evaluacion/{id}', [FormularioEvaluacionController::class, 'show']);
    Route::post('formularios-evaluacion/{id}', [FormularioEvaluacionController::class, 'update']); // POST en vez de PUT
    Route::delete('formularios-evaluacion/{id}', [FormularioEvaluacionController::class, 'destroy']);

    //para postulaciones
    // Postulaciones
Route::get('postulaciones', [PostulacionController::class, 'index']);
Route::get('postulaciones/{id}', [PostulacionController::class, 'show']);
Route::post('postulaciones', [PostulacionController::class, 'store']);
Route::post('postulaciones/{id}', [PostulacionController::class, 'update']);
Route::delete('postulaciones/{id}', [PostulacionController::class, 'destroy']);
// Obtener postulaciones por postulante
Route::get('postulaciones/postulante/{postulanteId}', [PostulacionController::class, 'porPostulante']);

Route::get('convocatorias/{convocatoria}/postulaciones', [PostulacionController::class, 'porConvocatoria']);

Route::post('postulacion-documentos', [PostulacionDocumentoController::class, 'store']);
Route::post('postulacion-documentos/multiple', [PostulacionDocumentoController::class, 'storeMultiple']);
Route::get('postulacion-documentos', [PostulacionDocumentoController::class, 'all']);
Route::get('postulaciones/{id}/documentos', [PostulacionDocumentoController::class, 'index']);

Route::delete('postulacion-documentos/{id}', [PostulacionDocumentoController::class, 'destroy']);
Route::delete('/postulacion-documentos/eliminar', [PostulacionDocumentoController::class, 'destroyByPostulacionAndRequisito']);

//evaluaciones del postulante segun formulario



Route::post('/evaluaciones', [EvaluacionController::class, 'store']); // Guardar evaluación
Route::get('/evaluaciones/{id}', [EvaluacionController::class, 'show']); // Ver resultados (API)



Route::get('/formularios/{id}', [FormularioController::class, 'show']); // Mostrar formulario
Route::post('/evaluaciones/resultado/{id}', [EvaluacionController::class, 'resultado']); // Resultados web

// Cambiar estado de postulación
Route::post('/postulaciones/{id}/cambiar-estado', [PostulacionController::class, 'cambiarEstado']);
//visto bueno de las comisiones a los documentos
Route::post('visto-bueno',[VistoBuenoController::class]);
//para ver postulandtes de una convocatoria
Route::get('convocatorias/{id}/postulantes', [PostulacionController::class, 'postulantesPorConvocatoria']);
// Rutas para Evaluaciones
    Route::get('postulaciones/{postulacionId}/evaluaciones', [EvaluController::class, 'index']);
    Route::post('postulaciones/{postulacionId}/evaluaciones', [EvaluController::class, 'store']);
    Route::get('evaluaciones/{id}', [EvaluController::class, 'show']);

Route::get('/postulantes/{id}/convocatorias', [EvaluController::class, 'postulantesPorConvocatoria']);
    
    // Rutas para Documentos Evaluados
    Route::post('evaluaciones/{evaluacionId}/documentos', [EvaluDocumentoController::class, 'store']);
    Route::post('evaluaciones-documentos/{id}', [EvaluDocumentoController::class, 'update']);
    Route::delete('evaluaciones-documentos/{id}', [EvaluDocumentoController::class, 'destroy']);
    //evaluacio completa
    Route::post('evaluaciones/completa', [EvaluController::class, 'guardarCompleta']);
    //evaluacion de requisitos
    Route::post('evaluaciones/{evaluacionId}/requisitos', [EvaluacionRequisitoController::class, 'store']);
    Route::get('evaluaciones/{evaluacionId}/requisitos', [EvaluacionRequisitoController::class, 'index']);
    Route::post('evaluacion-requisitos/{id}', [EvaluacionRequisitoController::class, 'update']);
    Route::delete('evaluacion-requisitos/{id}', [EvaluacionRequisitoController::class, 'destroy']);
    Route::get('postulaciones/{postulacionId}/evaluaciones/mi-evaluacion', [EvaluController::class, 'miEvaluacion']);

   
});