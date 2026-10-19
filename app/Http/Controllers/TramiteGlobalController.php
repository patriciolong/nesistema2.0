<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DocumentoGenerado;
use App\Models\Pago;
use App\Models\TipoTramite;
use App\Models\TramiteDivorcio;
use App\Models\TramiteImpuesto;
use App\Models\TramitePersonalizado;
use App\Models\TramitePoder;
use App\Models\TramiteVario;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TramiteGlobalController extends Controller
{
    /**
     * Muestra el panel maestro con el listado unificado de todos los trámites realizados,
     * métricas en vivo, filtros avanzados y opciones de cambio de estado.
     */
    public function index(Request $request)
    {
        // Validación de permisos
        $user = Auth::user();
        if ($user && !$user->hasPermission('tramites.view_all') && $user->role !== 'Administrador' && $user->role !== 'Supervisor') {
            abort(403, 'No tienes permisos para consultar el explorador global de trámites.');
        }

        // Parámetros de filtrado
        $search = trim($request->query('search', ''));
        $filtroTipo = $request->query('tipo_tramite', 'todos');
        $filtroEstado = $request->query('estado', 'todos');
        $filtroUsuario = $request->query('usuario_id', '');
        $filtroOficina = $request->query('oficina', '');
        $filtroPago = $request->query('estado_pago', 'todos'); // 'todos', 'pagado', 'pendiente'
        $fechaDesde = $request->query('fecha_desde', '');
        $fechaHasta = $request->query('fecha_hasta', '');
        $rangoPreset = $request->query('preset', '');
        $orden = $request->query('orden', 'recientes');

        // Presets de fecha rápidos
        if ($rangoPreset === 'hoy') {
            $fechaDesde = now()->toDateString();
            $fechaHasta = now()->toDateString();
        } elseif ($rangoPreset === '7d') {
            $fechaDesde = now()->subDays(7)->toDateString();
            $fechaHasta = now()->toDateString();
        } elseif ($rangoPreset === 'mes') {
            $fechaDesde = now()->startOfMonth()->toDateString();
            $fechaHasta = now()->endOfMonth()->toDateString();
        } elseif ($rangoPreset === 'anio') {
            $fechaDesde = now()->startOfYear()->toDateString();
            $fechaHasta = now()->endOfYear()->toDateString();
        }

        // 1. Cargar Pagos consolidados para asociar detalles de caja y cajero
        $pagos = Pago::with(['cajaSesion.user'])->latest()->get();
        $pagosConsolidados = $pagos->groupBy(function ($pago) {
            $tipoKey = strtolower($pago->tramite_tipo ?? '');
            if (str_contains($tipoKey, 'poder')) return 'poder_' . $pago->tramite_id;
            if (str_contains($tipoKey, 'divorcio')) return 'divorcio_' . $pago->tramite_id;
            if (str_contains($tipoKey, 'impuesto')) return 'impuesto_' . $pago->tramite_id;
            if (str_contains($tipoKey, 'vario')) return 'vario_' . $pago->tramite_id;
            if (str_contains($tipoKey, 'personalizado')) return 'personalizado_' . $pago->tramite_id;
            if (str_contains($tipoKey, 'documento')) return 'documento_' . $pago->tramite_id;
            return 'general_' . $pago->tramite_id;
        });

        $items = collect();

        // 1.1 PODERES NOTARIALES
        if ($filtroTipo === 'todos' || $filtroTipo === 'poderes') {
            $poderes = TramitePoder::with(['cliente', 'usuario'])->get();
            foreach ($poderes as $p) {
                $costo = floatval($p->tp_costo_tramite);
                $abono = floatval($p->tp_abono_tramite);
                $saldo = floatval($p->tp_saldo);
                $pagoInfo = $pagosConsolidados->get('poder_' . $p->id_tram_poderes)?->first();

                $items->push([
                    'id' => $p->id_tram_poderes,
                    'raw_created_at' => $p->created_at ? $p->created_at->timestamp : ($p->tp_fecha ? strtotime($p->tp_fecha) : 0),
                    'tipo_key' => 'poderes',
                    'tipo_nombre' => 'Poder Notarial',
                    'tipo_icono' => '📜',
                    'tipo_badge_color' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                    'subtipo' => $p->tp_razon_otorga_poder ?: 'Poder Notarial General',
                    'cliente_id' => $p->id_cliente,
                    'cliente_nombre' => $p->cliente ? ($p->cliente->c_nombre . ' ' . $p->cliente->c_apellido) : 'Cliente No Asignado',
                    'cliente_identificacion' => $p->cliente->c_identificacion ?? 'S/I',
                    'cliente_telefono' => $p->cliente->c_telefono ?? 'S/T',
                    'cliente_email' => $p->cliente->c_email ?? '',
                    'cliente_direccion' => $p->cliente ? ($p->cliente->c_direccion . ($p->cliente->c_departamento ? ' Apt ' . $p->cliente->c_departamento : '')) : '',
                    'costo' => $costo,
                    'abono' => $abono,
                    'saldo' => $saldo,
                    'estado_financiero' => $saldo <= 0 ? 'pagado' : 'pendiente',
                    'estado' => $p->estado ?: 'en_proceso',
                    'fecha' => $p->tp_fecha ?: ($p->created_at ? $p->created_at->format('Y-m-d') : now()->toDateString()),
                    'hora' => $p->created_at ? $p->created_at->format('H:i A') : '09:00 AM',
                    'fecha_hora_formatted' => $p->tp_fecha ? date('d/m/Y', strtotime($p->tp_fecha)) : ($p->created_at ? $p->created_at->format('d/m/Y H:i') : date('d/m/Y')),
                    'usuario' => $p->usuario->name ?? 'Usuario Sistema',
                    'usuario_id' => $p->id_usuario,
                    'oficina' => $p->tp_oficina ?: 'Oficina General',
                    'observaciones' => $p->tp_observaciones ?: ($p->tp_razon_otorga_poder ?: 'Poder notarial registrado.'),
                    'documento_pdf' => $p->documento_pdf,
                    'documento_pdf_url' => $p->documento_pdf ? asset($p->documento_pdf) : null,
                    'route_pdf' => $p->documento_pdf ? asset($p->documento_pdf) : route('poderes.show', $p->id_tram_poderes),
                    'caja_id' => $pagoInfo?->caja_sesion_id,
                    'caja_estado' => $pagoInfo?->cajaSesion?->estado,
                    'cajero' => $pagoInfo?->cajaSesion?->user?->name ?? ($pagoInfo?->usuario ?? 'N/A'),
                    'metodo_pago' => $pagoInfo?->metodo_pago ?? 'Efectivo / Cartera',
                    'fecha_pago' => $pagoInfo?->created_at ? $pagoInfo->created_at->format('d/m/Y H:i') : null,
                ]);
            }
        }

        // 1.2 REGISTRO DE DIVORCIOS
        if ($filtroTipo === 'todos' || $filtroTipo === 'divorcios') {
            $divorcios = TramiteDivorcio::with(['cliente', 'usuario'])->get();
            foreach ($divorcios as $d) {
                $costo = floatval($d->td_valor);
                $abono = floatval($d->td_abono);
                $saldo = floatval($d->td_saldo);
                $pagoInfo = $pagosConsolidados->get('divorcio_' . $d->id_tram_div)?->first();

                $items->push([
                    'id' => $d->id_tram_div,
                    'raw_created_at' => $d->created_at ? $d->created_at->timestamp : ($d->td_fecha ? strtotime($d->td_fecha) : 0),
                    'tipo_key' => 'divorcios',
                    'tipo_nombre' => 'Registro de Divorcio',
                    'tipo_icono' => '⚖️',
                    'tipo_badge_color' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'subtipo' => $d->td_motivo_divorcio ?: 'Disolución Conyugal',
                    'cliente_id' => $d->id_cliente,
                    'cliente_nombre' => $d->cliente ? ($d->cliente->c_nombre . ' ' . $d->cliente->c_apellido) : 'Cliente No Asignado',
                    'cliente_identificacion' => $d->cliente->c_identificacion ?? 'S/I',
                    'cliente_telefono' => $d->cliente->c_telefono ?? 'S/T',
                    'cliente_email' => $d->cliente->c_email ?? '',
                    'cliente_direccion' => $d->cliente ? ($d->cliente->c_direccion . ($d->cliente->c_departamento ? ' Apt ' . $d->cliente->c_departamento : '')) : '',
                    'costo' => $costo,
                    'abono' => $abono,
                    'saldo' => $saldo,
                    'estado_financiero' => $saldo <= 0 ? 'pagado' : 'pendiente',
                    'estado' => $d->estado ?: 'en_proceso',
                    'fecha' => $d->td_fecha ?: ($d->created_at ? $d->created_at->format('Y-m-d') : now()->toDateString()),
                    'hora' => $d->created_at ? $d->created_at->format('H:i A') : '09:00 AM',
                    'fecha_hora_formatted' => $d->td_fecha ? date('d/m/Y', strtotime($d->td_fecha)) : ($d->created_at ? $d->created_at->format('d/m/Y H:i') : date('d/m/Y')),
                    'usuario' => $d->usuario->name ?? 'Usuario Sistema',
                    'usuario_id' => $d->id_usuario,
                    'oficina' => $d->td_oficina ?: 'Oficina General',
                    'observaciones' => $d->td_observaciones ?: ($d->td_motivo_divorcio ?: 'Proceso de divorcio asentado.'),
                    'documento_pdf' => $d->documento_pdf,
                    'documento_pdf_url' => $d->documento_pdf ? asset($d->documento_pdf) : null,
                    'route_pdf' => $d->documento_pdf ? asset($d->documento_pdf) : route('divorcios.show', $d->id_tram_div),
                    'caja_id' => $pagoInfo?->caja_sesion_id,
                    'caja_estado' => $pagoInfo?->cajaSesion?->estado,
                    'cajero' => $pagoInfo?->cajaSesion?->user?->name ?? ($pagoInfo?->usuario ?? 'N/A'),
                    'metodo_pago' => $pagoInfo?->metodo_pago ?? 'Efectivo / Cartera',
                    'fecha_pago' => $pagoInfo?->created_at ? $pagoInfo->created_at->format('d/m/Y H:i') : null,
                ]);
            }
        }

        // 1.3 DECLARACIÓN DE IMPUESTOS / ITIN
        if ($filtroTipo === 'todos' || $filtroTipo === 'impuestos') {
            $impuestos = TramiteImpuesto::with(['cliente', 'usuario'])->get();
            foreach ($impuestos as $ti) {
                $costo = floatval($ti->ti_costo_tramite);
                $abono = floatval($ti->ti_abono_tramite);
                $saldo = floatval($ti->ti_saldo);
                $pagoInfo = $pagosConsolidados->get('impuesto_' . $ti->id_tram_impuestos)?->first();

                $items->push([
                    'id' => $ti->id_tram_impuestos,
                    'raw_created_at' => $ti->created_at ? $ti->created_at->timestamp : ($ti->ti_fecha ? strtotime($ti->ti_fecha) : 0),
                    'tipo_key' => 'impuestos',
                    'tipo_nombre' => 'Declaración de Impuestos',
                    'tipo_icono' => '📑',
                    'tipo_badge_color' => 'bg-sky-50 text-sky-700 border-sky-200',
                    'subtipo' => 'Taxes & Asesoría Fiscal',
                    'cliente_id' => $ti->id_cliente,
                    'cliente_nombre' => $ti->cliente ? ($ti->cliente->c_nombre . ' ' . $ti->cliente->c_apellido) : 'Cliente No Asignado',
                    'cliente_identificacion' => $ti->cliente->c_identificacion ?? 'S/I',
                    'cliente_telefono' => $ti->cliente->c_telefono ?? 'S/T',
                    'cliente_email' => $ti->cliente->c_email ?? '',
                    'cliente_direccion' => $ti->cliente ? ($ti->cliente->c_direccion . ($ti->cliente->c_departamento ? ' Apt ' . $ti->cliente->c_departamento : '')) : '',
                    'costo' => $costo,
                    'abono' => $abono,
                    'saldo' => $saldo,
                    'estado_financiero' => $saldo <= 0 ? 'pagado' : 'pendiente',
                    'estado' => $ti->estado ?: 'en_proceso',
                    'fecha' => $ti->ti_fecha ?: ($ti->created_at ? $ti->created_at->format('Y-m-d') : now()->toDateString()),
                    'hora' => $ti->created_at ? $ti->created_at->format('H:i A') : '09:00 AM',
                    'fecha_hora_formatted' => $ti->ti_fecha ? date('d/m/Y', strtotime($ti->ti_fecha)) : ($ti->created_at ? $ti->created_at->format('d/m/Y H:i') : date('d/m/Y')),
                    'usuario' => $ti->usuario->name ?? 'Usuario Sistema',
                    'usuario_id' => $ti->id_usuario,
                    'oficina' => $ti->ti_oficina ?: 'Oficina General',
                    'observaciones' => $ti->ti_observacion ?: 'Declaración de impuestos procesada.',
                    'documento_pdf' => $ti->documento_pdf,
                    'documento_pdf_url' => $ti->documento_pdf ? asset($ti->documento_pdf) : null,
                    'route_pdf' => $ti->documento_pdf ? asset($ti->documento_pdf) : route('impuestos.show', $ti->id_tram_impuestos),
                    'caja_id' => $pagoInfo?->caja_sesion_id,
                    'caja_estado' => $pagoInfo?->cajaSesion?->estado,
                    'cajero' => $pagoInfo?->cajaSesion?->user?->name ?? ($pagoInfo?->usuario ?? 'N/A'),
                    'metodo_pago' => $pagoInfo?->metodo_pago ?? 'Efectivo / Cartera',
                    'fecha_pago' => $pagoInfo?->created_at ? $pagoInfo->created_at->format('d/m/Y H:i') : null,
                ]);
            }
        }

        // 1.4 TRÁMITES VARIOS
        if ($filtroTipo === 'todos' || $filtroTipo === 'varios') {
            $varios = TramiteVario::with(['cliente', 'usuario'])->get();
            foreach ($varios as $v) {
                $costo = floatval($v->tv_valor_tramite);
                $abono = floatval($v->tv_abono_tramite);
                $saldo = floatval($v->tv_saldo);
                $pagoInfo = $pagosConsolidados->get('vario_' . $v->id_tramite_varios)?->first();

                $items->push([
                    'id' => $v->id_tramite_varios,
                    'raw_created_at' => $v->created_at ? $v->created_at->timestamp : ($v->tv_fecha ? strtotime($v->tv_fecha) : 0),
                    'tipo_key' => 'varios',
                    'tipo_nombre' => 'Trámite Varios',
                    'tipo_icono' => '📂',
                    'tipo_badge_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'subtipo' => $v->tv_motivo ?: 'Gestión Notarial Varia',
                    'cliente_id' => $v->id_cliente,
                    'cliente_nombre' => $v->cliente ? ($v->cliente->c_nombre . ' ' . $v->cliente->c_apellido) : 'Cliente No Asignado',
                    'cliente_identificacion' => $v->cliente->c_identificacion ?? 'S/I',
                    'cliente_telefono' => $v->cliente->c_telefono ?? 'S/T',
                    'cliente_email' => $v->cliente->c_email ?? '',
                    'cliente_direccion' => $v->cliente ? ($v->cliente->c_direccion . ($v->cliente->c_napartamento ? ' Apt ' . $v->cliente->c_napartamento : '')) : '',
                    'costo' => $costo,
                    'abono' => $abono,
                    'saldo' => $saldo,
                    'estado_financiero' => $saldo <= 0 ? 'pagado' : 'pendiente',
                    'estado' => $v->estado ?: 'en_proceso',
                    'fecha' => $v->tv_fecha ?: ($v->created_at ? $v->created_at->format('Y-m-d') : now()->toDateString()),
                    'hora' => $v->created_at ? $v->created_at->format('H:i A') : '09:00 AM',
                    'fecha_hora_formatted' => $v->tv_fecha ? date('d/m/Y', strtotime($v->tv_fecha)) : ($v->created_at ? $v->created_at->format('d/m/Y H:i') : date('d/m/Y')),
                    'usuario' => $v->usuario->name ?? 'Usuario Sistema',
                    'usuario_id' => $v->id_usuario,
                    'oficina' => $v->tv_oficina ?: 'Oficina General',
                    'observaciones' => $v->tv_observaciones ?: ($v->tv_motivo ?: 'Trámite vario procesado.'),
                    'documento_pdf' => $v->documento_pdf,
                    'documento_pdf_url' => $v->documento_pdf ? asset($v->documento_pdf) : null,
                    'route_pdf' => $v->documento_pdf ? asset($v->documento_pdf) : route('tramites-varios.show', $v->id_tramite_varios),
                    'caja_id' => $pagoInfo?->caja_sesion_id,
                    'caja_estado' => $pagoInfo?->cajaSesion?->estado,
                    'cajero' => $pagoInfo?->cajaSesion?->user?->name ?? ($pagoInfo?->usuario ?? 'N/A'),
                    'metodo_pago' => $pagoInfo?->metodo_pago ?? 'Efectivo / Cartera',
                    'fecha_pago' => $pagoInfo?->created_at ? $pagoInfo->created_at->format('d/m/Y H:i') : null,
                ]);
            }
        }

        // 1.5 TRÁMITES PERSONALIZADOS
        if ($filtroTipo === 'todos' || $filtroTipo === 'personalizados') {
            $personalizados = TramitePersonalizado::with(['cliente', 'tipoTramite'])->get();
            foreach ($personalizados as $tp) {
                $costo = floatval($tp->valor_tramite);
                $abono = floatval($tp->abono_tramite);
                $saldo = floatval($tp->saldo);
                $pagoInfo = $pagosConsolidados->get('personalizado_' . $tp->id)?->first();

                $items->push([
                    'id' => $tp->id,
                    'raw_created_at' => $tp->created_at ? $tp->created_at->timestamp : ($tp->fecha ? $tp->fecha->timestamp : 0),
                    'tipo_key' => 'personalizados',
                    'tipo_nombre' => $tp->tipoTramite->nombre ?? 'Trámite Especial',
                    'tipo_icono' => '✨',
                    'tipo_badge_color' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'subtipo' => $tp->tipoTramite->nombre ?? 'Formulario Especial',
                    'cliente_id' => $tp->id_cliente,
                    'cliente_nombre' => $tp->cliente ? ($tp->cliente->c_nombre . ' ' . $tp->cliente->c_apellido) : 'Cliente No Asignado',
                    'cliente_identificacion' => $tp->cliente->c_identificacion ?? 'S/I',
                    'cliente_telefono' => $tp->cliente->c_telefono ?? 'S/T',
                    'cliente_email' => $tp->cliente->c_email ?? '',
                    'cliente_direccion' => $tp->cliente ? ($tp->cliente->c_direccion . ($tp->cliente->c_departamento ? ' Apt ' . $tp->cliente->c_departamento : '')) : '',
                    'costo' => $costo,
                    'abono' => $abono,
                    'saldo' => $saldo,
                    'estado_financiero' => $saldo <= 0 ? 'pagado' : 'pendiente',
                    'estado' => $tp->estado ?: 'en_proceso',
                    'fecha' => $tp->fecha ? $tp->fecha->format('Y-m-d') : ($tp->created_at ? $tp->created_at->format('Y-m-d') : now()->toDateString()),
                    'hora' => $tp->created_at ? $tp->created_at->format('H:i A') : '09:00 AM',
                    'fecha_hora_formatted' => $tp->fecha ? $tp->fecha->format('d/m/Y') : ($tp->created_at ? $tp->created_at->format('d/m/Y H:i') : date('d/m/Y')),
                    'usuario' => $tp->usuario ?: 'Personal Autorizado',
                    'usuario_id' => null,
                    'oficina' => $tp->oficina ?: 'Oficina General',
                    'observaciones' => $tp->observaciones ?: 'Trámite personalizado emitido.',
                    'documento_pdf' => $tp->documento_pdf,
                    'documento_pdf_url' => $tp->documento_pdf ? asset($tp->documento_pdf) : null,
                    'route_pdf' => $tp->documento_pdf ? asset($tp->documento_pdf) : route('tramites-personalizados.show', $tp->id),
                    'caja_id' => $pagoInfo?->caja_sesion_id,
                    'caja_estado' => $pagoInfo?->cajaSesion?->estado,
                    'cajero' => $pagoInfo?->cajaSesion?->user?->name ?? ($pagoInfo?->usuario ?? 'N/A'),
                    'metodo_pago' => $pagoInfo?->metodo_pago ?? 'Efectivo / Cartera',
                    'fecha_pago' => $pagoInfo?->created_at ? $pagoInfo->created_at->format('d/m/Y H:i') : null,
                ]);
            }
        }

        // Métricas Globales del Historial
        $totalGeneral = $items->count();
        $totalMontoFacturado = $items->sum('costo');
        $totalMontoRecaudado = $items->sum('abono');
        $totalMontoSaldo = $items->sum('saldo');

        $totalListos = $items->whereIn('estado', ['listo', 'entregado'])->count();
        $totalEnProceso = $items->where('estado', 'en_proceso')->count();
        $totalEnRevision = $items->where('estado', 'en_revision')->count();
        $totalEntregados = $items->where('estado', 'entregado')->count();

        $hoyString = now()->toDateString();
        $totalHoy = $items->where('fecha', $hoyString)->count();
        $mesString = now()->format('Y-m');
        $totalMes = $items->filter(function($i) use ($mesString) {
            return str_starts_with($i['fecha'], $mesString);
        })->count();

        // 2. APLICAR FILTROS
        $filtrados = $items;

        // Filtro por Estado
        if ($filtroEstado !== 'todos') {
            $filtrados = $filtrados->where('estado', $filtroEstado);
        }

        // Filtro por Estado de Pago
        if ($filtroPago === 'pagado') {
            $filtrados = $filtrados->where('estado_financiero', 'pagado');
        } elseif ($filtroPago === 'pendiente') {
            $filtrados = $filtrados->where('estado_financiero', 'pendiente');
        }

        // Filtro por Usuario
        if (!empty($filtroUsuario)) {
            $filtrados = $filtrados->filter(function($item) use ($filtroUsuario) {
                return $item['usuario_id'] == $filtroUsuario || stripos($item['usuario'], $filtroUsuario) !== false;
            });
        }

        // Filtro por Oficina
        if (!empty($filtroOficina)) {
            $filtrados = $filtrados->filter(function($item) use ($filtroOficina) {
                return $item['oficina'] === $filtroOficina;
            });
        }

        // Filtro por Fecha Desde
        if (!empty($fechaDesde)) {
            $filtrados = $filtrados->filter(function($item) use ($fechaDesde) {
                return $item['fecha'] >= $fechaDesde;
            });
        }

        // Filtro por Fecha Hasta
        if (!empty($fechaHasta)) {
            $filtrados = $filtrados->filter(function($item) use ($fechaHasta) {
                return $item['fecha'] <= $fechaHasta;
            });
        }

        // Búsqueda por texto (Cliente, Cédula, Teléfono, ID, Subtipo)
        if (!empty($search)) {
            $filtrados = $filtrados->filter(function($item) use ($search) {
                return stripos($item['cliente_nombre'], $search) !== false ||
                       stripos($item['cliente_identificacion'], $search) !== false ||
                       stripos($item['cliente_telefono'], $search) !== false ||
                       stripos((string)$item['id'], $search) !== false ||
                       stripos($item['subtipo'], $search) !== false ||
                       stripos($item['tipo_nombre'], $search) !== false ||
                       stripos($item['usuario'], $search) !== false;
            });
        }

        // 3. ORDENAMIENTO
        switch ($orden) {
            case 'antiguos':
                $filtrados = $filtrados->sortBy('raw_created_at');
                break;
            case 'mayor_monto':
                $filtrados = $filtrados->sortByDesc('costo');
                break;
            case 'menor_monto':
                $filtrados = $filtrados->sortBy('costo');
                break;
            case 'cliente_asc':
                $filtrados = $filtrados->sortBy('cliente_nombre');
                break;
            case 'recientes':
            default:
                $filtrados = $filtrados->sortByDesc('raw_created_at');
                break;
        }

        // 4. PAGINACIÓN
        $perPage = 20;
        $page = LengthAwarePaginator::resolveCurrentPage() ?: 1;
        $paginatedItems = new LengthAwarePaginator(
            $filtrados->forPage($page, $perPage)->values(),
            $filtrados->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        // Listas auxiliares para selects
        $usuarios = User::select('id', 'name')->orderBy('name')->get();
        $oficinas = Cliente::whereNotNull('c_oficina_registro')
            ->where('c_oficina_registro', '!=', '')
            ->distinct()
            ->pluck('c_oficina_registro');

        return view('tramites.global_index', [
            'tramites' => $paginatedItems,
            'totalGeneral' => $totalGeneral,
            'totalMontoFacturado' => $totalMontoFacturado,
            'totalMontoRecaudado' => $totalMontoRecaudado,
            'totalMontoSaldo' => $totalMontoSaldo,
            'totalListos' => $totalListos,
            'totalEnProceso' => $totalEnProceso,
            'totalEnRevision' => $totalEnRevision,
            'totalEntregados' => $totalEntregados,
            'totalHoy' => $totalHoy,
            'totalMes' => $totalMes,
            'usuarios' => $usuarios,
            'oficinas' => $oficinas,
            'filtros' => [
                'search' => $search,
                'tipo_tramite' => $filtroTipo,
                'estado' => $filtroEstado,
                'usuario_id' => $filtroUsuario,
                'oficina' => $filtroOficina,
                'estado_pago' => $filtroPago,
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'preset' => $rangoPreset,
                'orden' => $orden,
            ]
        ]);
    }

    /**
     * Exporta el listado filtrado de trámites a formato CSV / Excel.
     */
    public function export(Request $request)
    {
        $response = $this->index($request);
        // Implementación de descarga en stream CSV rápido
        $tramites = $response->getData()['tramites'] ?? collect();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=tramites_realizados_' . date('Y-m-d_His') . '.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($tramites) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($handle, ['Folio', 'Tipo Trámite', 'Detalle/Motivo', 'Fecha', 'Hora', 'Cliente', 'Cédula/ID', 'Teléfono', 'Asesor', 'Oficina', 'Estado', 'Costo ($)', 'Abono ($)', 'Saldo ($)', 'Estado Pago']);

            foreach ($tramites as $t) {
                fputcsv($handle, [
                    '#' . str_pad($t['id'], 5, '0', STR_PAD_LEFT),
                    $t['tipo_nombre'],
                    $t['subtipo'],
                    $t['fecha'],
                    $t['hora'],
                    $t['cliente_nombre'],
                    $t['cliente_identificacion'],
                    $t['cliente_telefono'],
                    $t['usuario'],
                    $t['oficina'],
                    ucfirst(str_replace('_', ' ', $t['estado'])),
                    number_format($t['costo'], 2, '.', ''),
                    number_format($t['abono'], 2, '.', ''),
                    number_format($t['saldo'], 2, '.', ''),
                    ucfirst($t['estado_financiero']),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
