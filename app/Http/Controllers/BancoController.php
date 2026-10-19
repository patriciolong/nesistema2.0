<?php

namespace App\Http\Controllers;

use App\Models\Banco;
use Illuminate\Http\Request;

class BancoController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('bancos.view')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso necesario para ver entidades bancarias.');
        }

        $query = Banco::withCount('tarjetas');

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('codigo', 'like', "%{$buscar}%")
                  ->orWhere('titular', 'like', "%{$buscar}%")
                  ->orWhere('tipo_cuenta', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('estado') && $request->estado !== 'todos') {
            $query->where('estado', $request->estado);
        }

        $stats = [
            'total' => Banco::count(),
            'activos' => Banco::where('estado', 'Activo')->count(),
            'inactivos' => Banco::where('estado', 'Inactivo')->count(),
        ];

        $bancos = $query->orderBy('nombre', 'asc')->paginate(15)->withQueryString();
        return view('bancos.index', compact('bancos', 'stats'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermission('bancos.create')) {
            abort(403, 'Acceso denegado: No tienes permiso para registrar nuevos bancos.');
        }

        return view('bancos.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('bancos.create')) {
            abort(403, 'Acceso denegado: No tienes permiso para registrar nuevos bancos.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'codigo' => 'nullable|string|max:50',
            'tipo_cuenta' => 'nullable|string|max:50',
            'titular' => 'nullable|string|max:100',
            'estado' => 'required|in:Activo,Inactivo',
        ]);

        Banco::create($validated);

        return redirect()->route('bancos.index')->with('success', 'Banco creado exitosamente.');
    }

    public function edit(Banco $banco)
    {
        if (!auth()->user()->hasPermission('bancos.edit')) {
            abort(403, 'Acceso denegado: No tienes permiso para editar bancos.');
        }

        return view('bancos.edit', compact('banco'));
    }

    public function update(Request $request, Banco $banco)
    {
        if (!auth()->user()->hasPermission('bancos.edit')) {
            abort(403, 'Acceso denegado: No tienes permiso para editar bancos.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'codigo' => 'nullable|string|max:50',
            'tipo_cuenta' => 'nullable|string|max:50',
            'titular' => 'nullable|string|max:100',
            'estado' => 'required|in:Activo,Inactivo',
        ]);

        $banco->update($validated);

        return redirect()->route('bancos.index')->with('success', 'Banco actualizado exitosamente.');
    }

    public function destroy(Banco $banco)
    {
        if (!auth()->user()->hasPermission('bancos.delete')) {
            abort(403, 'Acceso denegado: No tienes permiso para eliminar bancos.');
        }

        if ($banco->tarjetas()->count() > 0) {
            return redirect()->route('bancos.index')->with('error', 'No se puede eliminar el banco porque tiene tarjetas o datáfonos asociados.');
        }

        $banco->delete();

        return redirect()->route('bancos.index')->with('success', 'Banco eliminado correctamente.');
    }
}
