<?php

namespace App\Http\Controllers\Web\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginWebController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
{
    // Validación completa
    $request->validate([
        'login' => [
            'required',
            'string',
            'min:3',
            'max:100',
        ],
        'password' => [
            'required',
            'string',
            'min:6',
        ],
    ], [
        'login.required' => 'Debes ingresar tu correo o número de empleado.',
        'password.required' => 'Debes ingresar tu contraseña.',
        'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
    ]);

    // Detectar si es correo o número de empleado
    $field = filter_var($request->login, FILTER_VALIDATE_EMAIL)
        ? 'email'
        : 'employee_number';

    // Intento de login SOLO si es supervisor y activo
    if (Auth::attempt([
        $field => $request->login,
        'password' => $request->password,
        'role' => 'supervisor',
        'active' => true
    ])) {
        $request->session()->regenerate();
        return redirect()->intended(route('supervisor.dashboard'));
    }

    return back()
        ->withInput($request->only('login'))
        ->withErrors(['login' => 'Credenciales inválidas o usuario inactivo.']);
}


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

