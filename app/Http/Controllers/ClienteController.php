<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Banco;
use App\Models\Tarjeta;
use App\Services\CajaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClientesExport;
use App\Exports\ClientesTemplateExport;
use App\Imports\ClientesImport;
use Barryvdh\DomPDF\Facade\Pdf;

class ClienteController extends Controller
{
    protected CajaService $cajaService;

    public function __construct(CajaService $cajaService)
    {
        $this->cajaService = $cajaService;
    }

    private function buildQuery(Request $request)
    {
        $user = Auth::user();
        $query = Cliente::query();

        if ($user->role !== 'Administrador' && $user->role !== 'Supervisor') {
            $query->where('c_oficina_registro', $user->office);
        } elseif ($request->filled('oficina')) {
            $query->where('c_oficina_registro', $request->oficina);
        }

        if ($request->filled('buscar')) {
            $busqueda = $request->input('buscar');
            $query->where(function ($q) use ($busqueda) {
                $q->where('c_nombre', 'like', "%{$busqueda}%")
                  ->orWhere('c_apellido', 'like', "%{$busqueda}%")
                  ->orWhere('c_identificacion', 'like', "%{$busqueda}%")
                  ->orWhere('c_telefono', 'like', "%{$busqueda}%");
            });
        }

        if ($request->filled('deuda')) {
            if ($request->deuda === 'con_deuda') {
                $query->where('c_saldo', '>', 0);
            } elseif ($request->deuda === 'sin_deuda') {
                $query->where('c_saldo', '<=', 0);
            }
        }

        return $query;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasPermission('clientes.view')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso para consultar el listado de clientes.');
        }

        $query = $this->buildQuery($request);

        $statsQuery = clone $query;
        $stats = [
            'total_clientes' => $statsQuery->count(),
            'total_deuda' => (float) $statsQuery->sum('c_deuda'),
            'total_abonado' => (float) $statsQuery->sum('c_abonado'),
            'total_saldo' => (float) $statsQuery->sum('c_saldo'),
            'con_deuda_count' => (clone $query)->where('c_saldo', '>', 0)->count(),
        ];

        $clientes = $query->paginate(15);
        $oficinas = Cliente::select('c_oficina_registro')->distinct()->pluck('c_oficina_registro');
        $bancos = Banco::activos()->orderBy('nombre')->get();
        $tarjetas = Tarjeta::with('banco')->activas()->orderBy('nombre')->get();
        $cajaAbierta = $this->cajaService->getCajaAbierta($user);

