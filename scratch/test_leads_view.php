<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use App\Models\Abogado;
use App\Models\User;

$user = User::first();
if ($user) {
    auth()->login($user);
}

try {
    $leads = Lead::orderBy('created_at', 'desc')->paginate(20);
    $totalLeads = Lead::count();
    $totalAbogadosLeads = Lead::where('servicio', 'like', '%abogado%')->count();
    $totalPendientes = Lead::where('synced_to_nesistema', false)->count();
    $totalAtendidos = Lead::where('synced_to_nesistema', true)->count();

    $renderedLeads = view('leads.index', compact('leads', 'totalLeads', 'totalAbogadosLeads', 'totalPendientes', 'totalAtendidos'))->render();
    echo "Leads view rendered successfully! Size: " . strlen($renderedLeads) . "\n";

    $abogados = Abogado::paginate(15);
    $ciudades = Abogado::distinct()->whereNotNull('ciudad')->pluck('ciudad');
    $especialidades = Abogado::distinct()->whereNotNull('especialidad')->pluck('especialidad');
    $totalActivos = Abogado::where('estado', 'activo')->count();
    $totalInactivos = Abogado::where('estado', 'inactivo')->count();

    $renderedAbogados = view('abogados.index', compact('abogados', 'ciudades', 'especialidades', 'totalActivos', 'totalInactivos'))->render();
    echo "Abogados view rendered successfully! Size: " . strlen($renderedAbogados) . "\n";
} catch (\Throwable $e) {
    echo "Render error: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
}
