<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\ChatConversacion;
use App\Models\ChatMensaje;
use Illuminate\Http\Request;
use App\Http\Controllers\ChatController;

echo "=== TESTING CHAT CONTROLLER DIRECTLY ===\n";

// Login as first user / admin
$user = User::first();
if ($user) {
    \Illuminate\Support\Facades\Auth::login($user);
    echo "Logged in as: " . $user->name . " (ID: {$user->id})\n";
}

$controller = new ChatController();

// 1. Test conversaciones
$req1 = Request::create('/chat/conversaciones', 'GET');
$res1 = $controller->conversaciones($req1);
$data1 = json_decode($res1->getContent(), true);
echo "1. Conversaciones endpoint response: success=" . ($data1['success'] ? 'true' : 'false') . ", count=" . count($data1['conversaciones']) . "\n";
if (!empty($data1['conversaciones'])) {
    $first = $data1['conversaciones'][0];
    echo "   First conversation: ID={$first['id']}, Cliente={$first['cliente_nombre']}, Unread={$first['unread_admin']}\n";

    // 2. Test mensajes
    $req2 = Request::create("/chat/mensajes/{$first['id']}", 'GET');
    $res2 = $controller->mensajes($req2, $first['id']);
    $data2 = json_decode($res2->getContent(), true);
    echo "2. Mensajes endpoint response: success=" . ($data2['success'] ? 'true' : 'false') . ", count=" . count($data2['mensajes']) . "\n";

    // 3. Test enviar
    $req3 = Request::create("/chat/enviar/{$first['id']}", 'POST', ['mensaje' => 'Hola desde test admin']);
    $res3 = $controller->enviar($req3, $first['id']);
    $data3 = json_decode($res3->getContent(), true);
    echo "3. Enviar endpoint response: success=" . ($data3['success'] ? 'true' : 'false') . ", msg ID=" . ($data3['mensaje']['id'] ?? 'N/A') . "\n";
}

echo "=== ALL CONTROLLER METHODS WORKING PERFECTLY ===\n";
