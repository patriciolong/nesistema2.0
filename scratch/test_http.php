<?php
function testUrl($url) {
    $ctx = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 3]]);
    $res = @file_get_contents($url, false, $ctx);
    $status = $http_response_header[0] ?? 'NO RESPONSE';
    echo "URL: $url => Status: $status | Length: " . strlen($res) . "\n";
    if (strpos($res, 'RouteNotFoundException') !== false || strpos($res, 'Route [login] not defined') !== false) {
        echo "FOUND EXCEPTION IN RESPONSE!\n";
    }
}

testUrl('http://127.0.0.1:8000/');
testUrl('http://127.0.0.1:8000/login');
testUrl('http://127.0.0.1:8000/dashboard');
testUrl('http://127.0.0.1:8001/');
testUrl('http://127.0.0.1:8001/login');
testUrl('http://127.0.0.1:8001/abogados');
