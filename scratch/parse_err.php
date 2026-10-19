<?php
$html = file_get_contents(__DIR__ . '/error_8000.html');
preg_match('/<title>(.*?)<\/title>/is', $html, $m1);
echo "Title: " . ($m1[1] ?? 'None') . "\n";

// Find any exception text or route list
if (preg_match_all('/class="exception_title[^"]*">(.*?)</is', $html, $m2)) {
    print_r($m2[1]);
}
if (preg_match_all('/class="header_title[^"]*">(.*?)</is', $html, $m3)) {
    print_r($m3[1]);
}
// Strip tags and look for RouteNotFoundException
$clean = strip_tags($html);
$lines = explode("\n", $clean);
foreach ($lines as $i => $line) {
    if (stripos($line, 'RouteNotFoundException') !== false || stripos($line, 'Route [login] not defined') !== false) {
        echo "Line $i: " . trim($line) . "\n";
        for ($j = max(0, $i - 5); $j <= min(count($lines)-1, $i + 15); $j++) {
            echo "   [$j] " . trim($lines[$j]) . "\n";
        }
        break;
    }
}
