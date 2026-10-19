<?php

$content = file_get_contents(__DIR__ . '/../resources/views/portal/app.blade.php');

preg_match_all('/<script(?:\s+[^>]*)?>(.*?)<\/script>/is', $content, $matches);

echo "Found " . count($matches[1]) . " script tags\n";
foreach ($matches[1] as $idx => $script) {
    $trimmed = trim($script);
    if (!empty($trimmed)) {
        $file = __DIR__ . "/script_{$idx}.js";
        file_put_contents($file, $trimmed);
        echo "Script {$idx} length: " . strlen($trimmed) . " bytes -> saved to {$file}\n";
    }
}
