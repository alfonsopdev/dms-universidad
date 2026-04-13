<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function authenticate(Request $request)
    {
        // 1. Validamos los datos
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Intentamos iniciar sesión
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // 3. Redirección al Vue (Dashboard)
            return redirect()->intended('/dashboard');
        }

        // 4. Si falla, devolvemos al Blade con error
        return back()->withErrors([
            'email' => 'El correo o la contraseña son incorrectos.',
        ])->onlyInput('email');
    }
}