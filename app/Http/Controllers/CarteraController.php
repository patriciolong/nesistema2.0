<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\TramitePersonalizado;
use App\Models\TramitePoder;
use App\Models\TramiteDivorcio;
use App\Models\TramiteImpuesto;
use App\Models\TramiteVario;
use App\Models\Pago;
use App\Models\Banco;
use App\Models\Tarjeta;
use App\Services\CajaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CarteraController extends Controller
{
    protected CajaService $cajaService;

    public function __construct(CajaService $cajaService)
    {
        $this->cajaService = $cajaService;
    }

    /**
     * Display the main portfolio (Cartera de Clientes / Cuentas por Cobrar & Créditos).
     */
    public function index(Request $request)
    {
        if (!Auth::user()->hasPermission('cartera.view') && !Auth::user()->hasPermission('clientes.view')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso para consultar la cartera de clientes.');
        }

        $query = Cliente::query();

        // 1. Buscador por nombre, apellido, cédula, teléfono o email
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function($q) use ($search) {
                $q->where('c_nombre', 'like', "%{$search}%")
                  ->orWhere('c_apellido', 'like', "%{$search}%")
                  ->orWhere('c_identificacion', 'like', "%{$search}%")
                  ->orWhere('c_telefono', 'like', "%{$search}%")
                  ->orWhere('c_email', 'like', "%{$search}%");
            });
        }

        // 2. Filtro por Oficina de Registro
        if ($request->filled('oficina')) {
            $query->where('c_oficina_registro', $request->input('oficina'));
        }

        // 3. Filtro por Estado de Deuda
        $filtroEstado = $request->input('filtro_estado', 'deudores');
        if ($filtroEstado === 'deudores') {
            $query->where('c_saldo', '>', 0);
        } elseif ($filtroEstado === 'al_dia') {
            $query->where('c_saldo', '<=', 0);
        }

        // 4. Ordenamiento
        $orden = $request->input('orden', 'mayor_saldo');
        if ($orden === 'mayor_saldo') {
            $query->orderBy('c_saldo', 'desc');
        } elseif ($orden === 'menor_saldo') {
            $query->orderBy('c_saldo', 'asc');
        } elseif ($orden === 'nombre') {
            $query->orderBy('c_nombre', 'asc')->orderBy('c_apellido', 'asc');
        } else {
            $query->orderBy('id_cliente', 'desc');
        }

        $clientes = $query->paginate(15)->withQueryString();

        // Métricas Globales de Cartera
        $totalCarteraPorCobrar = Cliente::where('c_saldo', '>', 0)->sum('c_saldo');
        $totalDeudaHistorica = Cliente::sum('c_deuda');
        $totalAbonado = Cliente::sum('c_abonado');
        $clientesDeudoresCount = Cliente::where('c_saldo', '>', 0)->count();
        $oficinas = Cliente::whereNotNull('c_oficina_registro')
            ->where('c_oficina_registro', '!=', '')
            ->distinct()
            ->pluck('c_oficina_registro');

        $user = Auth::user();
        $tieneCajaAbierta = $this->cajaService->hasCajaAbierta($user);
        $cajaAbierta = $this->cajaService->getCajaAbierta($user);
        $bancos = Banco::activos()->orderBy('nombre')->get();
        $tarjetas = Tarjeta::with('banco')->activas()->orderBy('nombre')->get();

        return view('cartera.index', compact(
            'clientes',
            'totalCarteraPorCobrar',
            'totalDeudaHistorica',
            'totalAbonado',
            'clientesDeudoresCount',
            'oficinas',
            'tieneCajaAbierta',
            'cajaAbierta',
            'bancos',
            'tarjetas'
        ));
    }

    /**
     * Returns JSON with all pending unpaid trámites for a specific client.
     */
    public function getDetalleDeuda(Cliente $cliente)
    {
        $poderes = TramitePoder::where('id_cliente', $cliente->id_cliente)
            ->where('tp_saldo', '>', 0)
            ->get()
            ->map(function($p) {
                $desc = $p->tp_razon_otorga_poder ?: ($p->tp_nombres_otorga_poder ? 'Otorga: ' . $p->tp_nombres_otorga_poder : 'Poder General / Especial');
                return [
                    'tipo' => 'poderes',
                    'tipo_label' => 'Poder Notarial',
                    'color_badge' => 'bg-amber-100 text-amber-800 border-amber-300',
                    'icon' => '📜',
                    'id' => $p->id_tram_poderes,
                    'fecha' => $p->tp_fecha ? date('d/m/Y', strtotime($p->tp_fecha)) : '-',
                    'descripcion' => $desc,
                    'costo' => floatval($p->tp_costo_tramite),
                    'abono' => floatval($p->tp_abono_tramite),
                    'saldo' => floatval($p->tp_saldo),
                    'estado' => $p->estado ?? 'en_proceso',
                ];
            });

        $divorcios = TramiteDivorcio::where('id_cliente', $cliente->id_cliente)
            ->where('td_saldo', '>', 0)
            ->get()
            ->map(function($d) {
                $tipoDiv = $d->td_controvertido ? 'Controvertido' : ($d->td_consensual ? 'Por Consenso' : ($d->td_notarial ? 'Notarial' : ''));
                $desc = 'Divorcio' . ($tipoDiv ? ' ' . $tipoDiv : '') . ($d->td_nombre_c ? ' - Cónyuge: ' . $d->td_nombre_c : ($d->td_motivo_divorcio ? ' - ' . $d->td_motivo_divorcio : ''));
                return [
                    'tipo' => 'divorcios',
                    'tipo_label' => 'Divorcio',
                    'color_badge' => 'bg-rose-100 text-rose-800 border-rose-300',
                    'icon' => '💔',
                    'id' => $d->id_tram_div,
                    'fecha' => $d->td_fecha ? date('d/m/Y', strtotime($d->td_fecha)) : '-',
                    'descripcion' => $desc,
                    'costo' => floatval($d->td_valor),
                    'abono' => floatval($d->td_abono),
                    'saldo' => floatval($d->td_saldo),
                    'estado' => $d->estado ?? 'en_proceso',
                ];
            });

        $impuestos = TramiteImpuesto::where('id_cliente', $cliente->id_cliente)
            ->where('ti_saldo', '>', 0)
            ->get()
            ->map(function($i) {
                return [
                    'tipo' => 'impuestos',
                    'tipo_label' => 'Impuestos / ITIN',
                    'color_badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                    'icon' => '📑',
                    'id' => $i->id_tram_impuestos,
                    'fecha' => $i->ti_fecha ? date('d/m/Y', strtotime($i->ti_fecha)) : '-',
                    'descripcion' => 'Declaración / Reporte ' . ($i->ti_anio_reporte ?: ''),
                    'costo' => floatval($i->ti_costo_tramite),
                    'abono' => floatval($i->ti_abono_tramite),
                    'saldo' => floatval($i->ti_saldo),
                    'estado' => $i->estado ?? 'en_proceso',
                ];
            });

        $varios = TramiteVario::where('id_cliente', $cliente->id_cliente)
            ->where('tv_saldo', '>', 0)
            ->get()
            ->map(function($v) {
                return [
                    'tipo' => 'varios',
                    'tipo_label' => 'Trámite Vario',
                    'color_badge' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                    'icon' => '📁',
                    'id' => $v->id_tramite_varios,
                    'fecha' => $v->tv_fecha ? date('d/m/Y', strtotime($v->tv_fecha)) : '-',
                    'descripcion' => $v->tv_motivo ?: 'Gestión Notarial Varia',
                    'costo' => floatval($v->tv_valor_tramite),
                    'abono' => floatval($v->tv_abono_tramite),
                    'saldo' => floatval($v->tv_saldo),
                    'estado' => $v->estado ?? 'en_proceso',
                ];
            });

        $personalizados = TramitePersonalizado::with('tipoTramite')
            ->where('id_cliente', $cliente->id_cliente)
            ->where('saldo', '>', 0)
            ->get()
            ->map(function($tp) {
                return [
                    'tipo' => 'personalizados',
                    'tipo_label' => $tp->tipoTramite->nombre ?? 'Personalizado',
                    'color_badge' => 'bg-purple-100 text-purple-800 border-purple-300',
                    'icon' => '✨',
                    'id' => $tp->id,
                    'fecha' => $tp->fecha ? date('d/m/Y', strtotime($tp->fecha)) : ($tp->created_at ? $tp->created_at->format('d/m/Y') : '-'),
                    'descripcion' => 'Trámite: ' . ($tp->tipoTramite->nombre ?? 'Personalizado'),
                    'costo' => floatval($tp->valor_tramite),
                    'abono' => floatval($tp->abono_tramite),
                    'saldo' => floatval($tp->saldo),
                    'estado' => $tp->estado ?? 'en_proceso',
                ];
            });

        $tramites = collect()
            ->concat($poderes)
            ->concat($divorcios)
            ->concat($impuestos)
            ->concat($varios)
            ->concat($personalizados)
            ->values();

        return response()->json([
            'success' => true,
            'cliente' => [
                'id_cliente' => $cliente->id_cliente,
                'nombre' => $cliente->c_nombre,
                'apellido' => $cliente->c_apellido,
                'nombre_completo' => $cliente->c_nombre . ' ' . $cliente->c_apellido,
                'identificacion' => $cliente->c_identificacion,
                'telefono' => $cliente->c_telefono,
                'email' => $cliente->c_email,
                'deuda' => floatval($cliente->c_deuda),
                'abonado' => floatval($cliente->c_abonado),
                'saldo' => floatval($cliente->c_saldo),
            ],
            'tramites' => $tramites,
            'total_saldo_tramites' => floatval($tramites->sum('saldo')),
        ]);
    }

    /**
     * Process single or multiple trámite credit payment in active cash session.
     */
    public function cobrar(Request $request)
    {
        if (!Auth::user()->hasPermission('cartera.cobrar') && !Auth::user()->hasPermission('clientes.abonar')) {
            return redirect()->back()->with('error', 'Acceso denegado: No cuentas con el permiso para realizar cobros de cartera.');
        }

        $request->validate([
            'cliente_id' => 'required|integer|exists:cliente,id_cliente',
            'modo_cobro' => 'required|string|in:individual,multiple,total',
            'monto_pago' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|string|in:Efectivo,Cheque,Transferencia,Tarjeta,Zelle',
            'banco_id' => 'nullable|exists:bancos,id',
            'tarjeta_id' => 'nullable|exists:tarjetas,id',
            'numero_referencia' => 'nullable|string|max:100',
            // Si es individual:
            'tramite_tipo' => 'required_if:modo_cobro,individual|nullable|string|in:poderes,divorcios,impuestos,varios,personalizados',
            'tramite_id' => 'required_if:modo_cobro,individual|nullable|integer',
            // Si es múltiple o array de seleccionados:
            'items' => 'nullable|array',
            'items.*.tipo' => 'required_with:items|string|in:poderes,divorcios,impuestos,varios,personalizados',
            'items.*.id' => 'required_with:items|integer',
            'items.*.monto' => 'required_with:items|numeric|min:0.01',
        ]);

        $user = Auth::user();
        $caja = $this->cajaService->getCajaAbierta($user);

        if (!$caja) {
            return redirect()->back()->with('error', '⚠️ No tienes una caja abierta actualmente. Debes abrir tu caja para poder procesar cobros de cartera.');
        }

        $cliente = Cliente::findOrFail($request->input('cliente_id'));
        $modoCobro = $request->input('modo_cobro');
        $montoTotalPago = floatval($request->input('monto_pago'));
        $metodoPago = $request->input('metodo_pago');
        $bancoId = $request->input('banco_id');
        $tarjetaId = $request->input('tarjeta_id');
        $numRef = $request->input('numero_referencia');

        DB::beginTransaction();
        try {
            $conceptosPagos = [];

            if ($modoCobro === 'individual') {
                $tramiteTipo = $request->input('tramite_tipo');
                $tramiteId = $request->input('tramite_id');
                $monto = $montoTotalPago;

                $res = $this->aplicarPagoATramite($tramiteTipo, $tramiteId, $monto);
                $conceptosPagos[] = $res['concepto'];

                $pago = Pago::create([
                    'cliente_id' => $cliente->id_cliente,
                    'monto' => $monto,
                    'concepto' => $res['concepto'],
                    'usuario' => $user->name,
                    'oficina' => $user->office ?? 'General',
                    'caja_sesion_id' => $caja->id,
                    'metodo_pago' => $metodoPago,
                    'banco_id' => $bancoId,
                    'tarjeta_id' => $tarjetaId,
                    'numero_referencia' => $numRef,
                    'tramite_tipo' => $res['tipo_name'],
                    'tramite_id' => $tramiteId,
                ]);

                $this->cajaService->registrarMovimiento([
                    'caja_sesion_id' => $caja->id,
                    'user_id' => $user->id,
                    'cliente_id' => $cliente->id_cliente,
                    'tipo' => 'ingreso_tramite',
                    'monto' => $monto,
                    'metodo_pago' => $metodoPago,
                    'banco_id' => $bancoId,
                    'tarjeta_id' => $tarjetaId,
                    'pago_id' => $pago->id,
                    'concepto' => $res['concepto'] . ' - Cliente: ' . $cliente->c_nombre . ' ' . $cliente->c_apellido,
                    'numero_referencia' => $numRef,
                    'tramite_tipo' => $res['tipo_name'],
                    'tramite_id' => $tramiteId,
                ]);

            } elseif ($modoCobro === 'multiple' && $request->has('items') && is_array($request->input('items'))) {
                $items = $request->input('items');
                $montoTotalProcesado = 0;

                foreach ($items as $item) {
                    $itemTipo = $item['tipo'];
                    $itemId = intval($item['id']);
                    $itemMonto = floatval($item['monto']);

                    if ($itemMonto <= 0) continue;

                    $res = $this->aplicarPagoATramite($itemTipo, $itemId, $itemMonto);
                    $conceptosPagos[] = $res['concepto'] . " ($" . number_format($itemMonto, 2) . ")";
                    $montoTotalProcesado += $itemMonto;

                    $pago = Pago::create([
                        'cliente_id' => $cliente->id_cliente,
                        'monto' => $itemMonto,
                        'concepto' => $res['concepto'],
                        'usuario' => $user->name,
                        'oficina' => $user->office ?? 'General',
                        'caja_sesion_id' => $caja->id,
                        'metodo_pago' => $metodoPago,
                        'banco_id' => $bancoId,
                        'tarjeta_id' => $tarjetaId,
                        'numero_referencia' => $numRef,
                        'tramite_tipo' => $res['tipo_name'],
                        'tramite_id' => $itemId,
                    ]);

                    $this->cajaService->registrarMovimiento([
                        'caja_sesion_id' => $caja->id,
                        'user_id' => $user->id,
                        'cliente_id' => $cliente->id_cliente,
                        'tipo' => 'ingreso_tramite',
                        'monto' => $itemMonto,
                        'metodo_pago' => $metodoPago,
                        'banco_id' => $bancoId,
                        'tarjeta_id' => $tarjetaId,
                        'pago_id' => $pago->id,
                        'concepto' => $res['concepto'] . ' - Cliente: ' . $cliente->c_nombre . ' ' . $cliente->c_apellido,
                        'numero_referencia' => $numRef,
                        'tramite_tipo' => $res['tipo_name'],
                        'tramite_id' => $itemId,
                    ]);
                }
            } else {
                // Cancelación / Abono Global Distribuido (modo 'total')
                $montoRestante = $montoTotalPago;

                // 1. Obtener todos los trámites con deuda en orden
                $unpaidPoderes = TramitePoder::where('id_cliente', $cliente->id_cliente)->where('tp_saldo', '>', 0)->get();
                foreach ($unpaidPoderes as $p) {
                    if ($montoRestante <= 0) break;
                    $abono = min($montoRestante, floatval($p->tp_saldo));
                    $res = $this->aplicarPagoATramite('poderes', $p->id_tram_poderes, $abono);
                    $conceptosPagos[] = $res['concepto'];
                    $montoRestante -= $abono;

                    $pago = Pago::create([
                        'cliente_id' => $cliente->id_cliente,
                        'monto' => $abono,
                        'concepto' => $res['concepto'],
                        'usuario' => $user->name,
                        'oficina' => $user->office ?? 'General',
                        'caja_sesion_id' => $caja->id,
                        'metodo_pago' => $metodoPago,
                        'banco_id' => $bancoId,
                        'tarjeta_id' => $tarjetaId,
                        'numero_referencia' => $numRef,
                        'tramite_tipo' => 'Poder',
                        'tramite_id' => $p->id_tram_poderes,
                    ]);

                    $this->cajaService->registrarMovimiento([
                        'caja_sesion_id' => $caja->id,
                        'user_id' => $user->id,
                        'cliente_id' => $cliente->id_cliente,
                        'tipo' => 'ingreso_tramite',
                        'monto' => $abono,
                        'metodo_pago' => $metodoPago,
                        'banco_id' => $bancoId,
                        'tarjeta_id' => $tarjetaId,
                        'pago_id' => $pago->id,
                        'concepto' => $res['concepto'] . ' - Cliente: ' . $cliente->c_nombre . ' ' . $cliente->c_apellido,
                        'numero_referencia' => $numRef,
                        'tramite_tipo' => 'Poder',
                        'tramite_id' => $p->id_tram_poderes,
                    ]);
                }

                $unpaidDivorcios = TramiteDivorcio::where('id_cliente', $cliente->id_cliente)->where('td_saldo', '>', 0)->get();
                foreach ($unpaidDivorcios as $d) {
                    if ($montoRestante <= 0) break;
                    $abono = min($montoRestante, floatval($d->td_saldo));
                    $res = $this->aplicarPagoATramite('divorcios', $d->id_tram_div, $abono);
                    $conceptosPagos[] = $res['concepto'];
                    $montoRestante -= $abono;

                    $pago = Pago::create([
                        'cliente_id' => $cliente->id_cliente,
                        'monto' => $abono,
                        'concepto' => $res['concepto'],
                        'usuario' => $user->name,
                        'oficina' => $user->office ?? 'General',
                        'caja_sesion_id' => $caja->id,
                        'metodo_pago' => $metodoPago,
                        'banco_id' => $bancoId,
                        'tarjeta_id' => $tarjetaId,
                        'numero_referencia' => $numRef,
                        'tramite_tipo' => 'Divorcio',
                        'tramite_id' => $d->id_tram_div,
                    ]);

                    $this->cajaService->registrarMovimiento([
                        'caja_sesion_id' => $caja->id,
                        'user_id' => $user->id,
                        'cliente_id' => $cliente->id_cliente,
                        'tipo' => 'ingreso_tramite',
                        'monto' => $abono,
                        'metodo_pago' => $metodoPago,
                        'banco_id' => $bancoId,
                        'tarjeta_id' => $tarjetaId,
                        'pago_id' => $pago->id,
                        'concepto' => $res['concepto'] . ' - Cliente: ' . $cliente->c_nombre . ' ' . $cliente->c_apellido,
                        'numero_referencia' => $numRef,
                        'tramite_tipo' => 'Divorcio',
                        'tramite_id' => $d->id_tram_div,
                    ]);
                }

                $unpaidImpuestos = TramiteImpuesto::where('id_cliente', $cliente->id_cliente)->where('ti_saldo', '>', 0)->get();
                foreach ($unpaidImpuestos as $i) {
                    if ($montoRestante <= 0) break;
                    $abono = min($montoRestante, floatval($i->ti_saldo));
                    $res = $this->aplicarPagoATramite('impuestos', $i->id_tram_impuestos, $abono);
                    $conceptosPagos[] = $res['concepto'];
                    $montoRestante -= $abono;

                    $pago = Pago::create([
                        'cliente_id' => $cliente->id_cliente,
                        'monto' => $abono,
                        'concepto' => $res['concepto'],
                        'usuario' => $user->name,
                        'oficina' => $user->office ?? 'General',
                        'caja_sesion_id' => $caja->id,
                        'metodo_pago' => $metodoPago,
                        'banco_id' => $bancoId,
                        'tarjeta_id' => $tarjetaId,
                        'numero_referencia' => $numRef,
                        'tramite_tipo' => 'Impuesto',
                        'tramite_id' => $i->id_tram_impuestos,
                    ]);

                    $this->cajaService->registrarMovimiento([
                        'caja_sesion_id' => $caja->id,
                        'user_id' => $user->id,
                        'cliente_id' => $cliente->id_cliente,
                        'tipo' => 'ingreso_tramite',
                        'monto' => $abono,
                        'metodo_pago' => $metodoPago,
                        'banco_id' => $bancoId,
                        'tarjeta_id' => $tarjetaId,
                        'pago_id' => $pago->id,
                        'concepto' => $res['concepto'] . ' - Cliente: ' . $cliente->c_nombre . ' ' . $cliente->c_apellido,
                        'numero_referencia' => $numRef,
                        'tramite_tipo' => 'Impuesto',
                        'tramite_id' => $i->id_tram_impuestos,
                    ]);
                }

                $unpaidVarios = TramiteVario::where('id_cliente', $cliente->id_cliente)->where('tv_saldo', '>', 0)->get();
                foreach ($unpaidVarios as $v) {
                    if ($montoRestante <= 0) break;
                    $abono = min($montoRestante, floatval($v->tv_saldo));
                    $res = $this->aplicarPagoATramite('varios', $v->id_tramite_varios, $abono);
                    $conceptosPagos[] = $res['concepto'];
                    $montoRestante -= $abono;

                    $pago = Pago::create([
                        'cliente_id' => $cliente->id_cliente,
                        'monto' => $abono,
                        'concepto' => $res['concepto'],
                        'usuario' => $user->name,
                        'oficina' => $user->office ?? 'General',
                        'caja_sesion_id' => $caja->id,
                        'metodo_pago' => $metodoPago,
                        'banco_id' => $bancoId,
                        'tarjeta_id' => $tarjetaId,
                        'numero_referencia' => $numRef,
                        'tramite_tipo' => 'Vario',
                        'tramite_id' => $v->id_tramite_varios,
                    ]);

                    $this->cajaService->registrarMovimiento([
                        'caja_sesion_id' => $caja->id,
                        'user_id' => $user->id,
                        'cliente_id' => $cliente->id_cliente,
                        'tipo' => 'ingreso_tramite',
                        'monto' => $abono,
                        'metodo_pago' => $metodoPago,
                        'banco_id' => $bancoId,
                        'tarjeta_id' => $tarjetaId,
                        'pago_id' => $pago->id,
                        'concepto' => $res['concepto'] . ' - Cliente: ' . $cliente->c_nombre . ' ' . $cliente->c_apellido,
                        'numero_referencia' => $numRef,
                        'tramite_tipo' => 'Vario',
                        'tramite_id' => $v->id_tramite_varios,
                    ]);
                }

                $unpaidPersonalizados = TramitePersonalizado::where('id_cliente', $cliente->id_cliente)->where('saldo', '>', 0)->get();
                foreach ($unpaidPersonalizados as $tp) {
                    if ($montoRestante <= 0) break;
                    $abono = min($montoRestante, floatval($tp->saldo));
                    $res = $this->aplicarPagoATramite('personalizados', $tp->id, $abono);
                    $conceptosPagos[] = $res['concepto'];
                    $montoRestante -= $abono;

                    $pago = Pago::create([
                        'cliente_id' => $cliente->id_cliente,
                        'monto' => $abono,
                        'concepto' => $res['concepto'],
                        'usuario' => $user->name,
                        'oficina' => $user->office ?? 'General',
                        'caja_sesion_id' => $caja->id,
                        'metodo_pago' => $metodoPago,
                        'banco_id' => $bancoId,
                        'tarjeta_id' => $tarjetaId,
                        'numero_referencia' => $numRef,
                        'tramite_tipo' => 'Personalizado',
                        'tramite_id' => $tp->id,
                    ]);

                    $this->cajaService->registrarMovimiento([
                        'caja_sesion_id' => $caja->id,
                        'user_id' => $user->id,
                        'cliente_id' => $cliente->id_cliente,
                        'tipo' => 'ingreso_tramite',
                        'monto' => $abono,
                        'metodo_pago' => $metodoPago,
                        'banco_id' => $bancoId,
                        'tarjeta_id' => $tarjetaId,
                        'pago_id' => $pago->id,
                        'concepto' => $res['concepto'] . ' - Cliente: ' . $cliente->c_nombre . ' ' . $cliente->c_apellido,
                        'numero_referencia' => $numRef,
                        'tramite_tipo' => 'Personalizado',
                        'tramite_id' => $tp->id,
                    ]);
                }
            }

            // Actualizar cartera global del cliente
            $cliente->c_abonado += $montoTotalPago;
            $cliente->c_saldo = max(0, $cliente->c_saldo - $montoTotalPago);
            $cliente->save();

            DB::commit();

            return redirect()->back()
                ->with('success', '¡Cobro de Cartera por $' . number_format($montoTotalPago, 2) . ' procesado con éxito en Caja #' . $caja->id . '!')
                ->with('imprimir_recibo', route('clientes.recibo_abono', ['cliente' => $cliente->id_cliente, 'monto' => $montoTotalPago]));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al procesar el cobro de cartera: ' . $e->getMessage());
        }
    }

    /**
     * Helper to apply payment to individual trámite record.
     */
    protected function aplicarPagoATramite(string $tipo, int $id, float $monto): array
    {
        switch ($tipo) {
            case 'poderes':
                $t = TramitePoder::findOrFail($id);
                $t->tp_abono_tramite += $monto;
                $t->tp_saldo = max(0, $t->tp_costo_tramite - $t->tp_abono_tramite);
                $t->save();
                return [
                    'tipo_name' => 'Poder',
                    'concepto' => 'Cobro Poder #' . $t->id_tram_poderes . ' (' . ($t->tp_razon_otorga_poder ?: 'General') . ')',
                ];

            case 'divorcios':
                $t = TramiteDivorcio::findOrFail($id);
                $t->td_abono += $monto;
                $t->td_saldo = max(0, $t->td_valor - $t->td_abono);
                $t->save();
                return [
                    'tipo_name' => 'Divorcio',
                    'concepto' => 'Cobro Divorcio #' . $t->id_tram_div,
                ];

            case 'impuestos':
                $t = TramiteImpuesto::findOrFail($id);
                $t->ti_abono_tramite += $monto;
                $t->ti_saldo = max(0, $t->ti_costo_tramite - $t->ti_abono_tramite);
                $t->save();
                return [
                    'tipo_name' => 'Impuesto',
                    'concepto' => 'Cobro Impuestos #' . $t->id_tram_impuestos,
                ];

            case 'varios':
                $t = TramiteVario::findOrFail($id);
                $t->tv_abono_tramite += $monto;
                $t->tv_saldo = max(0, $t->tv_valor_tramite - $t->tv_abono_tramite);
                $t->save();
                return [
                    'tipo_name' => 'Vario',
                    'concepto' => 'Cobro Trámite Vario #' . $t->id_tramite_varios . ' (' . ($t->tv_motivo ?: 'General') . ')',
                ];

            case 'personalizados':
                $t = TramitePersonalizado::with('tipoTramite')->findOrFail($id);
                $t->abono_tramite += $monto;
                $t->saldo = max(0, $t->valor_tramite - $t->abono_tramite);
                $t->save();
                return [
                    'tipo_name' => 'Personalizado',
                    'concepto' => 'Cobro Trámite: ' . ($t->tipoTramite->nombre ?? 'Personalizado'),
                ];

            default:
                throw new \InvalidArgumentException("Tipo de trámite no válido: {$tipo}");
        }
    }
}
