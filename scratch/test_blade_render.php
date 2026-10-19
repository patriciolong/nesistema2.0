<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$user = User::first();
if ($user) {
    Auth::login($user);
}

try {
    $rendered = view('chat.index', ['initialConversationId' => null])->render();
    echo "SUCCESS: Blade rendered without any errors! Length: " . strlen($rendered) . " bytes\n";
} catch (\Throwable $e) {
    echo "ERROR rendering blade: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
