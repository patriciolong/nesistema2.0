<?php

namespace App\Http\Controllers;

use App\Models\ChatConversacion;
use App\Models\ChatMensaje;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Muestra la vista principal del módulo de Chat en el Panel Administrativo.
     */
    public function index(Request $request): View
    {
        $clienteId = $request->query('cliente_id');
        $initialConversationId = null;

        if ($clienteId) {
            $conv = ChatConversacion::firstOrCreate(
                ['id_cliente' => $clienteId],
                [
                    'ultimo_mensaje' => 'Conversación iniciada',
                    'ultimo_mensaje_at' => now(),
                    'unread_admin' => 0,
                    'unread_cliente' => 0,
                    'estado' => 'activa',
                ]
            );
            $initialConversationId = $conv->id;
        }

        return view('chat.index', [
            'initialConversationId' => $initialConversationId,
        ]);
    }

    /**
     * Retorna el listado de conversaciones en formato JSON para el panel administrativo.
     */
    public function conversaciones(Request $request): JsonResponse
    {
        $buscar = trim((string) $request->query('q', ''));
        $filtro = $request->query('filtro', 'todas'); // todas, no_leidas

        $query = ChatConversacion::with(['cliente'])
            ->whereHas('cliente');

        if ($filtro === 'no_leidas') {
            $query->where('unread_admin', '>', 0);
        }

        if ($buscar !== '') {
            $query->whereHas('cliente', function ($q) use ($buscar) {
                $q->where('c_nombre', 'like', "%{$buscar}%")
                  ->orWhere('c_apellido', 'like', "%{$buscar}%")
                  ->orWhere('c_identificacion', 'like', "%{$buscar}%")
                  ->orWhere('c_telefono', 'like', "%{$buscar}%");
            });
        }

        $conversaciones = $query->orderBy('ultimo_mensaje_at', 'desc')
            ->orderBy('id', 'desc')
            ->take(50)
            ->get()
            ->map(function ($c) {
                $cliente = $c->cliente;
                $nombreCompleto = $cliente ? trim("{$cliente->c_nombre} {$cliente->c_apellido}") : 'Cliente Desconocido';
                if (empty($nombreCompleto) && $cliente) {
                    $nombreCompleto = $cliente->c_nombre ?: ('Cliente #' . $cliente->id_cliente);
                }

                return [
                    'id' => $c->id,
                    'cliente_id' => $c->id_cliente,
                    'cliente_nombre' => $nombreCompleto,
                    'cliente_identificacion' => $cliente ? $cliente->c_identificacion : '',
                    'cliente_telefono' => $cliente ? $cliente->c_telefono : '',
                    'cliente_oficina' => $cliente ? $cliente->c_oficina_registro : '',
                    'cliente_inicial' => mb_substr($nombreCompleto, 0, 1, 'UTF-8'),
                    'ultimo_mensaje' => $c->ultimo_mensaje ?: 'Sin mensajes',
                    'ultimo_mensaje_at' => $c->ultimo_mensaje_at ? $c->ultimo_mensaje_at->diffForHumans() : '',
                    'ultimo_mensaje_hora' => $c->ultimo_mensaje_at ? $c->ultimo_mensaje_at->format('h:i A') : '',
                    'unread_admin' => (int) $c->unread_admin,
                    'unread_cliente' => (int) $c->unread_cliente,
                    'estado' => $c->estado,
                ];
            });

        $totalUnread = ChatConversacion::sum('unread_admin');

        return response()->json([
            'success' => true,
            'conversaciones' => $conversaciones,
            'total_unread' => (int) $totalUnread,
        ]);
    }

    /**
     * Retorna los mensajes de una conversación específica y los marca como leídos por el admin.
     */
    public function mensajes(Request $request, int $id): JsonResponse
    {
        $conversacion = ChatConversacion::with('cliente')->find($id);

        if (!$conversacion) {
            return response()->json([
                'success' => false,
                'message' => 'Conversación no encontrada.',
            ], 404);
        }

        // Marcar mensajes del cliente como leídos
        ChatMensaje::where('id_conversacion', $id)
            ->where('remitente_tipo', 'cliente')
            ->where('leido', false)
            ->update(['leido' => true]);

        // Resetear contador de no leídos para el admin
        $conversacion->update(['unread_admin' => 0]);

        $mensajes = ChatMensaje::where('id_conversacion', $id)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'remitente_tipo' => $m->remitente_tipo,
                    'id_usuario' => $m->id_usuario,
                    'nombre_remitente' => $m->nombre_remitente,
                    'mensaje' => $m->mensaje,
                    'adjunto_url' => $m->adjunto_url,
                    'adjunto_nombre' => $m->adjunto_nombre,
                    'leido' => (bool) $m->leido,
                    'fecha' => $m->created_at ? $m->created_at->format('d/m/Y') : '',
                    'hora' => $m->created_at ? $m->created_at->format('h:i A') : '',
                    'created_at' => $m->created_at ? $m->created_at->toIso8601String() : null,
                ];
            });

        $cliente = $conversacion->cliente;
        $nombreCompleto = $cliente ? trim("{$cliente->c_nombre} {$cliente->c_apellido}") : 'Cliente';

        return response()->json([
            'success' => true,
            'conversacion' => [
                'id' => $conversacion->id,
                'cliente_id' => $conversacion->id_cliente,
                'cliente_nombre' => $nombreCompleto,
                'cliente_identificacion' => $cliente ? $cliente->c_identificacion : '',
                'cliente_telefono' => $cliente ? $cliente->c_telefono : '',
                'cliente_email' => $cliente ? $cliente->c_email : '',
                'cliente_oficina' => $cliente ? $cliente->c_oficina_registro : '',
                'cliente_inicial' => mb_substr($nombreCompleto, 0, 1, 'UTF-8'),
                'estado' => $conversacion->estado,
            ],
            'mensajes' => $mensajes,
        ]);
    }

    /**
     * Enviar mensaje desde el panel administrativo al cliente.
     */
    public function enviar(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mensaje' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $conversacion = ChatConversacion::find($id);

        if (!$conversacion) {
            return response()->json([
                'success' => false,
                'message' => 'Conversación no encontrada.',
            ], 404);
        }

        $user = Auth::user();
        $adminNombre = $user ? ($user->name ?: 'Notaría NESISTEMA') : 'Notaría NESISTEMA';

        $mensaje = ChatMensaje::create([
            'id_conversacion' => $conversacion->id,
            'remitente_tipo' => 'admin',
            'id_usuario' => $user ? $user->id : null,
            'nombre_remitente' => $adminNombre,
            'mensaje' => trim($request->input('mensaje')),
            'leido' => false,
        ]);

        // Actualizar conversación
        $conversacion->update([
            'ultimo_mensaje' => $mensaje->mensaje,
            'ultimo_mensaje_at' => now(),
            'unread_cliente' => $conversacion->unread_cliente + 1,
        ]);

        // Enviar notificación Push al dispositivo móvil del cliente
        try {
            app(\App\Services\ClientNotificationService::class)->notificarNuevoMensajeChat(
                $conversacion->id_cliente,
                $mensaje->mensaje,
                $adminNombre
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Error notifying client of chat message: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'mensaje' => [
                'id' => $mensaje->id,
                'remitente_tipo' => $mensaje->remitente_tipo,
                'id_usuario' => $mensaje->id_usuario,
                'nombre_remitente' => $mensaje->nombre_remitente,
                'mensaje' => $mensaje->mensaje,
                'leido' => false,
                'fecha' => $mensaje->created_at->format('d/m/Y'),
                'hora' => $mensaje->created_at->format('h:i A'),
                'created_at' => $mensaje->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Retorna el número de mensajes no leídos global para la barra lateral.
     */
    public function unreadCount(): JsonResponse
    {
        $count = ChatConversacion::sum('unread_admin');

        return response()->json([
            'success' => true,
            'count' => (int) $count,
        ]);
    }
}
