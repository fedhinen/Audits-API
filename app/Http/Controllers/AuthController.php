<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        // Por defecto todos los registros públicos son "technician" y activos
        $user = User::create([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'password'        => Hash::make($data['password']),
            'employee_number' => $data['employee_number'] ?? null,
            'role'            => 'technician',
            'active'          => true,
        ]);

        // Abilities según rol
        $abilities = ['audits:*', 'catalog:read'];

        $token = $user->createToken('api', $abilities)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        // El campo que recibimos ahora se llama "login"
        $login = $data['login'];
        $password = $data['password'];

        // Buscar por email O employee_number
        $user = User::where('email', $login)
            ->orWhere('employee_number', $login)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 422);
        }

        if (! $user->active) {
            return response()->json(['message' => 'Usuario inactivo'], 403);
        }

        // Si es technician, enviar código y NO generar token
        if ($user->role === 'technician') {
            $code = (string) rand(100000, 999999);
            $user->verification_code = Hash::make($code);
            $user->save();
            // Enviar correo usando el Mailable
            try {
                \Illuminate\Support\Facades\Mail::to('lujanlom0120@gmail.com')->send(new \App\Mail\VerificationCodeMail($code));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error enviando correo: ' . $e->getMessage());
                return response()->json(['message' => 'Error al enviar código de verificación: ' . $e->getMessage()], 500);
            }

            return response()->json([
                'message' => 'Código de verificación enviado al correo',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ], 200);
        }

        // Abilities según rol
        $abilities = match ($user->role) {
            'admin'      => ['*'],
            'supervisor' => ['assignments:*', 'reviews:*', 'catalog:read', 'catalog:write', 'audits:read'],
            default      => ['catalog:read'], // Technician ya no entra aquí para token
        };

        $device = $data['device_name'] ?? 'api';
        $token  = $user->createToken($device, $abilities)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'role'           => $user->role,
                'employee_number' => $user->employee_number,
            ],
        ], 200);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'email' => 'required|email'
        ]);
        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->code, $user->verification_code)) {
            return response()->json(['message' => 'Código incorrecto o expirado'], 422);
        }
        $user->verification_code = null;
        $user->save();
        $abilities = ['audits:*', 'catalog:read'];
        $token = $user->createToken('api', $abilities)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
                'employee_number' => $user->employee_number,
            ],
        ], 200);
    }


    /** POST /api/v1/auth/logout */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['message' => 'Sesión cerrada'], 200);
    }

    /** GET /api/v1/auth/me */
    public function me(Request $request)
    {
        $u = $request->user();

        return response()->json([
            'id'    => $u->id,
            'name'  => $u->name,
            'email' => $u->email,
            'role'  => $u->role,
        ], 200);
    }
}
