<?php
$data = http_build_query([
    'nombre' => 'Carlos Alberto Mendoza',
    'telefono' => '+1 (718) 555-0199',
    'ciudad' => 'Queens, NY',
    'pais' => 'Estados Unidos',
    'lawyer_name' => 'Christian Moreno',
    'servicio' => 'Consulta con Abogado: Christian Moreno',
    'mensaje' => 'Hola, requiero un poder especial para venta de un inmueble en Guayaquil y coordinar con el abogado.',
    'origen' => 'directorio_abogados',
]);

$opts = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-type: application/x-www-form-urlencoded\r\nAccept: application/json\r\n",
        'content' => $data,
        'ignore_errors' => true
    ]
];

$context = stream_context_create($opts);
$result = file_get_contents('http://127.0.0.1:8001/consulta/enviar', false, $context);
echo "POST /consulta/enviar response:\n" . $result . "\n";
