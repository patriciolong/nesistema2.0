<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\TramiteVario;
use App\Models\Pago;
use App\Models\Banco;
use App\Models\Tarjeta;
use App\Services\CajaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TramiteVarioController extends Controller
{
    protected CajaService $cajaService;

    public function __construct(CajaService $cajaService)
    {
        $this->cajaService = $cajaService;
    }

    public function create(Request $request)
    {
        if (!Auth::user()->hasPermission('tramites_varios.create')) {
            abort(403, 'Acceso restringido: No tienes permiso para crear trámites varios.');
        }

        $clienteId = $request->query('cliente');
        if (!$clienteId) abort(404, 'Cliente no especificado');

        $cliente = Cliente::findOrFail($clienteId);
        $bancos = Banco::activos()->orderBy('nombre')->get();
        $tarjetas = Tarjeta::with('banco')->activas()->orderBy('nombre')->get();
        $cajaAbierta = $this->cajaService->getCajaAbierta(Auth::user());

        return view('tramites_varios.create', compact('cliente', 'bancos', 'tarjetas', 'cajaAbierta'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission('tramites_varios.create')) {
            abort(403, 'Acceso restringido: No tienes permiso para crear trámites varios.');
        }
        $validated = $request->validate([
            'id_cliente' => ['required', 'exists:cliente,id_cliente'],
            'tv_motivo' => ['required', 'string', 'max:50'],
            'tv_tip_documento' => ['required', 'string', 'max:50'],
            'tv_oenvio' => ['required', 'string', 'max:100'],

            'tv_nom_envio' => ['nullable', 'string', 'max:50'],
            'tv_ciudad' => ['nullable', 'string', 'max:50'],
            'tv_provincia' => ['nullable', 'string', 'max:50'],
            'tv_telefono' => ['nullable', 'string', 'max:50'],
            'tv_traducciones' => ['nullable', 'boolean'],
            'tv_notarizacion' => ['nullable', 'boolean'],
            'tv_certificacion' => ['nullable', 'boolean'],
            'tv_apostilla' => ['nullable', 'boolean'],
            'tv_valor_tramite' => ['required', 'numeric', 'min:0'],
            'tv_abono_tramite' => ['nullable', 'numeric', 'min:0'],
            'tv_observaciones' => ['nullable', 'string', 'max:250'],
            'tv_razon_t' => ['nullable', 'string', 'max:150'],
            'tv_firmar_en' => ['nullable', 'string', 'max:150'],
            'metodo_pago' => ['nullable', 'string', 'in:Efectivo,Cheque,Transferencia,Tarjeta,Crédito,Zelle'],
            'banco_id' => ['nullable', 'exists:bancos,id'],
            'tarjeta_id' => ['nullable', 'exists:tarjetas,id'],
            'numero_referencia' => ['nullable', 'string', 'max:100'],
        ]);

        $honorarios = floatval($validated['tv_valor_tramite']);
        $abono = floatval($validated['tv_abono_tramite'] ?? 0);
        $saldo = max(0, $honorarios - $abono);
        
        $user = Auth::user();
        $caja = $this->cajaService->getCajaAbierta($user);

        if ($abono > 0 && !$caja) {
            return back()->withInput()->with('error', '⚠️ No tienes una caja abierta para recibir este pago en tu turno. Si el cliente pagará en ventanilla de caja, deja el "Abono Inicial" en $0.00 y el trámite se registrará en la Cartera de Clientes.');
        }

        $validated['tv_abono_tramite'] = $abono;
        $validated['tv_saldo'] = $saldo;
        $validated['estado'] = 'en_proceso';
        
        $validated['id_usuario'] = Auth::id();
        $validated['tv_oficina'] = Auth::user()->office ?? 'General';
        $validated['tv_fecha'] = now()->format('Y-m-d');
        
        // Handle checkboxes (nullable -> false if not present)
        $validated['tv_traducciones'] = $request->has('tv_traducciones') ? 1 : 0;
        $validated['tv_notarizacion'] = $request->has('tv_notarizacion') ? 1 : 0;
        $validated['tv_certificacion'] = $request->has('tv_certificacion') ? 1 : 0;
        $validated['tv_apostilla'] = $request->has('tv_apostilla') ? 1 : 0;

        DB::beginTransaction();
        try {
            // 1. Create the Trámite
            $tramite = TramiteVario::create($validated);

            // 2. Update the client's global debt
            $cliente = Cliente::findOrFail($validated['id_cliente']);
            $cliente->c_deuda += $honorarios;
            $cliente->c_abonado += $abono;
            $cliente->c_saldo += $saldo;
            $cliente->save();

            // 3. Register the payment and caja movement (if any abono was made)
            if ($abono > 0 && $caja) {
                $pago = Pago::create([
                    'cliente_id' => $cliente->id_cliente,
                    'monto' => $abono,
                    'concepto' => 'Abono Inicial - Trámite Vario #' . $tramite->id_tramite_varios,
                    'usuario' => Auth::user()->name,
                    'oficina' => Auth::user()->office ?? 'General',
                    'caja_sesion_id' => $caja->id,
                    'metodo_pago' => $validated['metodo_pago'] ?? 'Efectivo',
                    'banco_id' => $validated['banco_id'] ?? null,
                    'tarjeta_id' => $validated['tarjeta_id'] ?? null,
                    'numero_referencia' => $validated['numero_referencia'] ?? null,
                    'tramite_tipo' => 'Vario',
                    'tramite_id' => $tramite->id_tramite_varios,
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
                    'concepto' => 'Abono Inicial - Trámite Vario #' . $tramite->id_tramite_varios,
                    'numero_referencia' => $validated['numero_referencia'] ?? null,
                    'tramite_tipo' => 'Vario',
                    'tramite_id' => $tramite->id_tramite_varios,
                ]);
            }

            DB::commit();

            $mensaje = 'Trámite Vario guardado correctamente.' . ($saldo > 0 ? ' Saldo de $' . number_format($saldo, 2) . ' añadido a la Cartera del Cliente para cobro en caja.' : '');

            // Flash session to trigger PDF generation
            $redirect = redirect()->route('clientes.tramites', $cliente->id_cliente)
                ->with('success', $mensaje)
                ->with('imprimir_tramite', route('tramites-varios.show', $tramite->id_tramite_varios));
                
            if ($abono > 0) {
                $redirect->with('imprimir_recibo', route('clientes.recibo_abono', ['cliente' => $cliente->id_cliente, 'monto' => $abono]));
            }
            
            return $redirect;
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al guardar el trámite: ' . $e->getMessage());
        }
    }

    public function show(TramiteVario $tramites_vario)
    {
        $tramite = $tramites_vario;
        $cliente = $tramite->cliente;
        
        $pdf = Pdf::loadView('tramites_varios.recibo_pdf', compact('tramite', 'cliente'))->setPaper('letter', 'portrait');
        return $pdf->stream('Tramite_Vario_' . $tramite->id_tramite_varios . '.pdf');
    }
}
