<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\TramiteDivorcio;
use App\Models\Pago;
use App\Models\Banco;
use App\Models\Tarjeta;
use App\Services\CajaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TramiteDivorcioController extends Controller
{
    protected CajaService $cajaService;

    public function __construct(CajaService $cajaService)
    {
        $this->cajaService = $cajaService;
    }

    public function create(Cliente $cliente)
    {
        if (!Auth::user()->hasPermission('divorcios.create')) {
            abort(403, 'Acceso restringido: No tienes permiso para crear trámites de divorcio.');
        }

        $bancos = Banco::activos()->orderBy('nombre')->get();
        $tarjetas = Tarjeta::with('banco')->activas()->orderBy('nombre')->get();
        $cajaAbierta = $this->cajaService->getCajaAbierta(Auth::user());

        return view('divorcios.create', compact('cliente', 'bancos', 'tarjetas', 'cajaAbierta'));
    }

    public function store(Request $request, Cliente $cliente)
    {
        if (!Auth::user()->hasPermission('divorcios.create')) {
            abort(403, 'Acceso restringido: No tienes permiso para crear trámites de divorcio.');
        }
        $validated = $request->validate([
            'ofifirmar' => ['required', 'string'],
            'fecha' => ['nullable', 'date'],
            
            // Radio: tipo_divorcio (Controvertido, Consensual, Notarial)
            'tipo_divorcio' => ['required', 'string'],
            'esta_separado' => ['required', 'in:0,1'],
            'hijos' => ['required', 'in:0,1'],
            
            // Checkboxes:
            'posee_partida_matrimonio' => ['nullable', 'boolean'],
            'posee_partida_nacimiento_menores' => ['nullable', 'boolean'],
            
            // Conyuge:
            'nombre_conyugue' => ['required', 'string', 'max:50'],
            'identificacion_conyugue' => ['nullable', 'string', 'max:50'],
            'direccion_conyugue' => ['nullable', 'string', 'max:50'],
            'apartamento_conyugue' => ['nullable', 'string', 'max:50'],
            'ciudad_conyugue' => ['nullable', 'string', 'max:50'],
            'estado_conyugue' => ['nullable', 'string', 'max:50'],
            'postal_conyugue' => ['nullable', 'string', 'max:50'],
            'telefono_conyugue' => ['nullable', 'string', 'max:50'],
            
            // Matrimonio:
            'lugar_matrimonio' => ['nullable', 'string', 'max:50'],
            'fecha_matrimonio' => ['nullable', 'date'],
            'tiempo_separacion' => ['nullable', 'string', 'max:50'],
            
            'motivo' => ['nullable', 'string'],
            'con_quien_vive' => ['nullable', 'string'],
            
            'contacto_ecuador' => ['nullable', 'string', 'max:50'],
            'telefono_contacto_ecuador' => ['nullable', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string'],
            
            'honorarios' => ['required', 'numeric', 'min:0'],
            'abono' => ['nullable', 'numeric', 'min:0'],
            'metodo_pago' => ['nullable', 'string', 'in:Efectivo,Cheque,Transferencia,Tarjeta,Crédito,Zelle'],
            'banco_id' => ['nullable', 'exists:bancos,id'],
            'tarjeta_id' => ['nullable', 'exists:tarjetas,id'],
            'numero_referencia' => ['nullable', 'string', 'max:100'],
        ]);

        $honorarios = floatval($validated['honorarios']);
        $abono = floatval($validated['abono'] ?? 0);
        $saldo = max(0, $honorarios - $abono);

        $user = Auth::user();
        $caja = $this->cajaService->getCajaAbierta($user);

        if ($abono > 0 && !$caja) {
            return back()->withInput()->with('error', '⚠️ No tienes una caja abierta para recibir este pago en tu turno. Si el cliente pagará en ventanilla de caja, deja el "Abono Inicial" en $0.00 y el trámite se registrará en la Cartera de Clientes.');
        }

        DB::beginTransaction();

        try {
            $tramite = new TramiteDivorcio();
            $tramite->id_cliente = $cliente->id_cliente;
            $tramite->id_usuario = Auth::id();
            $tramite->td_oficina = Auth::user()->office;
            $tramite->td_fecha = $validated['fecha'] ?? date('Y-m-d');
            $tramite->td_firmar_en = $validated['ofifirmar'];
            
            $tramite->td_controvertido = ($validated['tipo_divorcio'] == 'Controvertido') ? 1 : 0;
            $tramite->td_consensual = ($validated['tipo_divorcio'] == 'Consensual') ? 1 : 0;
            $tramite->td_notarial = ($validated['tipo_divorcio'] == 'Notarial') ? 1 : 0;
            
            $tramite->td_separados = ($validated['esta_separado'] == 1) ? 1 : 0;
            $tramite->td_noseparados = ($validated['esta_separado'] == 0) ? 1 : 0;
            
            $tramite->td_hijos = $validated['hijos'];
            
            $tramite->td_ep_matrimonio = $request->has('posee_partida_matrimonio') ? 1 : 0;
            $tramite->td_ep_nacimiento = $request->has('posee_partida_nacimiento_menores') ? 1 : 0;
            
            $tramite->td_nombre_c = $validated['nombre_conyugue'];
            $tramite->td_identificacion_c = $validated['identificacion_conyugue'] ?? '';
            $tramite->td_direccion_c = $validated['direccion_conyugue'] ?? '';
            $tramite->td_apt_c = $validated['apartamento_conyugue'] ?? '';
            $tramite->td_ciudad_c = $validated['ciudad_conyugue'] ?? '';
            $tramite->td_estado_c = $validated['estado_conyugue'] ?? '';
            $tramite->td_cpostal_c = $validated['postal_conyugue'] ?? '';
            $tramite->td_telefono_c = $validated['telefono_conyugue'] ?? '';
            
            $tramite->td_lugar_matrimonio = $validated['lugar_matrimonio'] ?? '';
            $tramite->td_fecha_matrimonio = $validated['fecha_matrimonio'] ?? null;
            $tramite->td_tiempo_separacion = $validated['tiempo_separacion'] ?? '';
            
            $tramite->td_motivo_divorcio = $validated['motivo'] ?? '';
            $tramite->td_con_quien_vive = $validated['con_quien_vive'] ?? '';
            
            $tramite->td_estado_contac_ecuador = $validated['contacto_ecuador'] ?? '';
            $tramite->td_tel_ecuador = $validated['telefono_contacto_ecuador'] ?? '';
            $tramite->td_observaciones = $validated['observaciones'] ?? '';
            
            $tramite->td_mpago = $validated['metodo_pago'] ?? 'Efectivo';
            $tramite->td_valor = $honorarios;
            $tramite->td_abono = $abono;
            $tramite->td_saldo = $saldo;

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
                    'concepto' => 'Abono Inicial - Divorcio #' . $tramite->id_tram_div,
                    'usuario' => Auth::user()->name,
                    'oficina' => Auth::user()->office,
                    'caja_sesion_id' => $caja->id,
                    'metodo_pago' => $validated['metodo_pago'] ?? 'Efectivo',
                    'banco_id' => $validated['banco_id'] ?? null,
                    'tarjeta_id' => $validated['tarjeta_id'] ?? null,
                    'numero_referencia' => $validated['numero_referencia'] ?? null,
                    'tramite_tipo' => 'Divorcio',
                    'tramite_id' => $tramite->id_tram_div,
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
                    'concepto' => 'Abono Inicial - Trámite Divorcio #' . $tramite->id_tram_div,
                    'numero_referencia' => $validated['numero_referencia'] ?? null,
                    'tramite_tipo' => 'Divorcio',
                    'tramite_id' => $tramite->id_tram_div,
                ]);
            }

            DB::commit();

            $mensaje = 'Trámite de Divorcio guardado correctamente.' . ($saldo > 0 ? ' Saldo de $' . number_format($saldo, 2) . ' añadido a la Cartera del Cliente para cobro en caja.' : '');

            $redirect = redirect()->route('clientes.tramites', $cliente->id_cliente)
                ->with('success', $mensaje)
                ->with('imprimir_tramite', route('divorcios.show', $tramite->id_tram_div));
                
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
        $tramite = TramiteDivorcio::with(['cliente', 'usuario'])->findOrFail($id);
        $cliente = $tramite->cliente;
        
        $data = [
            'tramite' => $tramite,
            'cliente' => $cliente
        ];

        $pdf = Pdf::loadView('divorcios.recibo_pdf', $data)->setPaper('letter', 'portrait');
        return $pdf->stream('Tramite_Divorcio_' . $tramite->id_tram_div . '.pdf');
    }
}
