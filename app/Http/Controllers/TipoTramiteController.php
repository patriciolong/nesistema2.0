<?php

namespace App\Http\Controllers;

use App\Models\TipoTramite;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class TipoTramiteController extends Controller
{
    /**
     * Display a listing of custom procedure types with filters.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('tipo_tramites.view')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso necesario para ver tipos de trámites.');
        }

        $query = TipoTramite::query();

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('estado') && $request->estado !== 'todos') {
            if ($request->estado === 'activo') {
                $query->where('activo', true);
            } elseif ($request->estado === 'inactivo') {
                $query->where('activo', false);
            }
        }

        $stats = [
            'total' => TipoTramite::count(),
            'activos' => TipoTramite::where('activo', true)->count(),
            'inactivos' => TipoTramite::where('activo', false)->count(),
        ];

        $tipoTramites = $query->orderBy('orden', 'asc')->paginate(12)->withQueryString();
        return view('tipo_tramites.index', compact('tipoTramites', 'stats'));
    }

    /**
     * Show the form builder to create a new procedure type.
     */
    public function create()
    {
        if (!auth()->user()->hasPermission('tipo_tramites.create')) abort(403, 'Acceso denegado.');

        return view('tipo_tramites.create');
    }

    /**
     * Store a newly created procedure type in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('tipo_tramites.create')) abort(403, 'Acceso denegado.');

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'color_gradient' => 'required|string|max:150',
            'icono' => 'required|string|max:50',
            'campos' => 'nullable|json',
        ]);

        $slug = Str::slug($validated['nombre']);
        // Ensure unique slug
        $count = TipoTramite::where('slug', 'like', "{$slug}%")->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $camposArray = [];
        if (!empty($validated['campos'])) {
            $camposArray = json_decode($validated['campos'], true) ?? [];
        }

        $maxOrden = TipoTramite::max('orden') ?? 0;

        TipoTramite::create([
            'nombre' => $validated['nombre'],
            'slug' => $slug,
            'descripcion' => $validated['descripcion'],
            'color_gradient' => $validated['color_gradient'],
            'icono' => $validated['icono'],
            'campos' => $camposArray,
            'orden' => $maxOrden + 1,
            'activo' => true,
            'created_by' => Auth::user()->name ?? 'Sistema',
        ]);

        return redirect()->route('tipo-tramites.index')->with('success', '¡Nuevo tipo de trámite y formulario creados con éxito!');
    }

    /**
     * Show the form for editing the procedure type.
     */
    public function edit(TipoTramite $tipoTramite)
    {
        if (!auth()->user()->hasPermission('tipo_tramites.edit')) abort(403, 'Acceso denegado.');

        return view('tipo_tramites.edit', compact('tipoTramite'));
    }

    /**
     * Update the specified procedure type in storage.
     */
    public function update(Request $request, TipoTramite $tipoTramite)
    {
        if (!auth()->user()->hasPermission('tipo_tramites.edit')) abort(403, 'Acceso denegado.');

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'color_gradient' => 'required|string|max:150',
            'icono' => 'required|string|max:50',
            'campos' => 'nullable|json',
            'activo' => 'nullable|boolean',
        ]);

        $camposArray = [];
        if (!empty($validated['campos'])) {
            $camposArray = json_decode($validated['campos'], true) ?? [];
        }

        $tipoTramite->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'color_gradient' => $validated['color_gradient'],
            'icono' => $validated['icono'],
            'campos' => $camposArray,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('tipo-tramites.index')->with('success', 'Tipo de trámite actualizado correctamente.');
    }

    /**
     * Remove the specified procedure type from storage.
     */
    public function destroy(TipoTramite $tipoTramite)
    {
        if (!auth()->user()->hasPermission('tipo_tramites.delete')) abort(403, 'Acceso denegado.');

        $tipoTramite->delete();
        return redirect()->route('tipo-tramites.index')->with('success', 'Tipo de trámite eliminado correctamente.');
    }
}
