<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Registro de usuario
     */
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,artista,usuario',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json([
            'message' => '¡Usuario registrado exitosamente!',
            'user' => $user
        ], 201);
    }

    /**
     * Inicio de sesión
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('username', 'password');

        // Intentar autenticar
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $user = auth()->user();

        // 🔹 Personalizar el payload del JWT
        $customClaims = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
        ];

        // 🔹 Generar JWT con los claims personalizados
        $jwt = JWTAuth::claims($customClaims)->fromUser($user);

        // 🔹 Crear cookie visible (NO httpOnly) para permitir decodificar en https://jwt.io/
        // Duración: 1 día
        $cookie = cookie(
            'session',   // nombre de la cookie
            $jwt,        // valor del token
            60 * 24,     // duración (minutos)
            '/',         // ruta
            null,        // dominio
            false,       // secure (true si usas HTTPS)
            false        // httpOnly = false para que el navegador/React pueda leerlo
        );

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'token' => $jwt, // 🔹 Enviamos también el token al frontend
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ])->withCookie($cookie);
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        try {
            JWTAuth::parseToken()->invalidate();
        } catch (JWTException $e) {
            // Ignorar si ya expiró
        }

        $forgetCookie = cookie()->forget('session');

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ])->withCookie($forgetCookie);
    }

    /**
     * Obtener usuario autenticado desde token
     */
    public function user(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            return response()->json([
                'message' => 'Usuario autenticado correctamente',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'No autorizado'
            ], 401);
        }
    }
}
