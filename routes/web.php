<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/portal', [App\Http\Controllers\PortalClienteController::class, 'index'])->name('portal.app');
Route::get('/app', [App\Http\Controllers\PortalClienteController::class, 'index'])->name('portal.mobile');

// Política de Privacidad y Términos de Uso de la App Móvil (Google Play / App Store)
Route::get('/privacidad', function () {
    return view('privacy');
})->name('privacy');
Route::get('/politica-de-privacidad', function () {
    return view('privacy');
});

Route::middleware('auth')->group(function () {
    // Rutas para Módulo de Análisis con IA y Dashboard Dinámico
    Route::get('dashboard/export', [App\Http\Controllers\DashboardController::class, 'export'])->name('dashboard.export');
    Route::post('dashboard/filter', [App\Http\Controllers\DashboardController::class, 'filterData'])->name('dashboard.filter');
    Route::post('dashboard/ask-ai', [App\Http\Controllers\DashboardController::class, 'askAi'])->name('dashboard.ask_ai');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas para Módulo de Cartera de Clientes (Cuentas por Cobrar & Créditos)
    Route::get('cartera', [App\Http\Controllers\CarteraController::class, 'index'])->name('cartera.index');
    Route::get('cartera/cliente/{cliente}/detalle', [App\Http\Controllers\CarteraController::class, 'getDetalleDeuda'])->name('cartera.detalle');
    Route::post('cartera/cobrar', [App\Http\Controllers\CarteraController::class, 'cobrar'])->name('cartera.cobrar');

    Route::get('clientes/export', [App\Http\Controllers\ClienteController::class, 'export'])->name('clientes.export');
    Route::post('clientes/importar', [App\Http\Controllers\ClienteController::class, 'import'])->name('clientes.import');
    Route::get('clientes/plantilla-excel', [App\Http\Controllers\ClienteController::class, 'downloadTemplate'])->name('clientes.template');
    
    // Rutas para Módulo Global de Trámites Realizados (Historial y Explorador General)
    Route::get('tramites/realizados', [App\Http\Controllers\TramiteGlobalController::class, 'index'])->name('tramites.realizados.index');
    Route::get('tramites/realizados/export', [App\Http\Controllers\TramiteGlobalController::class, 'export'])->name('tramites.realizados.export');
    
    Route::post('tramites/cambiar-estado', [App\Http\Controllers\TramiteController::class, 'cambiarEstado'])->name('tramites.cambiar_estado');
    Route::post('tramites/cobrar', [App\Http\Controllers\TramiteController::class, 'cobrarTramite'])->name('tramites.cobrar');
    Route::get('clientes/{cliente}/tramites', [App\Http\Controllers\TramiteController::class, 'index'])->name('clientes.tramites');
    Route::get('clientes/{cliente}/pagos', [App\Http\Controllers\ClienteController::class, 'pagos'])->name('clientes.pagos');
    Route::get('clientes/{cliente}/recibo-abono', [App\Http\Controllers\ClienteController::class, 'reciboAbono'])->name('clientes.recibo_abono');
    Route::post('clientes/{cliente}/abonar', [App\Http\Controllers\ClienteController::class, 'abonar'])->name('clientes.abonar');
    Route::resource('clientes', App\Http\Controllers\ClienteController::class);
    Route::resource('users', App\Http\Controllers\UserController::class);
    Route::resource('oficinas', App\Http\Controllers\OficinaController::class);
    Route::resource('tramites-varios', App\Http\Controllers\TramiteVarioController::class);
    Route::get('clientes/{cliente}/divorcios/create', [App\Http\Controllers\TramiteDivorcioController::class, 'create'])->name('divorcios.create');
    Route::post('clientes/{cliente}/divorcios', [App\Http\Controllers\TramiteDivorcioController::class, 'store'])->name('divorcios.store');
    Route::get('divorcios/{id}', [App\Http\Controllers\TramiteDivorcioController::class, 'show'])->name('divorcios.show');

    Route::get('clientes/{cliente}/impuestos/create', [App\Http\Controllers\TramiteImpuestoController::class, 'create'])->name('impuestos.create');
    Route::post('clientes/{cliente}/impuestos', [App\Http\Controllers\TramiteImpuestoController::class, 'store'])->name('impuestos.store');
    Route::get('impuestos/{id}', [App\Http\Controllers\TramiteImpuestoController::class, 'show'])->name('impuestos.show');

    Route::get('clientes/{cliente}/poderes/create', [App\Http\Controllers\TramitePoderController::class, 'create'])->name('poderes.create');
    Route::post('clientes/{cliente}/poderes', [App\Http\Controllers\TramitePoderController::class, 'store'])->name('poderes.store');
    Route::get('poderes/{id}', [App\Http\Controllers\TramitePoderController::class, 'show'])->name('poderes.show');

    // Rutas para Constructor de Trámites Dinámicos y Formularios
    Route::resource('tipo-tramites', App\Http\Controllers\TipoTramiteController::class);
    Route::get('clientes/{cliente}/tramites-personalizados/{tipoTramite}/create', [App\Http\Controllers\TramitePersonalizadoController::class, 'create'])->name('tramites-personalizados.create');
    Route::post('clientes/{cliente}/tramites-personalizados/{tipoTramite}', [App\Http\Controllers\TramitePersonalizadoController::class, 'store'])->name('tramites-personalizados.store');
    Route::get('tramites-personalizados/{tramitePersonalizado}', [App\Http\Controllers\TramitePersonalizadoController::class, 'show'])->name('tramites-personalizados.show');

    // Rutas para Módulo de Plantillas y Diseñador de Documentos
    Route::post('plantillas/upload-image', [App\Http\Controllers\PlantillaController::class, 'uploadImage'])->name('plantillas.upload_image');
    Route::post('plantillas/import-pdf', [App\Http\Controllers\PlantillaController::class, 'importPdf'])->name('plantillas.import_pdf');
    Route::post('plantillas/guardar-documento-cliente', [App\Http\Controllers\PlantillaController::class, 'guardarDocumentoCliente'])->name('plantillas.guardar_documento_cliente');
    Route::post('plantillas/{plantilla}/guardar-expediente', [App\Http\Controllers\PlantillaController::class, 'guardarDocumentoCliente'])->name('plantillas.guardar_expediente');
    Route::get('plantillas/{plantilla}/generar', [App\Http\Controllers\PlantillaController::class, 'generar'])->name('plantillas.generar');
    Route::post('plantillas/{plantilla}/duplicar', [App\Http\Controllers\PlantillaController::class, 'duplicar'])->name('plantillas.duplicar');
    Route::post('plantillas/{plantilla}/descargar-pdf', [App\Http\Controllers\PlantillaController::class, 'descargarPdf'])->name('plantillas.descargar_pdf');
    Route::post('plantillas/{plantilla}/descargar-docx', [App\Http\Controllers\PlantillaController::class, 'descargarDocx'])->name('plantillas.descargar_docx');
    
    // Rutas para Membretes (Encabezados y Pies de Página)
    Route::post('plantillas-membretes/upload-image', [App\Http\Controllers\PlantillaMembreteController::class, 'uploadImage'])->name('plantillas-membretes.upload_image');
    Route::resource('plantillas-membretes', App\Http\Controllers\PlantillaMembreteController::class)->parameters(['plantillas-membretes' => 'membrete']);

    // Expediente de Documentos Emitidos desde Plantillas
    Route::get('plantillas/expediente', [App\Http\Controllers\PlantillaController::class, 'expediente'])->name('plantillas.expediente');
    Route::delete('plantillas/expediente/{documento}', [App\Http\Controllers\PlantillaController::class, 'destroyExpediente'])->name('plantillas.expediente.destroy');

    Route::resource('plantillas', App\Http\Controllers\PlantillaController::class);

    // Rutas para Módulo Financiero: Bancos y Tarjetas
    Route::resource('bancos', App\Http\Controllers\BancoController::class);
    Route::resource('tarjetas', App\Http\Controllers\TarjetaController::class);

    // Rutas para Módulo Operativo de Caja (Apertura, Arqueo, Cierre, Historial)
    Route::get('cajas', [App\Http\Controllers\CajaController::class, 'index'])->name('cajas.index');
    Route::post('cajas/abrir', [App\Http\Controllers\CajaController::class, 'abrir'])->name('cajas.abrir');
    Route::post('cajas/movimiento-manual', [App\Http\Controllers\CajaController::class, 'registrarMovimientoManual'])->name('cajas.movimiento_manual');
    Route::get('cajas/cerrar', [App\Http\Controllers\CajaController::class, 'showCerrar'])->name('cajas.cerrar');
    Route::post('cajas/check-cuadre', [App\Http\Controllers\CajaController::class, 'checkCuadre'])->name('cajas.check_cuadre');
    Route::post('cajas/cerrar', [App\Http\Controllers\CajaController::class, 'cerrar'])->name('cajas.procesar_cierre');
    Route::get('cajas/historial', [App\Http\Controllers\CajaController::class, 'historial'])->name('cajas.historial');
    Route::get('cajas/{id}/acta-pdf', [App\Http\Controllers\CajaController::class, 'actaPdf'])->name('cajas.acta_pdf');

    // Rutas para Módulo de Reportes de Caja y Desempeño (Exclusivo Administrador / Supervisor)
    Route::get('reportes/cajas', [App\Http\Controllers\CajaReporteController::class, 'index'])->name('reportes.cajas.index');
    Route::get('reportes/cajas/export', [App\Http\Controllers\CajaReporteController::class, 'export'])->name('reportes.cajas.export');
    Route::get('reportes/cajas/desempeno', [App\Http\Controllers\CajaReporteController::class, 'desempeno'])->name('reportes.cajas.desempeno');
    Route::get('reportes/cajas/{id}', [App\Http\Controllers\CajaReporteController::class, 'show'])->name('reportes.cajas.show');

    // Rutas para Módulo de Reportería de Cartera & Cuentas por Cobrar
    Route::get('reportes/cartera', [App\Http\Controllers\CarteraReporteController::class, 'index'])->name('reportes.cartera.index');
    Route::get('reportes/cartera/pdf', [App\Http\Controllers\CarteraReporteController::class, 'pdf'])->name('reportes.cartera.pdf');
    Route::get('reportes/cartera/excel', [App\Http\Controllers\CarteraReporteController::class, 'excel'])->name('reportes.cartera.excel');

    // Rutas para Módulo de Control de Precios y Auditoría de Desviaciones (±5%)
    Route::get('reportes/control-precios', [App\Http\Controllers\ControlTramitesController::class, 'index'])->name('reportes.control_precios.index');

    // Rutas para Módulo de Gestión de Permisos Granulares
    Route::get('permisos', [App\Http\Controllers\PermisoController::class, 'index'])->name('permisos.index');
    Route::get('permisos/{user}/edit', [App\Http\Controllers\PermisoController::class, 'edit'])->name('permisos.edit');
    Route::put('permisos/{user}', [App\Http\Controllers\PermisoController::class, 'update'])->name('permisos.update');
    Route::post('permisos/{user}/preset', [App\Http\Controllers\PermisoController::class, 'applyPreset'])->name('permisos.preset');

    // Rutas para Módulo de Chat en Vivo con Clientes
    Route::get('chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('chat/conversaciones', [App\Http\Controllers\ChatController::class, 'conversaciones'])->name('chat.conversaciones');
    Route::get('chat/mensajes/{id}', [App\Http\Controllers\ChatController::class, 'mensajes'])->name('chat.mensajes');
    Route::post('chat/enviar/{id}', [App\Http\Controllers\ChatController::class, 'enviar'])->name('chat.enviar');
    Route::get('chat/unread-count', [App\Http\Controllers\ChatController::class, 'unreadCount'])->name('chat.unread_count');

    // Rutas para Módulo de Reseñas y Calificaciones de Clientes
    Route::get('resenas', [App\Http\Controllers\ResenaController::class, 'index'])->name('resenas.index');
    Route::get('resenas/export/excel', [App\Http\Controllers\ResenaController::class, 'exportExcel'])->name('resenas.export_excel');
    Route::delete('resenas/{id}', [App\Http\Controllers\ResenaController::class, 'destroy'])->name('resenas.destroy');

    // Rutas para Módulo de Gestión de Abogados y Directorio Web
    Route::post('abogados/{abogado}/toggle-estado', [App\Http\Controllers\AbogadoController::class, 'toggleEstado'])->name('abogados.toggle_estado');
    Route::resource('abogados', App\Http\Controllers\AbogadoController::class);

    // Rutas para Módulo de Solicitudes Web y Leads
    Route::get('leads', [App\Http\Controllers\LeadController::class, 'index'])->name('leads.index');
    Route::post('leads/{lead}/cambiar-estado', [App\Http\Controllers\LeadController::class, 'toggleEstado'])->name('leads.cambiar_estado');
    Route::delete('leads/{lead}', [App\Http\Controllers\LeadController::class, 'destroy'])->name('leads.destroy');
});

// Endpoint Público para sincronización con Página Web Notaría
Route::get('api/public/abogados', [App\Http\Controllers\AbogadoController::class, 'apiPublicList'])->name('api.public.abogados.list');
Route::get('api/public/abogados/{id}', [App\Http\Controllers\AbogadoController::class, 'apiPublicShow'])->name('api.public.abogados.show');

// Ruta de respaldo para servir archivos de storage en Hostinger si el symlink no está activo
Route::get('storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath)) {
        abort(404);
    }
    $file = file_get_contents($fullPath);
    $type = mime_content_type($fullPath) ?: 'image/jpeg';
    return response($file, 200)
        ->header('Content-Type', $type)
        ->header('Cache-Control', 'public, max-age=86400');
})->where('path', '.*')->name('storage.fallback');

// Ruta de utilidad para crear el enlace simbólico storage:link desde el navegador en Hostinger
Route::get('crear-storage-link', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('storage:link');
        return response()->json(['success' => true, 'message' => 'Enlace de storage:link creado exitosamente en el servidor.']);
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
});

require __DIR__.'/auth.php';

