<?php
$ctx = stream_context_create(['http' => ['ignore_errors' => true]]);
$res = file_get_contents('http://127.0.0.1:8000/', false, $ctx);
file_put_contents(__DIR__ . '/error_8000.html', $res);
echo "Saved, size: " . strlen($res) . "\n";
