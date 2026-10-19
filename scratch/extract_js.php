<?php

$content = file_get_contents(__DIR__ . '/../resources/views/portal/app.blade.php');

// Extract script tag content
if (preg_match('/<script>(.*?)<\/script>/s', $content, $matches)) {
    $js = $matches[1];
    file_put_contents(__DIR__ . '/test_script.js', $js);
    echo "Extracted JS length: " . strlen($js) . " bytes\n";
} else {
    echo "Could not find script block!\n";
}
