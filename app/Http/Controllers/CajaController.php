<?php

namespace App\Http\Controllers;

use App\Models\CajaSesion;
use App\Models\CajaMovimiento;
use App\Models\Banco;
use App\Models\Tarjeta;
use App\Services\CajaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;

class CajaController extends Controller
{
    protected CajaService $cajaService;

    public function __construct(CajaService $cajaService)
    {
        $this->cajaService = $cajaService;
    }

    /**
     * Display current cash register status for the authenticated user.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user->hasPermission('cajas.view') && !$user->hasPermission('cajas.operar') && !$user->hasPermission('cajas.historial')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso para acceder al módulo de caja.');
        }

        $cajaActual = $this->cajaService->getCajaAbierta($user);

        if (!$cajaActual) {
            return view('cajas.index', [
                'cajaActual' => null,
                'totales' => null,
                'movimientos' => collect(),
            ]);
        }

        $totales = $this->cajaService->calcularTotalesSistema($cajaActual);
        $movimientos = $cajaActual->movimientos()
            ->with(['cliente', 'banco', 'tarjeta'])
            ->orderBy('created_at', 'desc')
            ->get();

        $bancos = Banco::activos()->orderBy('nombre')->get();
        $tarjetas = Tarjeta::with('banco')->activas()->orderBy('nombre')->get();

        return view('cajas.index', compact('cajaActual', 'totales', 'movimientos', 'bancos', 'tarjetas'));
    }

    /**
     * Open a new cash register session.
     */
    public function abrir(Request $request)
    {
        if (!Auth::user()->hasPermission('cajas.create') && !Auth::user()->hasPermission('cajas.operar')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso para aperturar cajas.');
        }

        $request->validate([
            'monto_apertura' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            $monto = floatval($request->input('monto_apertura'));
            $caja = $this->cajaService->abrirCaja(Auth::user(), $monto, $request->input('observaciones'));

            return redirect()->route('cajas.index')->with('success', '¡Caja #' . $caja->id . ' abierta exitosamente con $' . number_format($monto, 2) . '!');
        } catch (Exception $e) {
            return redirect()->route('cajas.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Register a quick manual movement (Ingreso Extra / Gasto Menor de Oficina).
     */
    public function registrarMovimientoManual(Request $request)
    {
        if (!Auth::user()->hasPermission('cajas.create') && !Auth::user()->hasPermission('cajas.operar')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso para registrar movimientos de caja.');
        }

        $request->validate([
            'tipo' => 'required|in:ingreso_extra,egreso_gasto,egreso_retiro',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:Efectivo,Cheque,Transferencia,Tarjeta,Zelle',
            'concepto' => 'required|string|max:255',
            'numero_referencia' => 'nullable|string|max:100',
            'banco_id' => 'nullable|exists:bancos,id',
            'tarjeta_id' => 'nullable|exists:tarjetas,id',
        ]);

        try {
            $user = Auth::user();
            $caja = $this->cajaService->requireCajaAbierta($user);

            $this->cajaService->registrarMovimiento([
                'caja_sesion_id' => $caja->id,
                'user_id' => $user->id,
                'tipo' => $request->tipo,
                'monto' => floatval($request->monto),
                'metodo_pago' => $request->metodo_pago,
                'concepto' => $request->concepto,
                'numero_referencia' => $request->numero_referencia,
                'banco_id' => $request->banco_id,
                'tarjeta_id' => $request->tarjeta_id,
            ]);

            return redirect()->route('cajas.index')->with('success', 'Movimiento registrado correctamente en caja.');
        } catch (Exception $e) {
            return redirect()->route('cajas.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Show the Arqueo & Closure interface with live square calculation.
     */
    public function showCerrar()
    {
        $user = Auth::user();
        $caja = $this->cajaService->getCajaAbierta($user);

        if (!$caja) {
            return redirect()->route('cajas.index')->with('error', 'No tienes una caja abierta para cerrar.');
        }

        $totales = $this->cajaService->calcularTotalesSistema($caja);
        $movimientos = $caja->movimientos()->with(['cliente', 'banco', 'tarjeta'])->orderBy('created_at', 'desc')->get();

        return view('cajas.cerrar', compact('caja', 'totales', 'movimientos'));
    }

    /**
     * API endpoint to validate live balance before submitting.
     */
    public function checkCuadre(Request $request)
    {
        $user = Auth::user();
        $caja = $this->cajaService->getCajaAbierta($user);

        if (!$caja) {
            return response()->json(['error' => 'No hay caja abierta'], 404);
        }

        $declarados = [
            'efectivo' => floatval($request->input('monto_efectivo', 0)),
            'cheque' => floatval($request->input('monto_cheque', 0)),
            'transferencia' => floatval($request->input('monto_transferencia', 0)),
            'tarjeta' => floatval($request->input('monto_tarjeta', 0)),
            'zelle' => floatval($request->input('monto_zelle', 0)),
        ];

        $resultado = $this->cajaService->validarCuadre($caja, $declarados);

        return response()->json($resultado);
    }

    /**
     * Process the cash register closing.
     */
    public function cerrar(Request $request)
    {
        $request->validate([
            'monto_efectivo' => 'required|numeric|min:0',
            'monto_cheque' => 'required|numeric|min:0',
            'monto_transferencia' => 'required|numeric|min:0',
            'monto_tarjeta' => 'required|numeric|min:0',
            'monto_zelle' => 'required|numeric|min:0',
            'observaciones_cierre' => 'nullable|string|max:1000',
        ]);

        try {
            $user = Auth::user();
            $caja = $this->cajaService->requireCajaAbierta($user);

            $declarados = [
                'efectivo' => floatval($request->input('monto_efectivo')),
                'cheque' => floatval($request->input('monto_cheque')),
                'transferencia' => floatval($request->input('monto_transferencia')),
                'tarjeta' => floatval($request->input('monto_tarjeta')),
                'zelle' => floatval($request->input('monto_zelle')),
            ];

            // Validation strictly requires cuadre = true
            $cajaCerrada = $this->cajaService->cerrarCaja($caja, $declarados, $request->input('observaciones_cierre'), false);

            return redirect()->route('cajas.index')
                ->with('success', '¡Caja #' . $cajaCerrada->id . ' cerrada y cuadrada exitosamente!')
                ->with('imprimir_cierre', route('cajas.acta_pdf', $cajaCerrada->id));
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show cashier personal session history with advanced filters.
     */
    public function historial(Request $request)
    {
        $user = Auth::user();
        $query = CajaSesion::where('user_id', $user->id);

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                if (is_numeric($buscar)) {
                    $q->where('id', $buscar);
                }
                $q->orWhere('observaciones_apertura', 'like', "%{$buscar}%")
                  ->orWhere('observaciones_cierre', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('estado') && $request->estado !== 'todos') {
            if ($request->estado === 'abierta') {
                $query->where('estado', 'abierta');
            } elseif ($request->estado === 'cerrada') {
                $query->where('estado', 'cerrada');
            } elseif ($request->estado === 'cuadrada') {
                $query->where('estado', 'cerrada')->where('cuadrado', true);
            } elseif ($request->estado === 'descuadre') {
                $query->where('estado', 'cerrada')->where('cuadrado', false);
            }
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_apertura', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_apertura', '<=', $request->fecha_hasta);
        }

        $statsQuery = clone $query;
        $stats = [
            'total_sesiones' => (clone $query)->count(),
            'total_recaudado' => (float) (clone $query)->sum('total_sistema_total'),
            'abiertas_count' => (clone $query)->where('estado', 'abierta')->count(),
            'cuadradas_count' => (clone $query)->where('estado', 'cerrada')->where('cuadrado', true)->count(),
        ];

        $cajas = $query->orderBy('fecha_apertura', 'desc')->paginate(15)->withQueryString();

        return view('cajas.historial', compact('cajas', 'stats'));
    }

    /**
     * Generate PDF of the cash register closure report / receipt.
     */
    public function actaPdf($id)
    {
        $user = Auth::user();
        $caja = CajaSesion::with(['user', 'movimientos.cliente', 'movimientos.banco', 'movimientos.tarjeta'])
            ->findOrFail($id);

        // El Administrador (y Supervisor) puede revisar e imprimir todas las cajas.
        // Los colaboradores regulares solo pueden ver e imprimir las cajas que ellos mismos abrieron.
        if ($user->role !== 'Administrador' && $user->role !== 'Supervisor') {
            if ($caja->user_id !== $user->id) {
                abort(403, 'Acceso restringido: Solo puedes consultar e imprimir las actas de las cajas abiertas por tu propio usuario.');
            }
        }

        $totales = $this->cajaService->calcularTotalesSistema($caja);

        $data = [
            'caja' => $caja,
            'totales' => $totales,
            'movimientos' => $caja->movimientos,
            'fecha_impresion' => now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('cajas.acta_cierre_pdf', $data);
        return $pdf->stream('Acta_Cierre_Caja_' . $caja->id . '.pdf');
    }
}
