<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Rutas API (usando JWTAuth)
|--------------------------------------------------------------------------
|
| Aquí definimos las rutas públicas (login/registro)
| y las protegidas mediante middleware JWT y roles.
|
*/

// 🔹 Rutas públicas: registro y login
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 🔹 Rutas protegidas (requieren token JWT)
Route::middleware(['jwt.verify'])->group(function () {

    // Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout']);

    // Obtener datos del usuario autenticado
    Route::get('/user', [AuthController::class, 'user']);

    // 🔹 Dashboards protegidos por rol usando RoleMiddleware
    Route::middleware('role:admin')->get('/dashboard/admin', function (Request $request) {
        return response()->json([
            'message' => 'Bienvenido al Dashboard de Administrador',
            'user' => $request->user(), // Usuario autenticado por JwtMiddleware
        ]);
    });

    Route::middleware('role:artista')->get('/dashboard/artista', function (Request $request) {
        return response()->json([
            'message' => 'Bienvenido al Dashboard de Artista',
            'user' => $request->user(),
        ]);
    });

    Route::middleware('role:usuario')->get('/dashboard/usuario', function (Request $request) {
        return response()->json([
            'message' => 'Bienvenido al Dashboard de Usuario',
            'user' => $request->user(),
        ]);
    });
});
