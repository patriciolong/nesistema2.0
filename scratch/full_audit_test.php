<?php

function testUrl($name, $url, $expectedCode = 200, $postData = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    if ($postData) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $status = ($httpCode == $expectedCode) ? "✅ OK ($httpCode)" : "❌ FAILED ($httpCode, expected $expectedCode) - $error";
    echo sprintf("[%-35s] %-45s -> %s\n", $name, $url, $status);
    return ['code' => $httpCode, 'response' => $response];
}

echo "=== AUDITORIA DE RUTAS: PAGINA WEB NOTARIA (http://127.0.0.1:8001) ===\n\n";

$routesWeb = [
    'Home / Inicio' => 'http://127.0.0.1:8001/',
    'Nosotros' => 'http://127.0.0.1:8001/nosotros',
    'Directorio Abogados' => 'http://127.0.0.1:8001/abogados',
    'Verificación Oficial' => 'http://127.0.0.1:8001/verificacion',
    'Contacto' => 'http://127.0.0.1:8001/contacto',
    'Servicios Principal' => 'http://127.0.0.1:8001/servicios',
    'Servicio: Poderes' => 'http://127.0.0.1:8001/servicios/poderes',
    'Servicio: Apostillas' => 'http://127.0.0.1:8001/servicios/apostillas',
    'Servicio: Traducciones' => 'http://127.0.0.1:8001/servicios/traducciones',
    'Servicio: Certificaciones' => 'http://127.0.0.1:8001/servicios/certificaciones',
    'Servicio: Impuestos/ITIN' => 'http://127.0.0.1:8001/servicios/impuestos-taxes-itins',
    'Servicio: Revocatorias' => 'http://127.0.0.1:8001/servicios/revocatorias',
    'Servicio: Acuerdos' => 'http://127.0.0.1:8001/servicios/acuerdos',
    'Servicio: Divorcios' => 'http://127.0.0.1:8001/servicios/divorcios',
    'Servicio: Juicios' => 'http://127.0.0.1:8001/servicios/juicios',
    'Servicio: Cartas Invitación' => 'http://127.0.0.1:8001/servicios/cartas-invitacion',
    'Servicio: Declaración Jurada' => 'http://127.0.0.1:8001/servicios/declaracion-juramentada',
    'Servicio: Envío Documentos' => 'http://127.0.0.1:8001/servicios/envio-de-documentos',
    'Servicio: Visas' => 'http://127.0.0.1:8001/servicios/tramites-de-visa',
    'Redirect 301: /poderes' => ['url' => 'http://127.0.0.1:8001/poderes', 'code' => 301],
    'Redirect 301: /apostillas' => ['url' => 'http://127.0.0.1:8001/apostillas', 'code' => 301],
    'Redirect 301: /login' => ['url' => 'http://127.0.0.1:8001/login', 'code' => 302],
];

foreach ($routesWeb as $name => $item) {
    $url = is_array($item) ? $item['url'] : $item;
    $code = is_array($item) ? $item['code'] : 200;
    testUrl($name, $url, $code);
}

echo "\n--- Test Formulario de Contacto (Lead) ---\n";
testUrl('POST /consulta/enviar', 'http://127.0.0.1:8001/consulta/enviar', 200, [
    'nombre' => 'Test Auditoria',
    'telefono' => '+1234567890',
    'mensaje' => 'Prueba de auditoria para despliegue a produccion',
    'servicio' => 'Poder Especial'
]);

echo "\n--- Test Formulario de Agendamiento de Citas ---\n";
testUrl('POST /citas/agendar', 'http://127.0.0.1:8001/citas/agendar', 200, [
    'nombre' => 'Test Auditoria Cita',
    'telefono' => '+1234567890',
    'sucursal' => 'Queens, NY',
    'servicio' => 'Poderes',
    'fecha_preferida' => date('Y-m-d', strtotime('+3 days')),
    'horario_preferido' => '10:00 AM'
]);

echo "\n\n=== AUDITORIA DE SISTEMA ADMINISTRATIVO: NESISTEMA (http://127.0.0.1:8000) ===\n\n";

$routesAdmin = [
    'Login (Root redirect)' => ['url' => 'http://127.0.0.1:8000/', 'code' => 302],
    'Login Page' => 'http://127.0.0.1:8000/login',
    'Portal Cliente App' => 'http://127.0.0.1:8000/portal',
    'API Public Abogados' => 'http://127.0.0.1:8000/api/public/abogados',
    'API Client Servicios' => 'http://127.0.0.1:8000/api/v1/client/servicios',
];

foreach ($routesAdmin as $name => $item) {
    $url = is_array($item) ? $item['url'] : $item;
    $code = is_array($item) ? $item['code'] : 200;
    testUrl($name, $url, $code);
}
