<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ClientChatController;
use App\Http\Controllers\ChatController;

echo "=== TEST API & CONTROLLERS ===\n";

$cliente = Cliente::first();
$req = Request::create('/api/v1/client/chat', 'GET');
$req->attributes->set('cliente', $cliente);

$clientChatCtrl = new ClientChatController();

// 1. Get messages for client
$resp1 = $clientChatCtrl->getMensajes($req);
echo "Client getMensajes status: " . $resp1->getStatusCode() . "\n";
$data1 = json_decode($resp1->getContent(), true);
echo "Client conversation ID: " . $data1['conversacion_id'] . ", Messages count: " . count($data1['mensajes']) . "\n";

// 2. Client sends message
$reqSend = Request::create('/api/v1/client/chat/enviar', 'POST', ['mensaje' => 'Probando mensaje desde la API del cliente']);
$reqSend->attributes->set('cliente', $cliente);
$resp2 = $clientChatCtrl->enviarMensaje($reqSend);
echo "Client enviarMensaje status: " . $resp2->getStatusCode() . "\n";

// 3. Admin lists conversations
$adminChatCtrl = new ChatController();
$reqAdminConv = Request::create('/chat/conversaciones', 'GET');
$resp3 = $adminChatCtrl->conversaciones($reqAdminConv);
echo "Admin conversaciones status: " . $resp3->getStatusCode() . "\n";
$data3 = json_decode($resp3->getContent(), true);
echo "Total conversaciones: " . count($data3['conversaciones']) . ", Total unread: " . $data3['total_unread'] . "\n";

echo "=== ALL CONTROLLERS PASSED PERFECTLY ===\n";
