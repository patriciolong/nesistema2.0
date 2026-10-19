<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\TramiteImpuesto;
use App\Models\Pago;
use App\Models\Banco;
use App\Models\Tarjeta;
use App\Services\CajaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TramiteImpuestoController extends Controller
{
    protected CajaService $cajaService;

    public function __construct(CajaService $cajaService)
    {
        $this->cajaService = $cajaService;
    }

    public function create(Cliente $cliente)
    {
        if (!Auth::user()->hasPermission('impuestos.create')) {
            abort(403, 'Acceso restringido: No tienes permiso para crear trámites de impuestos.');
        }

        $bancos = Banco::activos()->orderBy('nombre')->get();
        $tarjetas = Tarjeta::with('banco')->activas()->orderBy('nombre')->get();
        $cajaAbierta = $this->cajaService->getCajaAbierta(Auth::user());

        return view('impuestos.create', compact('cliente', 'bancos', 'tarjetas', 'cajaAbierta'));
    }

    public function store(Request $request, Cliente $cliente)
    {
        if (!Auth::user()->hasPermission('impuestos.create')) {
            abort(403, 'Acceso restringido: No tienes permiso para crear trámites de impuestos.');
        }
        $validated = $request->validate([
            'ofifirmar' => ['required', 'string'],
            'fechaim' => ['nullable', 'date'],
            'check1' => ['nullable', 'boolean'],
            'fechaeeuu' => ['nullable', 'date'],
            'anio_reporte' => ['nullable', 'string'],
            'numitin' => ['nullable', 'string'],
            'estcivil' => ['nullable', 'string'],
            'profesion' => ['nullable', 'string'],
            'dependentes' => ['nullable', 'numeric'],
            'metpago' => ['nullable', 'string'],
            'banco' => ['nullable', 'string'],
            'ncuenta' => ['nullable', 'string'],
            'nruta' => ['nullable', 'string'],
            
            'vtramite' => ['required', 'numeric', 'min:0'],
            'abono' => ['nullable', 'numeric', 'min:0'],
            'notas' => ['nullable', 'string'],
            'metodo_pago' => ['nullable', 'string', 'in:Efectivo,Cheque,Transferencia,Tarjeta,Crédito,Zelle'],
            'banco_id' => ['nullable', 'exists:bancos,id'],
            'tarjeta_id' => ['nullable', 'exists:tarjetas,id'],
            'numero_referencia' => ['nullable', 'string', 'max:100'],
        ]);

        $honorarios = floatval($validated['vtramite']);
        $abono = floatval($validated['abono'] ?? 0);
        $saldo = max(0, $honorarios - $abono);

        $user = Auth::user();
        $caja = $this->cajaService->getCajaAbierta($user);

        if ($abono > 0 && !$caja) {
            return back()->withInput()->with('error', '⚠️ No tienes una caja abierta para recibir este pago en tu turno. Si el cliente pagará en ventanilla de caja, deja el "Abono Inicial" en $0.00 y el trámite se registrará en la Cartera de Clientes.');
        }

        DB::beginTransaction();

        try {
            $tramite = new TramiteImpuesto();
            $tramite->id_cliente = $cliente->id_cliente;
            $tramite->id_usuario = Auth::id();
            $tramite->ti_oficina = Auth::user()->office;
            $tramite->ti_fecha = $validated['fechaim'] ?? date('Y-m-d');
            $tramite->ti_firmar_en = $validated['ofifirmar'];
            
            $tramite->ti_itin = $request->has('check1') ? 1 : 0;
            $tramite->ti_fechain = $validated['fechaeeuu'] ?? date('Y-m-d');
            $tramite->ti_anio_reporte = $validated['anio_reporte'] ?? '';
            $tramite->ti_nitin = $validated['numitin'] ?? '';
            $tramite->ti_ecivil = $validated['estcivil'] ?? '';
            $tramite->ti_profesion = $validated['profesion'] ?? '';
            $tramite->ti_dependientes = $validated['dependentes'] ?? '0';
            $tramite->ti_mpago = $validated['metpago'] ?? ($validated['metodo_pago'] ?? 'Efectivo');
            $tramite->ti_banco = $validated['banco'] ?? '';
            $tramite->ti_ncuenta = $validated['ncuenta'] ?? '';
            $tramite->ti_nruta = $validated['nruta'] ?? '';
            
            $tramite->ti_observacion = $validated['notas'] ?? '';
            $tramite->ti_costo_tramite = $honorarios;
            $tramite->ti_abono_tramite = $abono;
            $tramite->ti_saldo = $saldo;
            $tramite->estado = 'en_proceso';

            $tramite->save();

            // Update client's debt
            $cliente->c_deuda += $honorarios;
            $cliente->c_abonado += $abono;
            $cliente->c_saldo += $saldo;
            $cliente->save();

            // Register payment and movement if there's an abono
            if ($abono > 0 && $caja) {
                $pago = Pago::create([
                    'cliente_id' => $cliente->id_cliente,
                    'monto' => $abono,
                    'concepto' => 'Abono Inicial - Impuestos #' . $tramite->id_tram_impuestos,
                    'usuario' => Auth::user()->name,
                    'oficina' => Auth::user()->office,
                    'caja_sesion_id' => $caja->id,
                    'metodo_pago' => $validated['metodo_pago'] ?? 'Efectivo',
                    'banco_id' => $validated['banco_id'] ?? null,
                    'tarjeta_id' => $validated['tarjeta_id'] ?? null,
                    'numero_referencia' => $validated['numero_referencia'] ?? null,
                    'tramite_tipo' => 'Impuesto',
                    'tramite_id' => $tramite->id_tram_impuestos,
                ]);

                $this->cajaService->registrarMovimiento([
                    'caja_sesion_id' => $caja->id,
                    'user_id' => Auth::id(),
                    'cliente_id' => $cliente->id_cliente,
                    'tipo' => 'ingreso_tramite',
                    'monto' => $abono,
                    'metodo_pago' => $validated['metodo_pago'] ?? 'Efectivo',
                    'banco_id' => $validated['banco_id'] ?? null,
                    'tarjeta_id' => $validated['tarjeta_id'] ?? null,
                    'pago_id' => $pago->id,
                    'concepto' => 'Abono Inicial - Impuestos #' . $tramite->id_tram_impuestos,
                    'numero_referencia' => $validated['numero_referencia'] ?? null,
                    'tramite_tipo' => 'Impuesto',
                    'tramite_id' => $tramite->id_tram_impuestos,
                ]);
            }

            DB::commit();

            $mensaje = 'Declaración de Impuestos guardada correctamente.' . ($saldo > 0 ? ' Saldo de $' . number_format($saldo, 2) . ' añadido a la Cartera del Cliente para cobro en caja.' : '');

            $redirect = redirect()->route('clientes.tramites', $cliente->id_cliente)
                ->with('success', $mensaje)
                ->with('imprimir_tramite', route('impuestos.show', $tramite->id_tram_impuestos));
                
            if ($abono > 0) {
                $redirect->with('imprimir_recibo', route('clientes.recibo_abono', ['cliente' => $cliente->id_cliente, 'monto' => $abono]));
            }
            
            return $redirect;

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al guardar el trámite: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $tramite = TramiteImpuesto::with(['cliente', 'usuario'])->findOrFail($id);
        $cliente = $tramite->cliente;
        
        $data = [
            'tramite' => $tramite,
            'cliente' => $cliente
        ];

        $pdf = Pdf::loadView('impuestos.recibo_pdf', $data)->setPaper('letter', 'portrait');
        return $pdf->stream('Tramite_Impuestos_' . $tramite->id_tram_impuestos . '.pdf');
    }
}
