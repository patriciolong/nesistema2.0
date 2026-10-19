<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cliente;
use App\Models\ChatConversacion;
use App\Models\ChatMensaje;
use App\Models\User;
use Illuminate\Support\Str;

echo "=== TEST CHAT LIVE ENVIROMENT ===\n";

$cliente = Cliente::first();
if (!$cliente) {
    echo "No cliente found, creating test cliente...\n";
    $cliente = Cliente::create([
        'c_identificacion' => '1310539588',
        'c_nombre' => 'Patricio',
        'c_apellido' => 'Long',
        'c_telefono' => '0999999999',
    ]);
}

$rawToken = 'test_chat_token_' . Str::random(10);
$cliente->api_token = hash('sha256', $rawToken);
$cliente->save();

echo "1. Cliente ID: {$cliente->id_cliente}, Nombre: {$cliente->c_nombre}\n";

// 1. Simular mensaje enviado por cliente
$conv = ChatConversacion::firstOrCreate(
    ['id_cliente' => $cliente->id_cliente],
    [
        'ultimo_mensaje' => 'Conversación iniciada',
        'ultimo_mensaje_at' => now(),
        'unread_admin' => 0,
        'unread_cliente' => 0,
        'estado' => 'activa',
    ]
);

$msg1 = ChatMensaje::create([
    'id_conversacion' => $conv->id,
    'remitente_tipo' => 'cliente',
    'nombre_remitente' => "{$cliente->c_nombre} {$cliente->c_apellido}",
    'mensaje' => 'Hola, tengo una pregunta sobre mi trámite de poder.',
    'leido' => false,
]);

$conv->update([
    'ultimo_mensaje' => $msg1->mensaje,
    'ultimo_mensaje_at' => now(),
    'unread_admin' => $conv->unread_admin + 1,
]);

echo "2. Mensaje de cliente creado. Conversacion ID: {$conv->id}, Unread Admin: {$conv->fresh()->unread_admin}\n";

// 2. Simular lectura por Admin
ChatMensaje::where('id_conversacion', $conv->id)
    ->where('remitente_tipo', 'cliente')
    ->update(['leido' => true]);
$conv->update(['unread_admin' => 0]);

echo "3. Admin leyó mensaje. Unread Admin: {$conv->fresh()->unread_admin}\n";

// 3. Simular respuesta de Admin
$user = User::first();
$msg2 = ChatMensaje::create([
    'id_conversacion' => $conv->id,
    'remitente_tipo' => 'admin',
    'id_usuario' => $user ? $user->id : null,
    'nombre_remitente' => $user ? $user->name : 'Notaría NESISTEMA',
    'mensaje' => 'Hola Patricio, con gusto te ayudamos. Tu poder está en redacción.',
    'leido' => false,
]);

$conv->update([
    'ultimo_mensaje' => $msg2->mensaje,
    'ultimo_mensaje_at' => now(),
    'unread_cliente' => $conv->unread_cliente + 1,
]);

echo "4. Admin respondió. Unread Cliente: {$conv->fresh()->unread_cliente}\n";

// 4. Simular lectura por Cliente
ChatMensaje::where('id_conversacion', $conv->id)
    ->where('remitente_tipo', 'admin')
    ->update(['leido' => true]);
$conv->update(['unread_cliente' => 0]);

echo "5. Cliente leyó respuesta. Unread Cliente: {$conv->fresh()->unread_cliente}\n";

$totalMensajes = ChatMensaje::where('id_conversacion', $conv->id)->count();
echo "6. Total mensajes en conversación: {$totalMensajes}\n";
echo "=== TEST COMPLETADO CON ÉXITO ===\n";
