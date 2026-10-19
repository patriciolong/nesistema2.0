<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\TipoTramite;
use App\Models\TramitePersonalizado;
use App\Models\TramiteVario;
use App\Models\TramitePoder;
use App\Models\TramiteDivorcio;
use App\Models\TramiteImpuesto;
use App\Models\DocumentoGenerado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TramiteController extends Controller
{
    /**
     * View the central hub of all procedures for a specific client.
     */
    public function index(Cliente $cliente)
    {
        // 1. Procedimientos nativos
        $tramitesVarios = TramiteVario::where('id_cliente', $cliente->id_cliente)->get();
        $divorcios = TramiteDivorcio::where('id_cliente', $cliente->id_cliente)->get();
        $impuestos = TramiteImpuesto::where('id_cliente', $cliente->id_cliente)->get();
        $poderes = TramitePoder::where('id_cliente', $cliente->id_cliente)->get();

        // 2. Tipos de trámites personalizados activos
        $tiposPersonalizados = TipoTramite::where('activo', true)->orderBy('orden', 'asc')->get();

        // 3. Registros de trámites personalizados para este cliente
        $tramitesPersonalizados = TramitePersonalizado::with('tipoTramite')
            ->where('id_cliente', $cliente->id_cliente)
            ->orderBy('created_at', 'desc')
            ->get();

        // 4. Historial de Documentos Notariales Emitidos desde Plantillas
        $documentosGenerados = DocumentoGenerado::with('plantilla')
            ->where('id_cliente', $cliente->id_cliente)
            ->orderBy('created_at', 'desc')
            ->get();

        $cajaService = app(\App\Services\CajaService::class);
        $tieneCajaAbierta = $cajaService->hasCajaAbierta(auth()->user());
        $cajaAbierta = $cajaService->getCajaAbierta(auth()->user());
        $bancos = \App\Models\Banco::activos()->orderBy('nombre')->get();
        $tarjetas = \App\Models\Tarjeta::with('banco')->activas()->orderBy('nombre')->get();

        return view('tramites.index', compact(
            'cliente', 
            'tramitesVarios', 
            'divorcios', 
            'impuestos', 
            'poderes',
            'tiposPersonalizados',
            'tramitesPersonalizados',
            'documentosGenerados',
            'tieneCajaAbierta',
            'cajaAbierta',
            'bancos',
            'tarjetas'
        ));
    }

    /**
     * Procesar cobro en ventanilla de caja de un trámite específico.
     */
    public function cobrarTramite(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:cliente,id_cliente',
            'tramite_tipo' => 'required|string|in:poderes,divorcios,impuestos,varios,personalizados',
            'tramite_id' => 'required|integer',
            'monto_pago' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|string|in:Efectivo,Cheque,Transferencia,Tarjeta,Zelle',
            'banco_id' => 'nullable|exists:bancos,id',
            'tarjeta_id' => 'nullable|exists:tarjetas,id',
            'numero_referencia' => 'nullable|string|max:100',
        ]);

        $cajaService = app(\App\Services\CajaService::class);
        $user = \Illuminate\Support\Facades\Auth::user();
        $caja = $cajaService->getCajaAbierta($user);

        if (!$caja) {
            return redirect()->back()->with('error', '⚠️ No tienes una caja abierta actualmente. Debes abrir tu caja para poder procesar cobros.');
        }

        $cliente = Cliente::findOrFail($request->input('cliente_id'));
        $montoPago = floatval($request->input('monto_pago'));
        $tramiteTipo = $request->input('tramite_tipo');
        $tramiteId = $request->input('tramite_id');

        DB::beginTransaction();
        try {
            $concepto = 'Cobro de Trámite';
            $tipoName = 'Trámite';

            switch ($tramiteTipo) {
                case 'poderes':
                    $tramite = \App\Models\TramitePoder::findOrFail($tramiteId);
                    $saldoActual = floatval($tramite->tp_saldo);
                    if ($montoPago > $saldoActual + 0.01) {
                        return redirect()->back()->with('error', 'El monto a cobrar ($' . number_format($montoPago, 2) . ') supera el saldo pendiente del poder ($' . number_format($saldoActual, 2) . ').');
                    }
                    $tramite->tp_abono_tramite += $montoPago;
                    $tramite->tp_saldo = max(0, $tramite->tp_costo_tramite - $tramite->tp_abono_tramite);
                    $tramite->save();
                    $concepto = 'Cobro Poder #' . $tramite->id_tram_poderes . ' (' . ($tramite->tp_razon_otorga_poder ?: 'General') . ')';
                    $tipoName = 'Poder';
                    break;

                case 'divorcios':
                    $tramite = \App\Models\TramiteDivorcio::findOrFail($tramiteId);
                    $saldoActual = floatval($tramite->td_saldo);
                    if ($montoPago > $saldoActual + 0.01) {
                        return redirect()->back()->with('error', 'El monto a cobrar ($' . number_format($montoPago, 2) . ') supera el saldo pendiente del divorcio ($' . number_format($saldoActual, 2) . ').');
                    }
                    $tramite->td_abono += $montoPago;
                    $tramite->td_saldo = max(0, $tramite->td_valor - $tramite->td_abono);
                    $tramite->save();
                    $concepto = 'Cobro Divorcio #' . $tramite->id_tram_div;
                    $tipoName = 'Divorcio';
                    break;

                case 'impuestos':
                    $tramite = \App\Models\TramiteImpuesto::findOrFail($tramiteId);
                    $saldoActual = floatval($tramite->ti_saldo);
                    if ($montoPago > $saldoActual + 0.01) {
                        return redirect()->back()->with('error', 'El monto a cobrar ($' . number_format($montoPago, 2) . ') supera el saldo pendiente de impuestos ($' . number_format($saldoActual, 2) . ').');
                    }
                    $tramite->ti_abono_tramite += $montoPago;
                    $tramite->ti_saldo = max(0, $tramite->ti_costo_tramite - $tramite->ti_abono_tramite);
                    $tramite->save();
                    $concepto = 'Cobro Impuestos #' . $tramite->id_tram_impuestos;
                    $tipoName = 'Impuesto';
                    break;

                case 'varios':
                    $tramite = \App\Models\TramiteVario::findOrFail($tramiteId);
                    $saldoActual = floatval($tramite->tv_saldo);
                    if ($montoPago > $saldoActual + 0.01) {
                        return redirect()->back()->with('error', 'El monto a cobrar ($' . number_format($montoPago, 2) . ') supera el saldo pendiente del trámite ($' . number_format($saldoActual, 2) . ').');
                    }
                    $tramite->tv_abono_tramite += $montoPago;
                    $tramite->tv_saldo = max(0, $tramite->tv_valor_tramite - $tramite->tv_abono_tramite);
                    $tramite->save();
                    $concepto = 'Cobro Trámite Vario #' . $tramite->id_tramite_varios . ' (' . ($tramite->tv_motivo ?: 'General') . ')';
                    $tipoName = 'Vario';
                    break;

                case 'personalizados':
                    $tramite = \App\Models\TramitePersonalizado::with('tipoTramite')->findOrFail($tramiteId);
                    $saldoActual = floatval($tramite->saldo);
                    if ($montoPago > $saldoActual + 0.01) {
                        return redirect()->back()->with('error', 'El monto a cobrar ($' . number_format($montoPago, 2) . ') supera el saldo pendiente del trámite ($' . number_format($saldoActual, 2) . ').');
                    }
                    $tramite->abono_tramite += $montoPago;
                    $tramite->saldo = max(0, $tramite->valor_tramite - $tramite->abono_tramite);
                    $tramite->save();
                    $concepto = 'Cobro Trámite: ' . ($tramite->tipoTramite->nombre ?? 'Personalizado');
                    $tipoName = 'Personalizado';
                    break;
            }

            // Actualizar cartera global del cliente
            $cliente->c_abonado += $montoPago;
            $cliente->c_saldo = max(0, $cliente->c_saldo - $montoPago);
            $cliente->save();

            // 1. Crear registro de Pago
            $pago = \App\Models\Pago::create([
                'cliente_id' => $cliente->id_cliente,
                'monto' => $montoPago,
                'concepto' => $concepto,
                'usuario' => $user->name,
                'oficina' => $user->office ?? 'General',
                'caja_sesion_id' => $caja->id,
                'metodo_pago' => $request->input('metodo_pago'),
                'banco_id' => $request->input('banco_id'),
                'tarjeta_id' => $request->input('tarjeta_id'),
                'numero_referencia' => $request->input('numero_referencia'),
                'tramite_tipo' => $tipoName,
                'tramite_id' => $tramiteId,
            ]);

            // 2. Registrar movimiento en la caja del cajero
            $cajaService->registrarMovimiento([
                'caja_sesion_id' => $caja->id,
                'user_id' => $user->id,
                'cliente_id' => $cliente->id_cliente,
                'tipo' => 'ingreso_tramite',
                'monto' => $montoPago,
                'metodo_pago' => $request->input('metodo_pago'),
                'banco_id' => $request->input('banco_id'),
                'tarjeta_id' => $request->input('tarjeta_id'),
                'pago_id' => $pago->id,
                'concepto' => $concepto . ' - Cliente: ' . $cliente->c_nombre . ' ' . $cliente->c_apellido,
                'numero_referencia' => $request->input('numero_referencia'),
                'tramite_tipo' => $tipoName,
                'tramite_id' => $tramiteId,
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', '¡Cobro de $' . number_format($montoPago, 2) . ' registrado exitosamente en tu Caja #' . $caja->id . '!')
                ->with('imprimir_recibo', route('clientes.recibo_abono', ['cliente' => $cliente->id_cliente, 'monto' => $montoPago]));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al procesar el cobro: ' . $e->getMessage());
        }
    }

    /**
     * Update the status of a procedure and notify the client if completed/ready.
     */
    public function cambiarEstado(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer',
            'tramite_tipo' => 'required|string|in:personalizados,poderes,divorcios,impuestos,varios,documentos',
            'tramite_id' => 'required|integer',
            'estado' => 'required|string|in:en_proceso,en_revision,listo,entregado,cancelado',
            'documento_pdf' => 'nullable|file|mimes:pdf|max:25600',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $clienteId = $request->input('cliente_id');
        $tramiteTipo = $request->input('tramite_tipo');
        $tramiteId = $request->input('tramite_id');
        $nuevoEstado = $request->input('estado');
        $observaciones = $request->input('observaciones');

        // Obtener el registro actual del trámite
        $tramite = null;
        $nombreTramite = 'Trámite Notarial';
        $oficina = null;

        switch ($tramiteTipo) {
            case 'personalizados':
                $tramite = TramitePersonalizado::with('tipoTramite')->find($tramiteId);
                if ($tramite) {
                    $nombreTramite = $tramite->tipoTramite ? $tramite->tipoTramite->nombre : 'Trámite Personalizado';
                    $oficina = $tramite->oficina;
                }
                break;

            case 'poderes':
                $tramite = TramitePoder::find($tramiteId);
                if ($tramite) {
                    $nombreTramite = 'Poder Notarial (' . ($tramite->tp_razon_otorga_poder ?: 'General') . ')';
                    $oficina = $tramite->tp_oficina;
                }
                break;

            case 'divorcios':
                $tramite = TramiteDivorcio::find($tramiteId);
                if ($tramite) {
                    $nombreTramite = 'Trámite de Divorcio';
                    $oficina = $tramite->td_oficina;
                }
                break;

            case 'impuestos':
                $tramite = TramiteImpuesto::find($tramiteId);
                if ($tramite) {
                    $nombreTramite = 'Declaración de Impuestos / ITIN';
                    $oficina = $tramite->ti_oficina;
                }
                break;

            case 'varios':
                $tramite = TramiteVario::find($tramiteId);
                if ($tramite) {
                    $nombreTramite = 'Trámite Vario: ' . ($tramite->tv_motivo ?: 'Gestión Notarial');
                    $oficina = $tramite->tv_oficina;
                }
                break;

            case 'documentos':
                $tramite = DocumentoGenerado::with('plantilla')->find($tramiteId);
                if ($tramite) {
                    $nombreTramite = $tramite->plantilla ? $tramite->plantilla->nombre_plantilla : 'Documento Notarial';
                }
                break;
        }

        if (!$tramite) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El trámite especificado no fue encontrado.',
                ], 404);
            }
            return redirect()->back()->with('error', 'El trámite especificado no fue encontrado.');
        }

        $updateData = ['estado' => $nuevoEstado];

        // Manejo de carga de documento PDF
        if ($request->hasFile('documento_pdf')) {
            $file = $request->file('documento_pdf');
            $destinationPath = public_path('uploads/tramites_documentos');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $fileName = 'doc_' . $tramiteTipo . '_' . $tramiteId . '_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.pdf';
            $file->move($destinationPath, $fileName);
            $updateData['documento_pdf'] = 'uploads/tramites_documentos/' . $fileName;
        }

        // Validación estricta: Si se cambia a 'listo', DEBE tener o subirse el documento PDF
        if ($nuevoEstado === 'listo') {
            $tienePdf = !empty($updateData['documento_pdf']) || !empty($tramite->documento_pdf);
            if (!$tienePdf) {
                $errorMsg = '⚠️ Para marcar el trámite como "Listo para entregar" es obligatorio cargar el documento en PDF.';
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMsg,
                    ], 422);
                }
                return redirect()->back()->with('error', $errorMsg);
            }

            $updateData['fecha_completado'] = now();
        } elseif ($nuevoEstado === 'entregado') {
            if (empty($tramite->fecha_completado)) {
                $updateData['fecha_completado'] = now();
            }
        }

        // Guardar cambios
        $tramite->update($updateData);

        // Si el estado cambió a 'listo', enviar notificación destacada al cliente
        if ($nuevoEstado === 'listo') {
            app(\App\Services\ClientNotificationService::class)->notificarTramiteListo(
                $clienteId,
                $tramiteTipo,
                $tramiteId,
                $nombreTramite,
                $oficina,
                $observaciones
            );
        } else {
            app(\App\Services\ClientNotificationService::class)->notificarActualizacionTramite(
                $clienteId,
                $tramiteTipo,
                $tramiteId,
                $nombreTramite,
                $nuevoEstado,
                $observaciones
            );
        }

        $documentoPdfUrl = !empty($tramite->documento_pdf) ? asset($tramite->documento_pdf) : null;

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '¡Estado actualizado a Listo para entregar exitosamente!' . ($nuevoEstado === 'listo' ? ' Notificación enviada al cliente con el documento adjunto.' : ''),
                'nuevo_estado' => $nuevoEstado,
                'documento_pdf' => $tramite->documento_pdf,
                'documento_pdf_url' => $documentoPdfUrl,
            ]);
        }

        return redirect()->back()->with('success', '¡Estado del trámite actualizado exitosamente!' . ($nuevoEstado === 'listo' ? ' Se notificó al cliente que su trámite está listo para entregar.' : ''));
    }
}
