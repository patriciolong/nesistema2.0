<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function testImage($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    return ['code' => $code, 'type' => $type];
}

echo "=== VERIFICACION DE FOTOS DE ABOGADOS EN NESISTEMA 2.0 (Puerto 8000) ===\n";
$abogados = App\Models\Abogado::all();
foreach ($abogados as $a) {
    $url = $a->foto_url;
    // Replace localhost with 127.0.0.1:8000 if needed for curl test
    $testUrl = str_replace('http://localhost', 'http://127.0.0.1:8000', $url);
    $res = testImage($testUrl);
    echo sprintf("[ID: %d] %-35s -> %-65s [%s - %s]\n", $a->id, $a->nombre_completo, $testUrl, ($res['code'] == 200 ? '✅ 200 OK' : '❌ ' . $res['code']), $res['type']);
}

echo "\n=== VERIFICACION DE FOTOS DE ABOGADOS EN PAGINA WEB NOTARIA (Puerto 8001) ===\n";
// Load PAGINA WEB NOTARIA models
$webApp = require __DIR__ . '/../PAGINA WEB NOTARIA/bootstrap/app.php';
$webKernel = $webApp->make(Illuminate\Contracts\Console\Kernel::class);
$webKernel->bootstrap();

$webAbogados = \App\Models\Abogado::all();
foreach ($webAbogados as $a) {
    $url = $a->foto_url;
    $testUrl = str_replace('http://localhost:8000', 'http://127.0.0.1:8000', $url);
    $testUrl = str_replace('http://localhost:8001', 'http://127.0.0.1:8001', $testUrl);
    $testUrl = str_replace('https://nesistema.com', 'http://127.0.0.1:8000', $testUrl);
    $res = testImage($testUrl);
    echo sprintf("[ID: %d] %-35s -> %-65s [%s - %s]\n", $a->id, $a->nombre_completo, $testUrl, ($res['code'] == 200 ? '✅ 200 OK' : '❌ ' . $res['code']), $res['type']);
}