        return view('clientes.index', compact('clientes', 'user', 'oficinas', 'stats', 'bancos', 'tarjetas', 'cajaAbierta'));
    }

    /**
     * Export a listing of the resource to excel.
     */
    public function export(Request $request)
    {
        if (!Auth::user()->hasPermission('clientes.export')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso para exportar el catálogo de clientes.');
        }

        return Excel::download(new ClientesExport($this->buildQuery($request)), 'clientes.xlsx');
    }

    /**
     * Import clients from an Excel or CSV file.
     */
    public function import(Request $request)
    {
        if (!Auth::user()->hasPermission('clientes.create')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso para importar clientes.');
        }

        $request->validate([
            'archivo_excel' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:20480'],
            'duplicados' => ['nullable', 'in:actualizar,omitir'],
            'oficina_registro' => ['nullable', 'string', 'max:100'],
        ], [
            'archivo_excel.required' => 'Debes seleccionar un archivo de Excel (.xlsx, .xls o .csv) para importar.',
            'archivo_excel.mimes' => 'El formato del archivo debe ser .xlsx, .xls o .csv.',
            'archivo_excel.max' => 'El archivo no debe exceder los 20 MB.',
        ]);

        $duplicados = $request->input('duplicados', 'actualizar');
        $user = Auth::user();
        $oficinaDefault = $request->filled('oficina_registro') ? $request->input('oficina_registro') : ($user->office ?? 'General');

        try {
            $import = new ClientesImport($duplicados, $oficinaDefault, $user->name ?? 'Sistema');
            Excel::import($import, $request->file('archivo_excel'));

            $mensaje = "¡Importación completada con éxito! Se procesaron {$import->totalRows} filas: {$import->createdCount} clientes nuevos creados, {$import->updatedCount} clientes existentes actualizados.";
            
            if ($import->skippedCount > 0) {
                $mensaje .= " ({$import->skippedCount} filas omitidas).";
            }

            return redirect()->route('clientes.index')->with('success', $mensaje);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMsg = 'Error de validación en Excel: ';
            foreach ($failures as $failure) {
                $errorMsg .= "Fila {$failure->row()}: " . implode(', ', $failure->errors()) . ' ';
            }
            return redirect()->route('clientes.index')->with('error', $errorMsg);
        } catch (\Exception $e) {
            return redirect()->route('clientes.index')->with('error', 'Ocurrió un error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Download a sample Excel template for client imports.
     */
    public function downloadTemplate()
    {
        if (!Auth::user()->hasPermission('clientes.create') && !Auth::user()->hasPermission('clientes.view')) {
            abort(403, 'Acceso denegado: No tienes autorización para descargar la plantilla.');
        }

        return Excel::download(new ClientesTemplateExport(), 'plantilla_importacion_clientes.xlsx');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->hasPermission('clientes.create')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso para registrar nuevos clientes.');
        }

        return view('clientes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission('clientes.create')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso para registrar nuevos clientes.');
        }

        // Validation and creation
        $validated = $request->validate([
            'c_identificacion' => 'required|string|max:50',
            'c_nombre' => 'required|string|max:50',
            'c_apellido' => 'required|string|max:50',
            'c_telefono' => 'required|string|max:50',
            'c_edad' => 'required|integer',
            'c_direccion' => 'required|string|max:50',
            'c_pais' => 'required|string|max:50',
            'c_estado' => 'required|string|max:50',
            'c_ciudad' => 'required|string|max:50',
            'c_codpostal' => 'required|string|max:50',
            'c_email' => 'required|email|max:50',
            'c_napartamento' => 'required|string|max:50',
        ]);

        $validated['c_register'] = Auth::user()->name;
        $validated['c_oficina_registro'] = Auth::user()->office;
        $validated['c_abonado'] = 0;
        $validated['c_deuda'] = 0;
        $validated['c_saldo'] = 0;

        Cliente::create($validated);

        return redirect()->route('clientes.index')->with('success', 'Cliente creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        if (!Auth::user()->hasPermission('clientes.edit')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso para editar la información de clientes.');
        }

        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        if (!Auth::user()->hasPermission('clientes.edit')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso para editar la información de clientes.');
        }

        // Validation and update
        $validated = $request->validate([
            'c_identificacion' => 'required|string|max:50',
            'c_nombre' => 'required|string|max:50',
            'c_apellido' => 'required|string|max:50',
            'c_telefono' => 'required|string|max:50',
            'c_edad' => 'required|integer',
            'c_direccion' => 'required|string|max:50',
            'c_pais' => 'required|string|max:50',
            'c_estado' => 'required|string|max:50',
            'c_ciudad' => 'required|string|max:50',
            'c_codpostal' => 'required|string|max:50',
            'c_email' => 'required|email|max:50',
            'c_napartamento' => 'required|string|max:50',
        ]);

        $cliente->update($validated);

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!Auth::user()->hasPermission('clientes.delete')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso para eliminar clientes.');
        }

        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado exitosamente.');
    }

    /**
     * Store a payment (abono) for a specific client.
     */
    public function abonar(Request $request, Cliente $cliente)
    {
        if (!Auth::user()->hasPermission('clientes.abonar')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso para registrar abonos o pagos.');
        }

        $request->validate([
            'nuevo_abono' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:Efectivo,Cheque,Transferencia,Tarjeta,Zelle',
            'banco_id' => 'nullable|exists:bancos,id',
            'tarjeta_id' => 'nullable|exists:tarjetas,id',
            'numero_referencia' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $caja = $this->cajaService->getCajaAbierta($user);

        // All users who transact require an open cash register
        if (!$caja) {
            return redirect()->back()->with('error', 'No tienes una caja abierta actualmente. Debes abrir caja para poder registrar abonos.');
        }

        $nuevo_abono = floatval($request->input('nuevo_abono'));
        $abono_actual = floatval($cliente->c_abonado);
        $deuda_actual = floatval($cliente->c_saldo);

        $nuevo_total = $abono_actual + $nuevo_abono;
        $nuevo_saldo = $deuda_actual - $nuevo_abono;

        if ($nuevo_saldo < -0.01) {
            return redirect()->back()->with('error', 'El abono ($' . number_format($nuevo_abono, 2) . ') no puede ser mayor al saldo pendiente ($' . number_format($deuda_actual, 2) . ').');
        }

        DB::beginTransaction();
        try {
            $cliente->c_abonado = $nuevo_total;
            $cliente->c_saldo = max(0, $nuevo_saldo);
            $cliente->save();

            // 1. Registrar en historial de pagos
            $pago = Pago::create([
                'cliente_id' => $cliente->id_cliente,
                'monto' => $nuevo_abono,
                'concepto' => 'Abono a deuda cliente',
                'usuario' => $user->name,
                'oficina' => $user->office ?? 'General',
                'caja_sesion_id' => $caja->id,
                'metodo_pago' => $request->metodo_pago,
                'banco_id' => $request->banco_id,
                'tarjeta_id' => $request->tarjeta_id,
                'numero_referencia' => $request->numero_referencia,
            ]);

            // 2. Registrar movimiento en la sesión de caja activa
            $this->cajaService->registrarMovimiento([
                'caja_sesion_id' => $caja->id,
                'user_id' => $user->id,
                'cliente_id' => $cliente->id_cliente,
                'tipo' => 'ingreso_abono',
                'monto' => $nuevo_abono,
                'metodo_pago' => $request->metodo_pago,
                'banco_id' => $request->banco_id,
                'tarjeta_id' => $request->tarjeta_id,
                'pago_id' => $pago->id,
                'concepto' => 'Abono de cliente: ' . $cliente->c_nombre . ' ' . $cliente->c_apellido,
                'numero_referencia' => $request->numero_referencia,
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', '¡Abono de $' . number_format($nuevo_abono, 2) . ' registrado exitosamente en la Caja #' . $caja->id . '!')
                ->with('imprimir_recibo', route('clientes.recibo_abono', ['cliente' => $cliente->id_cliente, 'monto' => $nuevo_abono]));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al procesar el abono: ' . $e->getMessage());
        }
    }

    /**
     * Generate and stream a PDF receipt for a payment.
     */
    public function reciboAbono(Request $request, Cliente $cliente)
    {
        $monto = floatval($request->query('monto', 0));
        
        if ($monto <= 0) {
            return redirect()->route('clientes.index')->with('error', 'Monto inválido para el recibo.');
        }

        $data = [
            'cliente' => $cliente,
            'monto' => $monto,
            'fecha' => now()->format('d/m/Y H:i:s'),
            'usuario' => Auth::user()->name,
            'oficina' => Auth::user()->office,
        ];

        $pdf = Pdf::loadView('clientes.recibo_pdf', $data)->setPaper('letter', 'portrait');
        
        return $pdf->stream('Recibo_Abono_' . $cliente->c_identificacion . '.pdf');
    }

    /**
     * View payment history for a client.
     */
    public function pagos(Cliente $cliente)
    {
        $pagos = $cliente->pagos()->with(['banco', 'tarjeta', 'cajaSesion'])->orderBy('created_at', 'desc')->get();
        return view('clientes.pagos', compact('cliente', 'pagos'));
    }
}
