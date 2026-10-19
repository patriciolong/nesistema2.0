<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$abogados = App\Models\Abogado::all();
echo "Total abogados: " . $abogados->count() . "\n\n";
foreach ($abogados as $a) {
    echo "ID: " . $a->id . "\n";
    echo "Nombre: " . $a->nombre_completo . "\n";
    echo "Foto field (raw): " . var_export($a->foto, true) . "\n";
    echo "Foto URL accessor: " . $a->foto_url . "\n";
    
    // Check if physical file exists
    if (!empty($a->foto)) {
        $storagePath = storage_path('app/public/' . $a->foto);
        $publicPath = public_path($a->foto);
        $publicStoragePath = public_path('storage/' . $a->foto);
        
        echo "  - storage_path: " . $storagePath . " (" . (file_exists($storagePath) ? "EXISTS" : "MISSING") . ")\n";
        echo "  - public_path: " . $publicPath . " (" . (file_exists($publicPath) ? "EXISTS" : "MISSING") . ")\n";
        echo "  - public_storage: " . $publicStoragePath . " (" . (file_exists($publicStoragePath) ? "EXISTS" : "MISSING") . ")\n";
    }
    echo "--------------------------------------------------------\n";
}
