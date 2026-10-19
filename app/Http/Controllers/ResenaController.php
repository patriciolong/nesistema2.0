<?php

namespace App\Http\Controllers;

use App\Models\ResenaTramite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResenaController extends Controller
{
    /**
     * Muestra la vista principal del módulo de Reseñas y Calificaciones de Clientes.
     */
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->input('buscar', ''));
        $filtroEstrellas = $request->input('estrellas', 'todas');
        $filtroRecomienda = $request->input('recomendaria', 'todas');
        $filtroFecha = $request->input('fecha', 'todas');

        $query = ResenaTramite::with('cliente');

        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('tramite_nombre', 'like', "%{$buscar}%")
                  ->orWhere('comentario', 'like', "%{$buscar}%")
                  ->orWhereHas('cliente', function ($cq) use ($buscar) {
                      $cq->where('c_nombre', 'like', "%{$buscar}%")
                         ->orWhere('c_apellido', 'like', "%{$buscar}%")
                         ->orWhere('c_identificacion', 'like', "%{$buscar}%")
                         ->orWhere('c_telefono', 'like', "%{$buscar}%");
                  });
            });
        }

        if ($filtroEstrellas !== 'todas' && is_numeric($filtroEstrellas)) {
            $query->where('calificacion', (int) $filtroEstrellas);
        }

        if ($filtroRecomienda === 'si') {
            $query->where('recomendaria', true);
        } elseif ($filtroRecomienda === 'no') {
            $query->where('recomendaria', false);
        }

        if ($filtroFecha === 'hoy') {
            $query->whereDate('fecha_resena', today());
        } elseif ($filtroFecha === 'semana') {
            $query->whereBetween('fecha_resena', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($filtroFecha === 'mes') {
            $query->whereMonth('fecha_resena', now()->month)
                  ->whereYear('fecha_resena', now()->year);
        }

        $resenas = $query->orderBy('fecha_resena', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Métricas y Estadísticas Globales
        $totalResenas = ResenaTramite::count();
        $promedioGeneral = $totalResenas > 0 ? round((float) ResenaTramite::avg('calificacion'), 1) : 5.0;
        $promedioAtencion = $totalResenas > 0 ? round((float) ResenaTramite::avg('atencion_personal'), 1) : 5.0;
        $promedioRapidez = $totalResenas > 0 ? round((float) ResenaTramite::avg('rapidez'), 1) : 5.0;
        $totalRecomiendan = ResenaTramite::where('recomendaria', true)->count();
        $porcentajeRecomiendan = $totalResenas > 0 ? round(($totalRecomiendan / $totalResenas) * 100, 1) : 100;

        $distribucion = [
            5 => ResenaTramite::where('calificacion', 5)->count(),
            4 => ResenaTramite::where('calificacion', 4)->count(),
            3 => ResenaTramite::where('calificacion', 3)->count(),
            2 => ResenaTramite::where('calificacion', 2)->count(),
            1 => ResenaTramite::where('calificacion', 1)->count(),
        ];

        return view('resenas.index', [
            'resenas' => $resenas,
            'totalResenas' => $totalResenas,
            'promedioGeneral' => $promedioGeneral,
            'promedioAtencion' => $promedioAtencion,
            'promedioRapidez' => $promedioRapidez,
            'porcentajeRecomiendan' => $porcentajeRecomiendan,
            'distribucion' => $distribucion,
            'filtros' => [
                'buscar' => $buscar,
                'estrellas' => $filtroEstrellas,
                'recomendaria' => $filtroRecomienda,
                'fecha' => $filtroFecha,
            ],
        ]);
    }

    /**
     * Eliminar una reseña (administrador).
     */
    public function destroy(int $id)
    {
        $resena = ResenaTramite::findOrFail($id);
        $resena->delete();

        return redirect()->route('resenas.index')->with('success', 'La reseña ha sido eliminada correctamente.');
    }

    /**
     * Exportar reporte de reseñas en formato CSV / Excel.
     */
    public function exportExcel(): StreamedResponse
    {
        $fileName = 'Reporte_Resenas_NESISTEMA_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 para apertura correcta en Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados
            fputcsv($handle, [
                'ID',
                'Fecha',
                'Cliente',
                'Identificación',
                'Teléfono',
                'Trámite',
                'Tipo Trámite',
                'Calificación General (1-5)',
                'Atención Personal (1-5)',
                'Rapidez (1-5)',
                'Recomienda Servicio',
                'Comentario / Opinión del Cliente',
            ], ';');

            ResenaTramite::with('cliente')
                ->orderBy('fecha_resena', 'desc')
                ->chunk(100, function ($rows) use ($handle) {
                    foreach ($rows as $r) {
                        $cliente = $r->cliente;
                        $nombreCliente = $cliente ? trim("{$cliente->c_nombre} {$cliente->c_apellido}") : 'Cliente';

                        fputcsv($handle, [
                            $r->id,
                            $r->fecha_resena ? $r->fecha_resena->format('d/m/Y H:i') : '',
                            $nombreCliente,
                            $cliente ? $cliente->c_identificacion : '',
                            $cliente ? $cliente->c_telefono : '',
                            $r->tramite_nombre ?: 'Trámite Notarial',
                            ucfirst($r->tramite_tipo),
                            $r->calificacion,
                            $r->atencion_personal,
                            $r->rapidez,
                            $r->recomendaria ? 'SÍ' : 'NO',
                            $r->comentario ?: 'Sin comentario escrito',
                        ], ';');
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
