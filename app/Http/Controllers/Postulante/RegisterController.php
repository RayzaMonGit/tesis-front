<?php

namespace App\Http\Controllers\Postulante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Postulante\Postulante;;


use App\Http\Resources\Postulante\PostulanteCollection;
use App\Http\Resources\Postulante\PostulanteResource;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

use App\Http\Controllers\AuthController;
//para enviar email
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;



class RegisterController extends Controller
{
    public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $verificationCode = Str::random(6);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'verification_code' => $verificationCode,
        'is_verified' => false,
    ]);

    Mail::to($user->email)->send(new VerificationCodeMail($verificationCode));

    return redirect()->route('verification.notice');
}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Guardar el password plano para login
            $plainPassword = $request->password;

            $validator = Validator::make($request->all(), [
            // Datos del usuario
            'name' => 'required|string|max:100',
            'surname' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'telefono' => 'required|string|max:20',
            'gender' => 'required|string|in:M,F,O',
            'tipo_doc' => 'required|string',
            'n_doc' => 'required|string|unique:users,n_doc',
            'avatar' => 'nullable|image|max:2048',
    
            // Datos del postulante
            'grado_academico' => 'required|string|max:100',
            'experiencia_años' => 'required|integer|min:0',
            //'convocatoria_id' => 'nullable|exists:convocatorias,id',
            'fecha_nacimiento' => 'nullable|date',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }
    
        // Subir imagen si existe
        if ($request->hasFile("imagen")) {
            $path = Storage::putFile("users", $request->file("imagen"));
            $request->request->add(["avatar" => $path]);
        }
    
        // Encriptar password
        if ($request->password) {
            $request->request->add(["password" => bcrypt($request->password)]);
        }
    
        // Asignar role_id automáticamente (por nombre)
        $rolPostulante = Role::where('name', 'Postulante')->first();
        if (!$rolPostulante) {
            return response()->json(['message' => 'Rol postulante no encontrado'], 500);
        }
        $request->request->add(["role_id" => $rolPostulante->id]);
    
        // Crear usuario y rol
        $user = User::create($request->only([
            'name', 'surname', 'email', 'password', 'telefono', 'gender', 'tipo_doc', 'n_doc', 'avatar', 'role_id'
        ]));
        $role=Role::findOrFail($rolPostulante->id);
        $user->assignRole($role);
    
        // Crear postulante
        Postulante::create([
            'user_id' => $user->id,
            'grado_academico' => $request->grado_academico,
            'experiencia_años' => $request->experiencia_años,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            //'convocatoria_id' => $request->convocatoria_id,
        ]);
    
        
        $token = auth('api')->attempt([
            'email' => $user->email,
            'password' => $plainPassword,
        ]);
        
        $authController = new AuthController();
        
        return $authController->respondWithToken($token);
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
