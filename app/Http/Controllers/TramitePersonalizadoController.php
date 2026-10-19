<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\TipoTramite;
use App\Models\TramitePersonalizado;
use App\Models\Pago;
use App\Models\Banco;
use App\Models\Tarjeta;
use App\Services\CajaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TramitePersonalizadoController extends Controller
{
    protected CajaService $cajaService;

    public function __construct(CajaService $cajaService)
    {
        $this->cajaService = $cajaService;
    }

    /**
     * Show the dynamic form to create a procedure instance for a client.
     */
    public function create(Request $request, Cliente $cliente, TipoTramite $tipoTramite)
    {
        if (!Auth::user()->hasPermission('tramites_personalizados.create')) {
            abort(403, 'Acceso restringido: No tienes permiso para crear trámites personalizados.');
        }

        $bancos = Banco::activos()->orderBy('nombre')->get();
        $tarjetas = Tarjeta::with('banco')->activas()->orderBy('nombre')->get();
        $cajaAbierta = $this->cajaService->getCajaAbierta(Auth::user());

        return view('tramites_personalizados.create', compact('cliente', 'tipoTramite', 'bancos', 'tarjetas', 'cajaAbierta'));
    }

    /**
     * Store a newly created procedure instance for a client.
     */
    public function store(Request $request, Cliente $cliente, TipoTramite $tipoTramite)
    {
        if (!Auth::user()->hasPermission('tramites_personalizados.create')) {
            abort(403, 'Acceso restringido: No tienes permiso para crear trámites personalizados.');
        }
        $request->validate([
            'valor_tramite' => 'required|numeric|min:0',
            'abono_tramite' => 'nullable|numeric|min:0',
            'metodo_pago' => 'nullable|string|in:Efectivo,Cheque,Transferencia,Tarjeta,Crédito,Zelle',
            'banco_id' => 'nullable|exists:bancos,id',
            'tarjeta_id' => 'nullable|exists:tarjetas,id',
            'numero_referencia' => 'nullable|string|max:100',
        ]);

        $valor_tramite = floatval($request->input('valor_tramite', 0));
        $abono_tramite = floatval($request->input('abono_tramite', 0));
        $saldo = max(0, $valor_tramite - $abono_tramite);

        $user = Auth::user();
        $caja = $this->cajaService->getCajaAbierta($user);

        if ($abono_tramite > 0 && !$caja) {
            return redirect()->back()->withInput()->with('error', '⚠️ No tienes una caja abierta para recibir este pago en tu turno. Si el cliente pagará en ventanilla de caja, deja el "Abono Inicial" en $0.00 y el trámite se registrará en la Cartera de Clientes.');
        }

        // Extract custom field data based on the schema
        $camposConfig = $tipoTramite->campos ?? [];
        $datosFormulario = [];

        foreach ($camposConfig as $campo) {
            $fieldName = $campo['name'];
            if ($campo['type'] === 'checkbox') {
                $datosFormulario[$fieldName] = $request->has($fieldName) ? 'Sí' : 'No';
            } else {
                $datosFormulario[$fieldName] = $request->input($fieldName);
            }
        }

        DB::beginTransaction();
        try {
            // 1. Guardar el trámite personalizado
            $tramite = TramitePersonalizado::create([
                'tipo_tramite_id' => $tipoTramite->id,
                'id_cliente' => $cliente->id_cliente,
                'datos_formulario' => $datosFormulario,
                'valor_tramite' => $valor_tramite,
                'abono_tramite' => $abono_tramite,
                'saldo' => $saldo,
                'observaciones' => $request->input('observaciones'),
                'fecha' => now()->format('Y-m-d'),
                'usuario' => Auth::user()->name ?? 'Sistema',
                'oficina' => Auth::user()->office ?? 'General',
                'estado' => 'en_proceso',
            ]);

            // 2. Actualizar deuda/saldo global del cliente
            $cliente->c_deuda += $valor_tramite;
            $cliente->c_abonado += $abono_tramite;
            $cliente->c_saldo += $saldo;
            $cliente->save();

            // 3. Registrar el abono en el historial de pagos y en la caja activa
            if ($abono_tramite > 0 && $caja) {
                $pago = Pago::create([
                    'cliente_id' => $cliente->id_cliente,
                    'monto' => $abono_tramite,
                    'concepto' => 'Abono trámite: ' . $tipoTramite->nombre,
                    'usuario' => Auth::user()->name ?? 'Sistema',
                    'oficina' => Auth::user()->office ?? 'General',
                    'caja_sesion_id' => $caja->id,
                    'metodo_pago' => $request->metodo_pago ?? 'Efectivo',
                    'banco_id' => $request->banco_id,
                    'tarjeta_id' => $request->tarjeta_id,
                    'numero_referencia' => $request->numero_referencia,
                    'tramite_tipo' => 'Personalizado',
                    'tramite_id' => $tramite->id,
                ]);

                $this->cajaService->registrarMovimiento([
                    'caja_sesion_id' => $caja->id,
                    'user_id' => Auth::id(),
                    'cliente_id' => $cliente->id_cliente,
                    'tipo' => 'ingreso_tramite',
                    'monto' => $abono_tramite,
                    'metodo_pago' => $request->metodo_pago ?? 'Efectivo',
                    'banco_id' => $request->banco_id,
                    'tarjeta_id' => $request->tarjeta_id,
                    'pago_id' => $pago->id,
                    'concepto' => 'Abono Inicial - Trámite: ' . $tipoTramite->nombre,
                    'numero_referencia' => $request->numero_referencia,
                    'tramite_tipo' => 'Personalizado',
                    'tramite_id' => $tramite->id,
                ]);
            }

            DB::commit();

            $mensaje = '¡Trámite de ' . $tipoTramite->nombre . ' registrado correctamente!' . ($saldo > 0 ? ' Saldo de $' . number_format($saldo, 2) . ' añadido a la Cartera del Cliente para cobro en caja.' : '');

            return redirect()->route('clientes.tramites', $cliente->id_cliente)
                ->with('success', $mensaje)
                ->with('imprimir_tramite', route('tramites-personalizados.show', $tramite->id))
                ->with('imprimir_recibo', $abono_tramite > 0 ? route('clientes.recibo_abono', ['cliente' => $cliente->id_cliente, 'monto' => $abono_tramite]) : null);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al registrar trámite: ' . $e->getMessage());
        }
    }

    /**
     * Display or print PDF receipt for the custom procedure.
     */
    public function show(TramitePersonalizado $tramitePersonalizado)
    {
        $tramitePersonalizado->load(['tipoTramite', 'cliente']);
        
        $data = [
            'tramite' => $tramitePersonalizado,
            'cliente' => $tramitePersonalizado->cliente,
            'tipo' => $tramitePersonalizado->tipoTramite,
            'fecha' => $tramitePersonalizado->created_at->format('d/m/Y H:i A'),
        ];

        $pdf = Pdf::loadView('tramites_personalizados.recibo_pdf', $data)->setPaper('letter', 'portrait');
        return $pdf->stream('Tramite_' . ($tramitePersonalizado->tipoTramite->nombre ?? 'Doc') . '_' . $tramitePersonalizado->id . '.pdf');
    }
}
