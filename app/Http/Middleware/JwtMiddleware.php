<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    /**
     * Maneja una solicitud entrante (protege rutas usando JWT).
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // 🔹 Buscar el token en la cookie "session" o en el encabezado Authorization
            $token = $request->cookie('session') ?? $request->bearerToken();

            if (!$token) {
                return response()->json(['message' => 'Token no encontrado'], 401);
            }

            // 🔹 Autenticar el usuario a partir del token
            $user = JWTAuth::setToken($token)->authenticate();

            if (!$user) {
                return response()->json(['message' => 'Usuario no encontrado o no válido'], 401);
            }

            // ✅ Asociar el usuario autenticado a la solicitud (disponible con $request->user)
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token inválido o expirado',
                'error' => $e->getMessage()
            ], 401);
        }

        // Continuar con la solicitud
        return $next($request);
    }
}
