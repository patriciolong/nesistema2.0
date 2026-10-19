<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversacion;
use App\Models\ChatMensaje;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientChatController extends Controller
{
    /**
     * Obtiene la conversación activa y los mensajes del cliente autenticado.
     */
    public function getMensajes(Request $request): JsonResponse
    {
        /** @var Cliente $cliente */
        $cliente = $request->attributes->get('cliente');

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no autenticado.',
            ], 401);
        }

        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('chat_conversaciones') || !\Illuminate\Support\Facades\Schema::hasTable('chat_mensajes')) {
                return response()->json([
                    'success' => true,
                    'conversacion_id' => 0,
                    'unread_cliente' => 0,
                    'mensajes' => [],
                ]);
            }

            $conversacion = ChatConversacion::firstOrCreate(
                ['id_cliente' => $cliente->id_cliente],
                [
                    'ultimo_mensaje' => 'Conversación iniciada',
                    'ultimo_mensaje_at' => now(),
                    'unread_admin' => 0,
                    'unread_cliente' => 0,
                    'estado' => 'activa',
                ]
            );

            // Marcar mensajes de la notaría como leídos por el cliente
            ChatMensaje::where('id_conversacion', $conversacion->id)
                ->where('remitente_tipo', 'admin')
                ->where('leido', false)
                ->update(['leido' => true]);

            // Resetear contador de no leídos para el cliente
            if ($conversacion->unread_cliente > 0) {
                $conversacion->update(['unread_cliente' => 0]);
            }

            $mensajes = ChatMensaje::where('id_conversacion', $conversacion->id)
                ->orderBy('id', 'asc')
                ->get()
                ->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'remitente_tipo' => $m->remitente_tipo,
                        'nombre_remitente' => $m->nombre_remitente,
                        'mensaje' => $m->mensaje,
                        'adjunto_url' => $m->adjunto_url,
                        'adjunto_nombre' => $m->adjunto_nombre,
                        'leido' => (bool) $m->leido,
                        'hora' => $m->created_at ? $m->created_at->format('h:i A') : '',
                        'fecha' => $m->created_at ? $m->created_at->format('d/m/Y') : '',
                        'created_at' => $m->created_at ? $m->created_at->toIso8601String() : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'conversacion_id' => $conversacion->id,
                'unread_cliente' => 0,
                'mensajes' => $mensajes,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => true,
                'conversacion_id' => 0,
                'unread_cliente' => 0,
                'mensajes' => [],
            ]);
        }
    }

    /**
     * Envía un mensaje desde la app móvil o portal del cliente hacia la notaría.
     */
    public function enviarMensaje(Request $request): JsonResponse
    {
        /** @var Cliente $cliente */
        $cliente = $request->attributes->get('cliente');

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no autenticado.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'mensaje' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('chat_conversaciones') || !\Illuminate\Support\Facades\Schema::hasTable('chat_mensajes')) {
                return response()->json([
                    'success' => false,
                    'message' => 'El servicio de chat no está disponible temporalmente.',
                ], 503);
            }

            $conversacion = ChatConversacion::firstOrCreate(
                ['id_cliente' => $cliente->id_cliente],
                [
                    'ultimo_mensaje' => 'Conversación iniciada',
                    'ultimo_mensaje_at' => now(),
                    'unread_admin' => 0,
                    'unread_cliente' => 0,
                    'estado' => 'activa',
                ]
            );

            $nombreCliente = trim("{$cliente->c_nombre} {$cliente->c_apellido}") ?: ($cliente->c_nombre ?: 'Cliente');

            $mensaje = ChatMensaje::create([
                'id_conversacion' => $conversacion->id,
                'remitente_tipo' => 'cliente',
                'id_usuario' => null,
                'nombre_remitente' => $nombreCliente,
                'mensaje' => trim($request->input('mensaje')),
                'leido' => false,
            ]);

            // Actualizar conversación
            $conversacion->update([
                'ultimo_mensaje' => $mensaje->mensaje,
                'ultimo_mensaje_at' => now(),
                'unread_admin' => $conversacion->unread_admin + 1,
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => [
                    'id' => $mensaje->id,
                    'remitente_tipo' => $mensaje->remitente_tipo,
                    'nombre_remitente' => $mensaje->nombre_remitente,
                    'mensaje' => $mensaje->mensaje,
                    'leido' => false,
                    'hora' => $mensaje->created_at->format('h:i A'),
                    'fecha' => $mensaje->created_at->format('d/m/Y'),
                    'created_at' => $mensaje->created_at->toIso8601String(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el mensaje.',
            ], 500);
        }
    }

    /**
     * Retorna el número de mensajes no leídos para el cliente (para la insignia del botón flotante).
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        /** @var Cliente $cliente */
        $cliente = $request->attributes->get('cliente');

        if (!$cliente) {
            return response()->json(['success' => false, 'count' => 0], 401);
        }

        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('chat_conversaciones')) {
                return response()->json(['success' => true, 'unread_count' => 0]);
            }
            $conversacion = ChatConversacion::where('id_cliente', $cliente->id_cliente)->first();
            $unread = $conversacion ? (int) $conversacion->unread_cliente : 0;

            return response()->json([
                'success' => true,
                'unread_count' => $unread,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => true, 'unread_count' => 0]);
        }
    }
}
