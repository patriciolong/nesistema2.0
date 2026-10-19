<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Abogado;
use App\Models\Cliente;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * Muestra la bandeja de entrada de solicitudes web y consultas de abogados.
     */
    public function index(Request $request)
    {
        $query = Lead::query();

        // Filtro por tipo/origen
        if ($request->filled('tipo')) {
            if ($request->tipo === 'abogados') {
                $query->where(function($q) {
                    $q->where('servicio', 'like', '%abogado%')
                      ->orWhere('origen', 'directorio_abogados');
                });
            } elseif ($request->tipo === 'tramites') {
                $query->where('servicio', 'not like', '%abogado%')
                      ->where('origen', '!=', 'directorio_abogados');
            }
        }

        // Filtro por estado sincronizado / atendido
        if ($request->filled('estado')) {
            if ($request->estado === 'pendientes') {
                $query->where('synced_to_nesistema', false);
            } elseif ($request->estado === 'atendidos') {
                $query->where('synced_to_nesistema', true);
            }
        }

        // Búsqueda por nombre, teléfono, servicio o mensaje
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%")
                  ->orWhere('servicio', 'like', "%{$search}%")
                  ->orWhere('mensaje', 'like', "%{$search}%")
                  ->orWhere('ciudad', 'like', "%{$search}%");
            });
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $totalLeads = Lead::count();
        $totalAbogadosLeads = Lead::where(function($q) {
            $q->where('servicio', 'like', '%abogado%')
              ->orWhere('origen', 'directorio_abogados');
        })->count();
        $totalPendientes = Lead::where('synced_to_nesistema', false)->count();
        $totalAtendidos = Lead::where('synced_to_nesistema', true)->count();

        return view('leads.index', compact(
            'leads',
            'totalLeads',
            'totalAbogadosLeads',
            'totalPendientes',
            'totalAtendidos'
        ));
    }

    /**
     * Alternar estado de atención (Pendiente / Atendido).
     */
    public function toggleEstado(Lead $lead)
    {
        $lead->synced_to_nesistema = !$lead->synced_to_nesistema;
        $lead->save();

        $statusText = $lead->synced_to_nesistema ? 'marcada como Atendida' : 'marcada como Pendiente';
        return back()->with('success', "La solicitud de {$lead->nombre_completo} ha sido {$statusText}.");
    }

    /**
     * Eliminar una solicitud.
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();
        return back()->with('success', 'La solicitud ha sido eliminada del sistema.');
    }
}
