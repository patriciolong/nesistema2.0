<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ClientAuthController;
use App\Http\Controllers\Api\ClientTramiteController;
use App\Http\Controllers\Api\ClientChatController;

echo "=== TESTING CLIENT APIS ===\n";

$cliente = Cliente::first();
if (!$cliente) {
    die("No clients found\n");
}

echo "Testing with Cliente ID: {$cliente->id_cliente}, Cédula: {$cliente->c_identificacion}\n";

// 1. Test Login
$reqLogin = Request::create('/api/v1/client/login', 'POST', [
    'identificacion' => $cliente->c_identificacion,
    'password' => $cliente->c_identificacion, // or password
]);
$authController = new ClientAuthController();
$resLogin = $authController->login($reqLogin);
$dataLogin = json_decode($resLogin->getContent(), true);
echo "1. Login result: " . ($dataLogin['success'] ? 'SUCCESS' : 'FAILED: ' . ($dataLogin['message'] ?? '')) . "\n";

$token = $dataLogin['token'] ?? null;
echo "   Token: " . substr($token ?? '', 0, 20) . "...\n";

// 2. Test Servicios (público)
$tramiteController = new ClientTramiteController();
$reqServicios = Request::create('/api/v1/client/servicios', 'GET');
$resServicios = $tramiteController->servicios($reqServicios);
$dataServicios = json_decode($resServicios->getContent(), true);
echo "2. Servicios count: " . count($dataServicios['servicios'] ?? []) . "\n";

// 3. Test Profile
if ($cliente) {
    $reqProfile = Request::create('/api/v1/client/profile', 'GET');
    $reqProfile->attributes->set('cliente', $cliente);
    $resProfile = $authController->profile($reqProfile);
    $dataProfile = json_decode($resProfile->getContent(), true);
    echo "3. Profile result: success=" . ($dataProfile['success'] ? 'true' : 'false') . ", Name=" . ($dataProfile['cliente']['nombre_completo'] ?? '') . "\n";

    // 4. Test Tramites
    $reqTramites = Request::create('/api/v1/client/tramites', 'GET');
    $reqTramites->attributes->set('cliente', $cliente);
    $resTramites = $tramiteController->index($reqTramites);
    $dataTramites = json_decode($resTramites->getContent(), true);
    echo "4. Tramites count: " . count($dataTramites['tramites'] ?? []) . "\n";

    // 5. Test Chat
    $chatController = new ClientChatController();
    $reqChat = Request::create('/api/v1/client/chat', 'GET');
    $reqChat->attributes->set('cliente', $cliente);
    $resChat = $chatController->getMensajes($reqChat);
    $dataChat = json_decode($resChat->getContent(), true);
    echo "5. Chat mensajes count: " . count($dataChat['mensajes'] ?? []) . "\n";
}

echo "=== ALL CLIENT APIS TESTED ===\n";
