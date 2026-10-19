<?php

namespace App\Http\Controllers;

use App\Models\Tarjeta;
use App\Models\Banco;
use Illuminate\Http\Request;

class TarjetaController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('tarjetas.view')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso necesario para ver tarjetas o datáfonos.');
        }

        $query = Tarjeta::with('banco');

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('franquicia', 'like', "%{$buscar}%")
                  ->orWhere('ultimos_digitos', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('banco_id') && $request->banco_id !== 'todos') {
            $query->where('banco_id', $request->banco_id);
        }

        if ($request->filled('tipo') && $request->tipo !== 'todos') {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('estado') && $request->estado !== 'todos') {
            $query->where('estado', $request->estado);
        }

        $stats = [
            'total' => Tarjeta::count(),
            'activas' => Tarjeta::where('estado', 'Activo')->count(),
            'inactivas' => Tarjeta::where('estado', 'Inactivo')->count(),
        ];

        $bancos = Banco::orderBy('nombre')->get();
        $tarjetas = $query->orderBy('nombre', 'asc')->paginate(15)->withQueryString();

        return view('tarjetas.index', compact('tarjetas', 'bancos', 'stats'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermission('tarjetas.create')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso necesario para registrar tarjetas.');
        }

        $bancos = Banco::activos()->orderBy('nombre')->get();
        return view('tarjetas.create', compact('bancos'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('tarjetas.create')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso necesario para registrar tarjetas.');
        }

        $validated = $request->validate([
            'banco_id' => 'required|exists:bancos,id',
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|string|max:50',
            'franquicia' => 'required|string|max:50',
            'ultimos_digitos' => 'nullable|string|max:10',
            'estado' => 'required|string|max:20',
        ]);

        Tarjeta::create($validated);

        return redirect()->route('tarjetas.index')->with('success', 'Tarjeta o datáfono registrado correctamente.');
    }

    public function edit(Tarjeta $tarjeta)
    {
        if (!auth()->user()->hasPermission('tarjetas.edit')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso necesario para editar tarjetas.');
        }

        $bancos = Banco::activos()->orderBy('nombre')->get();
        return view('tarjetas.edit', compact('tarjeta', 'bancos'));
    }

    public function update(Request $request, Tarjeta $tarjeta)
    {
        if (!auth()->user()->hasPermission('tarjetas.edit')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso necesario para editar tarjetas.');
        }

        $validated = $request->validate([
            'banco_id' => 'required|exists:bancos,id',
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|string|max:50',
            'franquicia' => 'required|string|max:50',
            'ultimos_digitos' => 'nullable|string|max:10',
            'estado' => 'required|string|max:20',
        ]);

        $tarjeta->update($validated);

        return redirect()->route('tarjetas.index')->with('success', 'Tarjeta actualizada correctamente.');
    }

    public function destroy(Tarjeta $tarjeta)
    {
        if (!auth()->user()->hasPermission('tarjetas.delete')) {
            abort(403, 'Acceso denegado: No cuentas con el permiso necesario para eliminar tarjetas.');
        }

        if ($tarjeta->movimientos()->count() > 0 || $tarjeta->pagos()->count() > 0) {
            return redirect()->route('tarjetas.index')->with('error', 'No se puede eliminar la tarjeta porque tiene transacciones registradas.');
        }

        $tarjeta->delete();

        return redirect()->route('tarjetas.index')->with('success', 'Tarjeta eliminada exitosamente.');
    }
}
