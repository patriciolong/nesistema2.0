<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\NotificacionCliente;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClientNotificationService
{
    /**
     * Notificar al cliente cuando un trámite está listo para entrega/descarga.
     */
    public function notificarTramiteListo(
        int $clienteId,
        string $tramiteTipo,
        $tramiteId,
        string $nombreTramite,
        ?string $oficina = null,
        ?string $observaciones = null
    ): ?NotificacionCliente {
        $cliente = Cliente::find($clienteId);
        if (!$cliente) {
            return null;
        }

        $titulo = "🎉 ¡Tu trámite está listo para entregar!";
        $mensaje = "Tu trámite '{$nombreTramite}' ha sido completado y está listo para entregar";
        if ($oficina) {
            $mensaje .= " en la sede: {$oficina}.";
        } else {
            $mensaje .= ". Puedes descargarlo desde tu portal/app o pasar a retirarlo.";
        }

        if ($observaciones) {
            $mensaje .= " Nota: {$observaciones}";
        }

        // 1. Guardar notificación interna en la Base de Datos
        $notificacion = NotificacionCliente::create([
            'id_cliente' => $cliente->id_cliente,
            'tramite_tipo' => $tramiteTipo,
            'tramite_id' => $tramiteId,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'leido' => false,
            'data_extra' => [
                'nombre_tramite' => $nombreTramite,
                'oficina' => $oficina,
                'estado' => 'listo',
                'fecha' => now()->toIso8601String(),
            ],
        ]);

        // 2. Enviar Notificación Push si el cliente tiene registrado un dispositivo
        if (!empty($cliente->fcm_token)) {
            $this->sendPushNotification(
                $cliente->fcm_token,
                $titulo,
                $mensaje,
                [
                    'tipo' => 'tramite',
                    'tramite_tipo' => $tramiteTipo,
                    'tramite_id' => (string) $tramiteId,
                    'notificacion_id' => (string) $notificacion->id,
                ]
            );
        }

        return $notificacion;
    }

    /**
     * Notificar al cliente cuando se actualiza el estado o se agrega un avance a su trámite.
     */
    public function notificarActualizacionTramite(
        int $clienteId,
        string $tramiteTipo,
        $tramiteId,
        string $nombreTramite,
        string $nuevoEstado,
        ?string $observaciones = null
    ): ?NotificacionCliente {
        $cliente = Cliente::find($clienteId);
        if (!$cliente) {
            return null;
        }

        $titulo = "📋 Actualización de Trámite: {$nombreTramite}";
        $mensaje = "Tu trámite ahora se encuentra en estado: " . ucfirst($nuevoEstado) . ".";
        if ($observaciones) {
            $mensaje .= " Detalle: {$observaciones}";
        }

        $notificacion = NotificacionCliente::create([
            'id_cliente' => $cliente->id_cliente,
            'tramite_tipo' => $tramiteTipo,
            'tramite_id' => $tramiteId,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'leido' => false,
            'data_extra' => [
                'nombre_tramite' => $nombreTramite,
                'estado' => $nuevoEstado,
                'fecha' => now()->toIso8601String(),
            ],
        ]);

        if (!empty($cliente->fcm_token)) {
            $this->sendPushNotification(
                $cliente->fcm_token,
                $titulo,
                $mensaje,
                [
                    'tipo' => 'tramite',
                    'tramite_tipo' => $tramiteTipo,
                    'tramite_id' => (string) $tramiteId,
                    'notificacion_id' => (string) $notificacion->id,
                ]
            );
        }

        return $notificacion;
    }

    /**
     * Notificar al cliente cuando el administrador responde en el chat en vivo.
     */
    public function notificarNuevoMensajeChat(
        int $clienteId,
        string $mensajeTexto,
        string $nombreRemitente = 'Notaría NESISTEMA'
    ): bool {
        $cliente = Cliente::find($clienteId);
        if (!$cliente || empty($cliente->fcm_token)) {
            return false;
        }

        $titulo = "💬 Mensaje de {$nombreRemitente}";
        $cuerpo = mb_strlen($mensajeTexto) > 100 ? (mb_substr($mensajeTexto, 0, 97) . '...') : $mensajeTexto;

        return $this->sendPushNotification(
            $cliente->fcm_token,
            $titulo,
            $cuerpo,
            [
                'tipo' => 'chat',
                'cliente_id' => (string) $clienteId,
                'remitente' => $nombreRemitente,
            ]
        );
    }

    /**
     * Envío universal de Notificaciones Push (FCM / Expo / Google Cloud).
     */
    public function sendPushNotification(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            // 1. Soporte para Expo Push Tokens
            if (str_starts_with($token, 'ExponentPushToken') || str_starts_with($token, 'ExpoPushToken')) {
                $response = Http::timeout(5)->post('https://exp.host/--/api/v2/push/send', [
                    'to' => $token,
                    'sound' => 'default',
                    'title' => $title,
                    'body' => $body,
                    'data' => $data,
                    'priority' => 'high',
                ]);

                return $response->successful();
            }

            // 2. Soporte para Firebase Cloud Messaging (FCM Legacy & v1)
            $fcmServerKey = config('services.fcm.server_key') ?: env('FCM_SERVER_KEY');
            if (!empty($fcmServerKey)) {
                $response = Http::timeout(6)
                    ->withHeaders([
                        'Authorization' => 'key=' . $fcmServerKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post('https://fcm.googleapis.com/fcm/send', [
                        'to' => $token,
                        'priority' => 'high',
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                            'sound' => 'default',
                            'click_action' => 'FCM_PLUGIN_ACTIVITY',
                            'icon' => 'ic_launcher',
                        ],
                        'data' => array_merge($data, [
                            'title' => $title,
                            'message' => $body,
                        ]),
                    ]);

                if ($response->successful()) {
                    Log::info("FCM Push sent successfully to [{$token}]: {$title}");
                    return true;
                } else {
                    Log::warning("FCM Push response error: " . $response->body());
                }
            }

            Log::info("Push Notification recorded for token [{$token}]: {$title} - {$body}");
            return true;
        } catch (\Exception $e) {
            Log::error("Error sending Push Notification: " . $e->getMessage());
            return false;
        }
    }
}
