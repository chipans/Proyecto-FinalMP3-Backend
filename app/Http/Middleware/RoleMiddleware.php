<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class RoleMiddleware
{
    /**
     * Manejar la solicitud y verificar rol del usuario.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        try {
            // Obtener token de cookie o header
            $token = $request->cookie('session') ?? $request->bearerToken();
            if (!$token) {
                return response()->json(['message' => 'Token no encontrado'], 401);
            }

            // Autenticar usuario desde token
            $user = JWTAuth::setToken($token)->authenticate();
            if (!$user) {
                return response()->json(['message' => 'Usuario no encontrado'], 401);
            }

            // 🔹 Verificar que $role no esté vacío y comparar
            if (!empty($role) && $user->role !== $role) {
                return response()->json(['message' => 'Acceso denegado: no tienes permisos'], 403);
            }

            // Adjuntar usuario a la solicitud
            $request->merge(['user' => $user]);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token inválido o expirado'], 401);
        }

        return $next($request);
    }
}
