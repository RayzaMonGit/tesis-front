<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class VerificationController extends Controller
{
     public function show()
    {
        return view('auth.verify');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
                    ->where('verification_code', $request->verification_code)
                    ->first();

        if (!$user) {
            return back()->withErrors(['verification_code' => 'Código inválido.']);
        }

        $user->is_verified = true;
        $user->verification_code = null;
        $user->save();

        return redirect()->route('login')->with('status', 'Cuenta verificada. Ahora puedes iniciar sesión.');
    }
}