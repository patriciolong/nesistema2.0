<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\DocumentoGenerado;
use App\Models\Plantilla;
use App\Models\ResenaTramite;
use App\Models\TipoTramite;
use App\Models\TramitePersonalizado;
use App\Models\TramiteVario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ClientTramiteController extends Controller
{
    /**
     * Get all procedures for the authenticated client, grouped & categorized.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Cliente $cliente */
        $cliente = $request->attributes->get('cliente');
        $clienteId = $cliente->id_cliente;

        $categoriaFilter = $request->input('categoria', 'todas'); // todas, poderes, divorcios, impuestos, varios, formularios, documentos
        $estadoFilter = $request->input('estado', 'todos'); // todos, en_proceso, en_revision, listo, entregado
        $buscar = strtolower(trim($request->input('buscar', '')));

        $tramites = collect();

        // 1. Trámites Personalizados / Formularios Dinámicos
        if ($categoriaFilter === 'todas' || $categoriaFilter === 'formularios') {
            $personalizados = TramitePersonalizado::with('tipoTramite')
                ->where('id_cliente', $clienteId)
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($personalizados as $tp) {
                $nombre = $tp->tipoTramite ? $tp->tipoTramite->nombre : 'Trámite Personalizado';
                $color = $tp->tipoTramite ? $tp->tipoTramite->color_gradient : 'linear-gradient(135deg, #4f46e5, #7c3aed)';
                $fechaRaw = $tp->created_at ?: ($tp->fecha ?: now());
                $fechaStr = is_string($fechaRaw) ? $fechaRaw : $fechaRaw->format('Y-m-d H:i:s');
                
                $tramites->push([
                    'id' => (int) $tp->id,
                    'categoria' => 'formularios',
                    'categoria_label' => 'Formulario Notarial',
                    'categoria_icono' => 'document-text',
                    'nombre' => $nombre,
                    'descripcion' => $tp->observaciones ?: 'Formulario dinámico registrado',
                    'estado' => $tp->estado ?: 'en_proceso',
                    'estado_label' => $this->getEstadoLabel($tp->estado ?: 'en_proceso'),
                    'estado_color' => $this->getEstadoColor($tp->estado ?: 'en_proceso'),
                    'color_badge' => $color,
                    'fecha' => date('Y-m-d', strtotime($fechaStr)),
                    'fecha_formateada' => date('d M, Y', strtotime($fechaStr)),
                    'fecha_completado' => !empty($tp->fecha_completado) ? date('d M, Y h:i A', strtotime((string)$tp->fecha_completado)) : null,
                    'oficina' => $tp->oficina ?: $cliente->c_oficina_registro,
                    'asesor' => $tp->usuario ?: 'Asesor Notarial',
                    'costo_total' => (float) $tp->valor_tramite,
                    'abono' => (float) $tp->abono_tramite,
                    'saldo' => (float) $tp->saldo,
                    'detalles' => $tp->datos_formulario ?: [],
                    'documento_pdf' => $tp->documento_pdf,
                    'documento_pdf_url' => $tp->documento_pdf ? asset($tp->documento_pdf) : null,
                    'download_url' => route('api.client.tramites.download', ['categoria' => 'formularios', 'id' => $tp->id]),
                    'porcentaje_avance' => $this->getPorcentajeAvance($tp->estado ?: 'en_proceso'),
                ]);
            }
        }

        // 2. Poderes
        if ($categoriaFilter === 'todas' || $categoriaFilter === 'poderes') {
            $poderes = DB::table('tramite_poderes')
                ->where('id_cliente', $clienteId)
                ->orderBy('id_tram_poderes', 'desc')
                ->get();

            foreach ($poderes as $p) {
                $fechaRaw = $p->tp_fecha ?: date('Y-m-d');
                $tramites->push([
                    'id' => (int) $p->id_tram_poderes,
                    'categoria' => 'poderes',
                    'categoria_label' => 'Poder Notarial',
                    'categoria_icono' => 'document-duplicate',
                    'nombre' => 'Poder Notarial: ' . ($p->tp_razon_otorga_poder ?: 'General'),
                    'descripcion' => "Otorga poder a: {$p->tp_nombres_otorga_poder} (C.I: {$p->tp_cedulla_otorga_poder})",
                    'estado' => $p->estado ?: 'en_proceso',
                    'estado_label' => $this->getEstadoLabel($p->estado ?: 'en_proceso'),
                    'estado_color' => $this->getEstadoColor($p->estado ?: 'en_proceso'),
                    'color_badge' => 'linear-gradient(135deg, #0284c7, #0369a1)',
                    'fecha' => date('Y-m-d', strtotime($fechaRaw)),
                    'fecha_formateada' => date('d M, Y', strtotime($fechaRaw)),
                    'fecha_completado' => !empty($p->fecha_completado) ? date('d M, Y h:i A', strtotime((string)$p->fecha_completado)) : null,
                    'oficina' => $p->tp_oficina ?: $cliente->c_oficina_registro,
                    'asesor' => 'Asesoría Legal',
                    'costo_total' => (float) $p->tp_costo_tramite,
                    'abono' => (float) $p->tp_abono_tramite,
                    'saldo' => (float) $p->tp_saldo,
                    'detalles' => [
                        'Apoderado' => $p->tp_nombres_otorga_poder,
                        'Identificación Apoderado' => $p->tp_cedulla_otorga_poder,
                        'Razón' => $p->tp_razon_otorga_poder,
                        'Envío a' => $p->tp_enviar_nombrede ?: 'N/A',
                        'Ciudad' => $p->tp_ciudad_enviar ?: 'N/A',
                    ],
                    'documento_pdf' => $p->documento_pdf ?? null,
                    'documento_pdf_url' => !empty($p->documento_pdf) ? asset($p->documento_pdf) : null,
                    'download_url' => route('api.client.tramites.download', ['categoria' => 'poderes', 'id' => $p->id_tram_poderes]),
                    'porcentaje_avance' => $this->getPorcentajeAvance($p->estado ?: 'en_proceso'),
                ]);
            }
        }

        // 3. Divorcios
        if ($categoriaFilter === 'todas' || $categoriaFilter === 'divorcios') {
            $divorcios = DB::table('tramite_divorcio')
                ->where('id_cliente', $clienteId)
                ->orderBy('id_tram_div', 'desc')
                ->get();

            foreach ($divorcios as $d) {
                $tipoDiv = $d->td_consensual ? 'Mutuo Consentimiento' : ($d->td_controvertido ? 'Controvertido' : 'Notarial');
                $fechaRaw = $d->td_fecha ?: date('Y-m-d');
                $tramites->push([
                    'id' => (int) $d->id_tram_div,
                    'categoria' => 'divorcios',
                    'categoria_label' => 'Trámite de Divorcio',
                    'categoria_icono' => 'heart-broken',
                    'nombre' => "Divorcio: {$tipoDiv}",
                    'descripcion' => "Cónyuge: {$d->td_nombre_c} ({$d->td_identificacion_c})",
                    'estado' => $d->estado ?: 'en_proceso',
                    'estado_label' => $this->getEstadoLabel($d->estado ?: 'en_proceso'),
                    'estado_color' => $this->getEstadoColor($d->estado ?: 'en_proceso'),
                    'color_badge' => 'linear-gradient(135deg, #e11d48, #be123c)',
                    'fecha' => date('Y-m-d', strtotime($fechaRaw)),
                    'fecha_formateada' => date('d M, Y', strtotime($fechaRaw)),
                    'fecha_completado' => !empty($d->fecha_completado) ? date('d M, Y h:i A', strtotime((string)$d->fecha_completado)) : null,
                    'oficina' => $d->td_oficina ?: $cliente->c_oficina_registro,
                    'asesor' => 'Departamento Legal',
                    'costo_total' => (float) $d->td_valor,
                    'abono' => (float) $d->td_abono,
                    'saldo' => (float) $d->td_saldo,
                    'detalles' => [
                        'Cónyuge' => $d->td_nombre_c,
                        'Identificación Cónyuge' => $d->td_identificacion_c,
                        'Lugar de Matrimonio' => $d->td_lugar_matrimonio ?: 'N/A',
                        'Fecha Matrimonio' => $d->td_fecha_matrimonio ?: 'N/A',
                        'Tipo de Proceso' => $tipoDiv,
                    ],
                    'documento_pdf' => $d->documento_pdf ?? null,
                    'documento_pdf_url' => !empty($d->documento_pdf) ? asset($d->documento_pdf) : null,
                    'download_url' => route('api.client.tramites.download', ['categoria' => 'divorcios', 'id' => $d->id_tram_div]),
                    'porcentaje_avance' => $this->getPorcentajeAvance($d->estado ?: 'en_proceso'),
                ]);
            }
        }

        // 4. Impuestos (Taxes / ITIN)
        if ($categoriaFilter === 'todas' || $categoriaFilter === 'impuestos') {
            $impuestos = DB::table('tramite_impuestos')
                ->where('id_cliente', $clienteId)
                ->orderBy('id_tram_impuestos', 'desc')
                ->get();

            foreach ($impuestos as $imp) {
                $fechaRaw = $imp->ti_fecha ?: date('Y-m-d');
                $tramites->push([
                    'id' => (int) $imp->id_tram_impuestos,
                    'categoria' => 'impuestos',
                    'categoria_label' => 'Impuestos / Tax Return',
                    'categoria_icono' => 'calculator',
                    'nombre' => 'Declaración de Impuestos ' . ($imp->ti_anio_reporte ?: date('Y')),
                    'descripcion' => "Reporte de impuestos año {$imp->ti_anio_reporte} - ITIN/SSN",
                    'estado' => $imp->estado ?: 'en_proceso',
                    'estado_label' => $this->getEstadoLabel($imp->estado ?: 'en_proceso'),
                    'estado_color' => $this->getEstadoColor($imp->estado ?: 'en_proceso'),
                    'color_badge' => 'linear-gradient(135deg, #059669, #047857)',
                    'fecha' => date('Y-m-d', strtotime($fechaRaw)),
                    'fecha_formateada' => date('d M, Y', strtotime($fechaRaw)),
                    'fecha_completado' => !empty($imp->fecha_completado) ? date('d M, Y h:i A', strtotime((string)$imp->fecha_completado)) : null,
                    'oficina' => $imp->ti_oficina ?: $cliente->c_oficina_registro,
                    'asesor' => 'Contabilidad / Taxes',
                    'costo_total' => (float) $imp->ti_costo_tramite,
                    'abono' => (float) $imp->ti_abono_tramite,
                    'saldo' => (float) $imp->ti_saldo,
                    'detalles' => [
                        'Año Reporte' => $imp->ti_anio_reporte,
                        'Profesión' => $imp->ti_profesion ?: 'N/A',
                        'Estado Civil' => $imp->ti_ecivil ?: 'N/A',
                        'Dependientes' => $imp->ti_dependientes ?: '0',
                    ],
                    'documento_pdf' => $imp->documento_pdf ?? null,
                    'documento_pdf_url' => !empty($imp->documento_pdf) ? asset($imp->documento_pdf) : null,
                    'download_url' => route('api.client.tramites.download', ['categoria' => 'impuestos', 'id' => $imp->id_tram_impuestos]),
                    'porcentaje_avance' => $this->getPorcentajeAvance($imp->estado ?: 'en_proceso'),
                ]);
            }
        }

        // 5. Trámites Varios
        if ($categoriaFilter === 'todas' || $categoriaFilter === 'varios') {
            $varios = TramiteVario::where('id_cliente', $clienteId)
                ->orderBy('id_tramite_varios', 'desc')
                ->get();

            foreach ($varios as $tv) {
                $fechaRaw = $tv->tv_fecha ?: date('Y-m-d');
                $tramites->push([
                    'id' => (int) $tv->id_tramite_varios,
                    'categoria' => 'varios',
                    'categoria_label' => 'Trámite Notarial Vario',
                    'categoria_icono' => 'folder',
                    'nombre' => 'Trámite: ' . ($tv->tv_motivo ?: 'Gestión Notarial'),
                    'descripcion' => $tv->tv_observaciones ?: ($tv->tv_tip_documento ?: 'Servicio notarial múltiple'),
                    'estado' => $tv->estado ?: 'en_proceso',
                    'estado_label' => $this->getEstadoLabel($tv->estado ?: 'en_proceso'),
                    'estado_color' => $this->getEstadoColor($tv->estado ?: 'en_proceso'),
                    'color_badge' => 'linear-gradient(135deg, #d97706, #b45309)',
                    'fecha' => date('Y-m-d', strtotime($fechaRaw)),
                    'fecha_formateada' => date('d M, Y', strtotime($fechaRaw)),
                    'fecha_completado' => !empty($tv->fecha_completado) ? date('d M, Y h:i A', strtotime((string)$tv->fecha_completado)) : null,
                    'oficina' => $tv->tv_oficina ?: $cliente->c_oficina_registro,
                    'asesor' => 'Notaría',
                    'costo_total' => (float) $tv->tv_valor_tramite,
                    'abono' => (float) $tv->tv_abono_tramite,
                    'saldo' => (float) $tv->tv_saldo,
                    'detalles' => [
                        'Tipo Documento' => $tv->tv_tip_documento ?: 'N/A',
                        'Notarización' => $tv->tv_notarizacion ? 'Sí' : 'No',
                        'Apostilla' => $tv->tv_apostilla ? 'Sí' : 'No',
                        'Traducción' => $tv->tv_traducciones ? 'Sí' : 'No',
                        'Certificación' => $tv->tv_certificacion ? 'Sí' : 'No',
                    ],
                    'documento_pdf' => $tv->documento_pdf,
                    'documento_pdf_url' => $tv->documento_pdf ? asset($tv->documento_pdf) : null,
                    'download_url' => route('api.client.tramites.download', ['categoria' => 'varios', 'id' => $tv->id_tramite_varios]),
                    'porcentaje_avance' => $this->getPorcentajeAvance($tv->estado ?: 'en_proceso'),
                ]);
            }
        }

        // Aplicar filtros de estado si corresponde
        if ($estadoFilter !== 'todos') {
            $tramites = $tramites->filter(function ($t) use ($estadoFilter) {
                return $t['estado'] === $estadoFilter;
            });
        }

        // Aplicar filtro de búsqueda si corresponde
        if ($buscar !== '') {
            $tramites = $tramites->filter(function ($t) use ($buscar) {
                return str_contains(strtolower($t['nombre']), $buscar) ||
                       str_contains(strtolower($t['descripcion']), $buscar) ||
                       str_contains(strtolower($t['categoria_label']), $buscar) ||
                       str_contains(strtolower($t['oficina']), $buscar);
            });
        }

        // Obtener reseñas existentes del cliente con fallback seguro
        $resenasExistentes = collect();
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('resenas_tramite')) {
                $resenasExistentes = ResenaTramite::where('id_cliente', $clienteId)
                    ->get()
                    ->keyBy(function ($r) {
                        return "{$r->tramite_tipo}_{$r->tramite_id}";
                    });
            }
        } catch (\Throwable $e) {
            $resenasExistentes = collect();
        }

        $allTramites = $tramites->map(function ($t) use ($resenasExistentes) {
            $key = "{$t['categoria']}_{$t['id']}";
            $resena = $resenasExistentes->get($key);
            $t['resena'] = $resena ? [
                'id' => $resena->id,
                'calificacion' => (int) $resena->calificacion,
                'atencion_personal' => (int) $resena->atencion_personal,
                'rapidez' => (int) $resena->rapidez,
                'comentario' => $resena->comentario,
                'recomendaria' => (bool) $resena->recomendaria,
                'fecha_formateada' => $resena->fecha_resena ? $resena->fecha_resena->format('d M, Y') : '',
            ] : null;
            $t['calificado'] = !is_null($resena);
            return $t;
        })->sortByDesc('fecha')->values();

        // Calcular estadísticas
        $stats = [
            'total' => $allTramites->count(),
            'en_proceso' => $allTramites->where('estado', 'en_proceso')->count(),
            'en_revision' => $allTramites->where('estado', 'en_revision')->count(),
            'listos' => $allTramites->where('estado', 'listo')->count(),
            'entregados' => $allTramites->where('estado', 'entregado')->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'tramites' => $allTramites,
        ]);
    }

    /**
     * Get single procedure detail with progress timeline.
     */
    public function show(Request $request, string $categoria, $id): JsonResponse
    {
        /** @var Cliente $cliente */
        $cliente = $request->attributes->get('cliente');

        // Reutilizamos index para obtener el item específico enriquecido
        $fakeRequest = new Request([
            'categoria' => $categoria,
            'estado' => 'todos',
        ]);
        $fakeRequest->attributes->set('cliente', $cliente);

        $response = $this->index($fakeRequest);
        $data = $response->getData(true);

        $item = collect($data['tramites'] ?? [])->firstWhere('id', (int) $id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado.',
            ], 404);
        }

        // Construir la línea de tiempo de avance visual
        $timeline = [
            [
                'fase' => 1,
                'titulo' => 'Solicitud Recibida',
                'descripcion' => 'Recepción y apertura del expediente del trámite',
                'completado' => true,
                'activo' => false,
                'fecha' => $item['fecha_formateada'] ?: 'Iniciado',
            ],
            [
                'fase' => 2,
                'titulo' => 'En Elaboración Notarial',
                'descripcion' => 'Redacción legal y recopilación de documentación requerida',
                'completado' => in_array($item['estado'], ['en_revision', 'listo', 'entregado']),
                'activo' => $item['estado'] === 'en_proceso',
                'fecha' => $item['estado'] === 'en_proceso' ? 'En curso' : null,
            ],
            [
                'fase' => 3,
                'titulo' => 'En Revisión Legal / Firma',
                'descripcion' => 'Validación notarial, revisión de firmas y sellos oficiales',
                'completado' => in_array($item['estado'], ['listo', 'entregado']),
                'activo' => $item['estado'] === 'en_revision',
                'fecha' => $item['estado'] === 'en_revision' ? 'En revisión' : null,
            ],
            [
                'fase' => 4,
                'titulo' => '¡Listo para entregar!',
                'descripcion' => 'Trámite completado. Documento listo para entrega en tu oficina notarial o descarga',
                'completado' => in_array($item['estado'], ['listo', 'entregado']),
                'activo' => $item['estado'] === 'listo',
                'fecha' => $item['fecha_completado'] ?: ($item['estado'] === 'listo' ? '¡Listo para entregar!' : null),
            ],
        ];

        return response()->json([
            'success' => true,
            'tramite' => $item,
            'timeline' => $timeline,
        ]);
    }

    /**
     * Download or stream the completed PDF document for the specified procedure.
     */
    public function download(Request $request, string $categoria, $id)
    {
        /** @var Cliente $cliente */
        $cliente = $request->attributes->get('cliente');
        $clienteId = $cliente->id_cliente;

        $docPath = null;
        $nombreDocumento = 'Documento_' . ucfirst($categoria) . '_' . $id . '.pdf';

        switch ($categoria) {
            case 'formularios':
            case 'personalizados':
                $tp = TramitePersonalizado::with('tipoTramite')->where('id', $id)->where('id_cliente', $clienteId)->first();
                if ($tp) {
                    $docPath = $tp->documento_pdf;
                    $tipoName = $tp->tipoTramite ? $tp->tipoTramite->nombre : 'Tramite_Personalizado';
                    $nombreDocumento = 'Tramite_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $tipoName) . '_' . $id . '.pdf';
                }
                break;

            case 'poderes':
                $p = DB::table('tramite_poderes')->where('id_tram_poderes', $id)->where('id_cliente', $clienteId)->first();
                if ($p) {
                    $docPath = $p->documento_pdf;
                    $nombreDocumento = 'Poder_Notarial_' . ($p->tp_razon_otorga_poder ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $p->tp_razon_otorga_poder) : $id) . '.pdf';
                }
                break;

            case 'divorcios':
                $d = DB::table('tramite_divorcio')->where('id_tram_div', $id)->where('id_cliente', $clienteId)->first();
                if ($d) {
                    $docPath = $d->documento_pdf;
                    $nombreDocumento = 'Tramite_Divorcio_' . $id . '.pdf';
                }
                break;

            case 'impuestos':
                $imp = DB::table('tramite_impuestos')->where('id_tram_impuestos', $id)->where('id_cliente', $clienteId)->first();
                if ($imp) {
                    $docPath = $imp->documento_pdf;
                    $nombreDocumento = 'Declaracion_Impuestos_' . ($imp->ti_anio_reporte ?: $id) . '.pdf';
                }
                break;

            case 'varios':
                $tv = TramiteVario::where('id_tramite_varios', $id)->where('id_cliente', $clienteId)->first();
                if ($tv) {
                    $docPath = $tv->documento_pdf;
                    $nombreDocumento = 'Tramite_Vario_' . ($tv->tv_motivo ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $tv->tv_motivo) : $id) . '.pdf';
                }
                break;

            case 'documentos':
                $doc = DocumentoGenerado::with('plantilla')->where('id', $id)->where('id_cliente', $clienteId)->first();
                if ($doc) {
                    $docPath = $doc->documento_pdf;
                    $plantillaName = $doc->plantilla ? $doc->plantilla->nombre_plantilla : 'Documento_Notarial';
                    $nombreDocumento = preg_replace('/[^a-zA-Z0-9_-]/', '_', $plantillaName) . '_' . $id . '.pdf';
                }
                break;
        }

        if (empty($docPath)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El documento final aún no ha sido cargado o no se encuentra disponible para descarga.',
                ], 404);
            }
            return response('⚠️ El documento final aún no ha sido cargado o no está disponible.', 404, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        // Resolver la ruta física del archivo de manera exhaustiva y normalizada
        $cleanPath = ltrim(str_replace('\\', '/', $docPath), '/');
        $cleanPathNoPublic = preg_replace('#^public/#i', '', $cleanPath);

        $candidates = [
            public_path($cleanPath),
            public_path($cleanPathNoPublic),
            storage_path('app/public/' . $cleanPath),
            storage_path('app/public/' . $cleanPathNoPublic),
            storage_path($cleanPath),
            base_path($cleanPath),
            base_path('public/' . $cleanPathNoPublic),
        ];

        $fullPath = null;
        foreach ($candidates as $cand) {
            if (!empty($cand) && file_exists($cand) && is_file($cand)) {
                $fullPath = $cand;
                break;
            }
        }

        if (!$fullPath) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo físico del documento no fue encontrado en el servidor.',
                ], 404);
            }
            return response('⚠️ El archivo físico del documento no fue encontrado en el servidor.', 404, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Length' => filesize($fullPath),
            'Cache-Control' => 'no-cache, must-revalidate',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, Accept, X-Requested-With',
            'Access-Control-Expose-Headers' => 'Content-Disposition, Content-Length',
        ];

        if ($request->boolean('preview') || $request->boolean('stream') || $request->input('mode') === 'inline') {
            return response()->file($fullPath, array_merge($headers, [
                'Content-Disposition' => 'inline; filename="' . $nombreDocumento . '"',
            ]));
        }

        return response()->download($fullPath, $nombreDocumento, $headers);
    }

    /**
     * Get all available notarial services/procedures dynamically.
     */
    public function servicios(Request $request): JsonResponse
    {
        $servicios = collect();

        // 1. Tipos de Trámites Dinámicos (Creados desde el módulo de TipoTramites)
        $tiposDinamicos = TipoTramite::where('activo', true)
            ->orderBy('orden', 'asc')
            ->orderBy('nombre', 'asc')
            ->get();

        foreach ($tiposDinamicos as $tipo) {
            $campos = is_array($tipo->campos) ? $tipo->campos : json_decode($tipo->campos ?? '[]', true);
            $requisitos = [];
            if (!empty($campos)) {
                foreach ($campos as $c) {
                    if (!empty($c['label']) || !empty($c['nombre'])) {
                        $requisitos[] = $c['label'] ?? $c['nombre'];
                    }
                }
            }

            $servicios->push([
                'id' => 'tipo_' . $tipo->id,
                'origen' => 'tipo_tramite',
                'nombre' => $tipo->nombre,
                'slug' => $tipo->slug,
                'categoria' => 'formularios',
                'categoria_label' => 'Trámite Especial / Formulario',
                'icono' => $tipo->icono ?: 'document-text',
                'icono_emoji' => '📋',
                'color_gradient' => $tipo->color_gradient ?: 'linear-gradient(135deg, #4f46e5, #7c3aed)',
                'descripcion' => $tipo->descripcion ?: 'Trámite notarial especializado disponible en nuestra notaría.',
                'requisitos' => $requisitos,
                'campos' => $campos,
                'activo' => (bool) $tipo->activo,
            ]);
        }

        // 2. Plantillas Notariales Activas
        $plantillas = Plantilla::where('activo', true)
            ->orderBy('nombre_plantilla', 'asc')
            ->get();

        foreach ($plantillas as $p) {
            $catSlug = strtolower(trim($p->categoria ?: 'varios'));
            $catLabel = $p->categoria ? ucfirst($p->categoria) : 'Documento Notarial';
            
            $servicios->push([
                'id' => 'plantilla_' . $p->id_plantilla,
                'origen' => 'plantilla',
                'nombre' => $p->nombre_plantilla,
                'slug' => Str::slug($p->nombre_plantilla),
                'categoria' => $catSlug,
                'categoria_label' => $catLabel,
                'icono' => 'document-duplicate',
                'icono_emoji' => '📄',
                'color_gradient' => 'linear-gradient(135deg, #0284c7, #0369a1)',
                'descripcion' => $p->descripcion ?: 'Elaboración y redacción de ' . strtolower($p->nombre_plantilla) . ' con validez notarial oficial.',
                'requisitos' => ['Cédula de identidad', 'Certificado de votación', 'Documentación de respaldo'],
                'campos' => [],
                'activo' => (bool) $p->activo,
            ]);
        }

        // 3. Servicios Principales Notariales
        // Poderes
        $servicios->push([
            'id' => 'core_poderes',
            'origen' => 'core',
            'nombre' => 'Poderes Notariales (Especiales y Generales)',
            'slug' => 'poderes-notariales',
            'categoria' => 'poderes',
            'categoria_label' => 'Poderes',
            'icono' => 'document-text',
            'icono_emoji' => '📜',
            'color_gradient' => 'linear-gradient(135deg, #0284c7, #0ea5e9)',
            'descripcion' => 'Otorgamiento de poderes generales, especiales para administración de bienes, trámites bancarios, compraventa de vehículos e inmuebles o representación legal.',
            'requisitos' => [
                'Cédula de ciudadanía y papeleta de votación del poderdante',
                'Datos completos y copia de cédula del apoderado',
                'Para bienes inmuebles o vehículos: copia de escritura o matrícula',
            ],
            'campos' => [],
            'activo' => true,
        ]);

        // Divorcios
        $servicios->push([
            'id' => 'core_divorcios',
            'origen' => 'core',
            'nombre' => 'Divorcio por Mutuo Consentimiento Notarial',
            'slug' => 'divorcios-notariales',
            'categoria' => 'divorcios',
            'categoria_label' => 'Divorcios',
            'icono' => 'scale',
            'icono_emoji' => '⚖️',
            'color_gradient' => 'linear-gradient(135deg, #e11d48, #f43f5e)',
            'descripcion' => 'Trámite ágil de disolución del vínculo matrimonial por mutuo acuerdo ante Notario Público, conforme a la ley notarial vigente.',
            'requisitos' => [
                'Partida de matrimonio íntegra y actualizada',
                'Cédula y certificado de votación de ambos cónyuges',
                'En caso de hijos menores: acta de mediación o sentencia ejecutoriada de pensión alimenticia y tenencia',
                'Convenio o acta de liquidación de sociedad conyugal (si aplica)',
            ],
            'campos' => [],
            'activo' => true,
        ]);

        // Impuestos
        $servicios->push([
            'id' => 'core_impuestos',
            'origen' => 'core',
            'nombre' => 'Liquidación y Pago de Impuestos Notariales',
            'slug' => 'impuestos-notariales',
            'categoria' => 'impuestos',
            'categoria_label' => 'Impuestos',
            'icono' => 'cash',
            'icono_emoji' => '💵',
            'color_gradient' => 'linear-gradient(135deg, #059669, #10b981)',
            'descripcion' => 'Cálculo, liquidación y gestión de alcabalas, plusvalías, impuestos municipales, transferencias de dominio y tasas notariales.',
            'requisitos' => [
                'Carta de pago de predio urbano/rural del año en curso',
                'Certificado de gravámenes actualizado del Registro de la Propiedad',
                'Copia de escritura anterior y minuta',
                'Cédula y papeleta de votación de compradores y vendedores',
            ],
            'campos' => [],
            'activo' => true,
        ]);

        // Trámites Varios / Declaraciones
        $servicios->push([
            'id' => 'core_declaraciones',
            'origen' => 'core',
            'nombre' => 'Declaraciones Juramentadas y Contratos',
            'slug' => 'declaraciones-juramentadas',
            'categoria' => 'varios',
            'categoria_label' => 'Trámites Varios',
            'icono' => 'pencil-square',
            'icono_emoji' => '✍️',
            'color_gradient' => 'linear-gradient(135deg, #d97706, #f59e0b)',
            'descripcion' => 'Declaraciones juramentadas para ingreso al sector público, autorizaciones de salida de país de menores, posesiones efectivas y contratos privados/notariales.',
            'requisitos' => [
                'Cédula de ciudadanía y certificado de votación',
                'Para salida del país: partida de nacimiento del menor y cédulas de ambos padres',
                'Para posesión efectiva: partida de defunción y partidas de nacimiento de herederos',
            ],
            'campos' => [],
            'activo' => true,
        ]);

        return response()->json([
            'success' => true,
            'total' => $servicios->count(),
            'servicios' => $servicios->values(),
        ]);
    }

    private function getEstadoLabel(string $estado): string
    {
        return match ($estado) {
            'listo' => 'Listo para entregar',
            'en_revision' => 'En Revisión Legal',
            'entregado' => 'Entregado al Cliente',
            'cancelado' => 'Cancelado',
            default => 'En Proceso',
        };
    }

    private function getEstadoColor(string $estado): string
    {
        return match ($estado) {
            'listo' => '#10b981', // Emerald
            'en_revision' => '#3b82f6', // Blue
            'entregado' => '#64748b', // Slate
            'cancelado' => '#ef4444', // Red
            default => '#f59e0b', // Amber
        };
    }

    private function getPorcentajeAvance(string $estado): int
    {
        return match ($estado) {
            'listo' => 100,
            'entregado' => 100,
            'en_revision' => 70,
            'cancelado' => 0,
            default => 35,
        };
    }

    /**
     * Guarda o actualiza la reseña y calificación de un trámite específico.
     */
    public function guardarResena(Request $request, string $categoria, $id): JsonResponse
    {
        /** @var Cliente $cliente */
        $cliente = $request->attributes->get('cliente');

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no autenticado.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'calificacion' => 'required|integer|min:1|max:5',
            'atencion_personal' => 'nullable|integer|min:1|max:5',
            'rapidez' => 'nullable|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:2000',
            'recomendaria' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $nombreTramite = $request->input('tramite_nombre', 'Trámite Notarial');

        $resena = ResenaTramite::updateOrCreate(
            [
                'id_cliente' => $cliente->id_cliente,
                'tramite_tipo' => $categoria,
                'tramite_id' => $id,
            ],
            [
                'tramite_nombre' => $nombreTramite,
                'calificacion' => (int) $request->input('calificacion', 5),
                'atencion_personal' => (int) $request->input('atencion_personal', 5),
                'rapidez' => (int) $request->input('rapidez', 5),
                'comentario' => trim((string) $request->input('comentario')),
                'recomendaria' => $request->boolean('recomendaria', true),
                'fecha_resena' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => '¡Muchas gracias! Tu reseña y calificación se han registrado exitosamente.',
            'resena' => [
                'id' => $resena->id,
                'calificacion' => (int) $resena->calificacion,
                'atencion_personal' => (int) $resena->atencion_personal,
                'rapidez' => (int) $resena->rapidez,
                'comentario' => $resena->comentario,
                'recomendaria' => (bool) $resena->recomendaria,
                'fecha_formateada' => $resena->fecha_resena ? $resena->fecha_resena->format('d M, Y') : date('d M, Y'),
            ],
        ]);
    }
}
