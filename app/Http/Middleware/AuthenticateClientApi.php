<?php

namespace App\Http\Middleware;

use App\Models\Cliente;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateClientApi
{
    /**
     * Handle an incoming client API request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Obtener el token desde múltiples fuentes (Bearer, Headers alternativos, Query o Body)
        $token = $request->bearerToken();

        if (!$token) {
            $authHeader = $request->header('Authorization') 
                ?: ($request->server('HTTP_AUTHORIZATION') ?: $request->server('REDIRECT_HTTP_AUTHORIZATION'));
            
            if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = trim($matches[1]);
            }
        }

        if (!$token) {
            $token = $request->header('X-Client-Token') 
                ?: ($request->query('token') 
                ?: ($request->query('api_token') 
                ?: ($request->input('token') 
                ?: $request->input('api_token'))));
        }

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token de autorización no proporcionado.',
            ], 401);
        }

        $hashedToken = hash('sha256', $token);
        $cliente = Cliente::where('api_token', $hashedToken)->first();

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Sesión inválida o expirada. Por favor ingresa nuevamente.',
            ], 401);
        }

        // Asignar el cliente a la petición
        $request->attributes->set('cliente', $cliente);

        return $next($request);
    }
}
