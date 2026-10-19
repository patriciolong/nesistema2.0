<?php

namespace App\Http\Controllers;

use App\Models\Oficina;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OficinaController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('oficinas.view')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso necesario para ver oficinas.');
        }

        $query = Oficina::query();

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('direccion', 'like', "%{$buscar}%")
                  ->orWhere('telefono', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'todos') {
            $query->where('status', $request->status);
        }

        $stats = [
            'total' => Oficina::count(),
            'activas' => Oficina::where('status', 'Activa')->count(),
            'inactivas' => Oficina::where('status', 'Inactiva')->count(),
        ];

        $oficinas = $query->orderBy('nombre')->paginate(15)->withQueryString();
        return view('oficinas.index', compact('oficinas', 'stats'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermission('oficinas.create')) abort(403, 'Acceso denegado.');
        
        return view('oficinas.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('oficinas.create')) abort(403, 'Acceso denegado.');

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:oficinas'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:Activa,Inactiva'],
        ]);

        Oficina::create($validated);

        return redirect()->route('oficinas.index')->with('success', 'Oficina creada exitosamente.');
    }

    public function edit(Oficina $oficina)
    {
        if (!auth()->user()->hasPermission('oficinas.edit')) abort(403, 'Acceso denegado.');

        return view('oficinas.edit', compact('oficina'));
    }

    public function update(Request $request, Oficina $oficina)
    {
        if (!auth()->user()->hasPermission('oficinas.edit')) abort(403, 'Acceso denegado.');

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('oficinas')->ignore($oficina->id)],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:Activa,Inactiva'],
        ]);

        $oficina->update($validated);

        return redirect()->route('oficinas.index')->with('success', 'Oficina actualizada exitosamente.');
    }

    public function destroy(Oficina $oficina)
    {
        if (!auth()->user()->hasPermission('oficinas.delete')) abort(403, 'Acceso denegado.');

        $oficina->delete();

        return redirect()->route('oficinas.index')->with('success', 'Oficina eliminada exitosamente.');
    }
}
