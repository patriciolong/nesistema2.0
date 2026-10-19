<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Has table leads: " . (Schema::hasTable('leads') ? 'YES' : 'NO') . "\n";
if (Schema::hasTable('leads')) {
    $cols = Schema::getColumnListing('leads');
    print_r($cols);
    $rows = DB::table('leads')->orderBy('id', 'desc')->take(10)->get();
    echo "Leads count: " . $rows->count() . "\n";
    foreach ($rows as $r) {
        echo "ID: {$r->id} | {$r->nombre} {$r->apellidos} | Tel: {$r->telefono} | Servicio: {$r->servicio} | Mensaje: {$r->mensaje}\n";
    }
}
