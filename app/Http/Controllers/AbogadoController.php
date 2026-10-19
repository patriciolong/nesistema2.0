<?php

namespace App\Http\Controllers;

use App\Models\Abogado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AbogadoController extends Controller
{
    /**
     * Muestra el listado de abogados en el panel administrativo.
     */
    public function index(Request $request)
    {
        $query = Abogado::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhere('especialidad', 'like', "%{$search}%")
                  ->orWhere('ciudad', 'like', "%{$search}%")
                  ->orWhere('matricula_profesional', 'like', "%{$search}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('ciudad')) {
            $query->where('ciudad', $request->ciudad);
        }

        if ($request->filled('especialidad')) {
            $query->where('especialidad', $request->especialidad);
        }

        $abogados = $query->orderBy('destacado', 'desc')
                          ->orderBy('nombres', 'asc')
                          ->paginate(15)
                          ->withQueryString();

        $ciudades = Abogado::distinct()->whereNotNull('ciudad')->pluck('ciudad');
        $especialidades = Abogado::distinct()->whereNotNull('especialidad')->pluck('especialidad');
        $totalActivos = Abogado::where('estado', 'activo')->count();
        $totalInactivos = Abogado::where('estado', 'inactivo')->count();

        return view('abogados.index', compact('abogados', 'ciudades', 'especialidades', 'totalActivos', 'totalInactivos'));
    }

    /**
     * Formulario para registrar un nuevo abogado.
     */
    public function create()
    {
        return view('abogados.create');
    }

    /**
     * Almacena un nuevo abogado en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:150',
            'apellidos' => 'required|string|max:150',
            'especialidad' => 'required|string|max:200',
            'colegio_abogados' => 'nullable|string|max:200',
            'matricula_profesional' => 'nullable|string|max:100',
            'ciudad' => 'required|string|max:100',
            'pais' => 'required|string|max:100',
            'experiencia_anios' => 'required|integer|min:0|max:60',
            'idiomas' => 'nullable|string|max:150',
            'biografia_corta' => 'nullable|string|max:1500',
            'telefono_privado' => 'nullable|string|max:50',
            'email_privado' => 'nullable|email|max:150',
            'estado' => 'required|in:activo,inactivo',
            'destacado' => 'nullable|boolean',
            'foto_file' => 'nullable|image|max:5120',
        ]);

        $validated['destacado'] = $request->has('destacado');
        $validated['idiomas'] = $validated['idiomas'] ?? 'Español, Inglés';

        if ($request->hasFile('foto_file')) {
            $file = $request->file('foto_file');
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $relativePath = 'abogados/' . $filename;

            // 1. Guardar en storage/app/public
            $file->storeAs('abogados', $filename, 'public');

            // 2. Guardar físicamente en public/storage/abogados (por si no hay symlink en Hostinger)
            $publicStorageDir = public_path('storage/abogados');
            if (!is_dir($publicStorageDir)) {
                @mkdir($publicStorageDir, 0777, true);
            }
            @copy($file->getRealPath(), $publicStorageDir . '/' . $filename);

            // 3. Guardar físicamente en assets/images/team
            $teamDir = public_path('assets/images/team');
            if (!is_dir($teamDir)) {
                @mkdir($teamDir, 0777, true);
            }
            @copy($file->getRealPath(), $teamDir . '/' . $filename);

            // 4. Copiar a dominios hermanos en el mismo hosting si existen
            $siblingDirs = [
                base_path('../notariaecuador.com/public/storage/abogados'),
                base_path('../notariaecuador.com/public_html/storage/abogados'),
                base_path('../notariaecuador.com/public/assets/images/team'),
                base_path('../PAGINA WEB NOTARIA/public/storage/abogados'),
                base_path('../PAGINA WEB NOTARIA/public/assets/images/team'),
            ];
            foreach ($siblingDirs as $sDir) {
                if (is_dir(dirname($sDir))) {
                    @mkdir($sDir, 0777, true);
                    @copy($file->getRealPath(), $sDir . '/' . $filename);
                }
            }

            $validated['foto'] = $relativePath;
        }

        Abogado::create($validated);

        return redirect()->route('abogados.index')
                         ->with('success', 'Abogado registrado correctamente y sincronizado con el directorio web.');
    }

    /**
     * Muestra los detalles de un abogado (administración).
     */
    public function show(Abogado $abogado)
    {
        return view('abogados.show', compact('abogado'));
    }

    /**
     * Formulario para editar un abogado existente.
     */
    public function edit(Abogado $abogado)
    {
        return view('abogados.edit', compact('abogado'));
    }

    /**
     * Actualiza la información del abogado.
     */
    public function update(Request $request, Abogado $abogado)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:150',
            'apellidos' => 'required|string|max:150',
            'especialidad' => 'required|string|max:200',
            'colegio_abogados' => 'nullable|string|max:200',
            'matricula_profesional' => 'nullable|string|max:100',
            'ciudad' => 'required|string|max:100',
            'pais' => 'required|string|max:100',
            'experiencia_anios' => 'required|integer|min:0|max:60',
            'idiomas' => 'nullable|string|max:150',
            'biografia_corta' => 'nullable|string|max:1500',
            'telefono_privado' => 'nullable|string|max:50',
            'email_privado' => 'nullable|email|max:150',
            'estado' => 'required|in:activo,inactivo',
            'destacado' => 'nullable|boolean',
            'foto_file' => 'nullable|image|max:5120',
        ]);

        $validated['destacado'] = $request->has('destacado');

        if ($request->hasFile('foto_file')) {
            // Eliminar foto anterior de storage si existe
            if (!empty($abogado->foto) && Storage::disk('public')->exists($abogado->foto)) {
                Storage::disk('public')->delete($abogado->foto);
            }

            $file = $request->file('foto_file');
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $relativePath = 'abogados/' . $filename;

            // 1. Guardar en storage/app/public
            $file->storeAs('abogados', $filename, 'public');

            // 2. Guardar físicamente en public/storage/abogados (evita depender de symlink en Hostinger)
            $publicStorageDir = public_path('storage/abogados');
            if (!is_dir($publicStorageDir)) {
                @mkdir($publicStorageDir, 0777, true);
            }
            @copy($file->getRealPath(), $publicStorageDir . '/' . $filename);

            // 3. Guardar físicamente en assets/images/team
            $teamDir = public_path('assets/images/team');
            if (!is_dir($teamDir)) {
                @mkdir($teamDir, 0777, true);
            }
            @copy($file->getRealPath(), $teamDir . '/' . $filename);

            // 4. Copiar a dominios hermanos en el mismo hosting si existen
            $siblingDirs = [
                base_path('../notariaecuador.com/public/storage/abogados'),
                base_path('../notariaecuador.com/public_html/storage/abogados'),
                base_path('../notariaecuador.com/public/assets/images/team'),
                base_path('../PAGINA WEB NOTARIA/public/storage/abogados'),
                base_path('../PAGINA WEB NOTARIA/public/assets/images/team'),
            ];
            foreach ($siblingDirs as $sDir) {
                if (is_dir(dirname($sDir))) {
                    @mkdir($sDir, 0777, true);
                    @copy($file->getRealPath(), $sDir . '/' . $filename);
                }
            }

            $validated['foto'] = $relativePath;
        }

        $abogado->update($validated);

        return redirect()->route('abogados.index')
                         ->with('success', 'Información del abogado actualizada con éxito.');
    }

    /**
     * Elimina un abogado del sistema.
     */
    public function destroy(Abogado $abogado)
    {
        if (!empty($abogado->foto) && Storage::disk('public')->exists($abogado->foto)) {
            Storage::disk('public')->delete($abogado->foto);
        }

        $abogado->delete();

        return redirect()->route('abogados.index')
                         ->with('success', 'El abogado ha sido eliminado del directorio.');
    }

    /**
     * Alternar estado Activo / Inactivo con un clic.
     */
    public function toggleEstado(Abogado $abogado)
    {
        $abogado->estado = ($abogado->estado === 'activo') ? 'inactivo' : 'activo';
        $abogado->save();

        return back()->with('success', "El estado de {$abogado->nombre_completo} ha cambiado a: {$abogado->estado}.");
    }

    /**
     * API Pública para consumir desde la Página Web Notaría u otros portales autorizados.
     * Retorna únicamente información pública protegida.
     */
    public function apiPublicList(Request $request)
    {
        $query = Abogado::activo();

        if ($request->filled('ciudad')) {
            $query->where('ciudad', $request->ciudad);
        }

        if ($request->filled('especialidad')) {
            $query->where('especialidad', $request->especialidad);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhere('especialidad', 'like', "%{$search}%")
                  ->orWhere('ciudad', 'like', "%{$search}%");
            });
        }

        $abogados = $query->orderBy('destacado', 'desc')
                          ->orderBy('nombres', 'asc')
                          ->get()
                          ->map(fn($a) => $a->toPublicArray());

        return response()->json([
            'status' => 'success',
            'count' => $abogados->count(),
            'data' => $abogados
        ]);
    }

    /**
     * API Pública para obtener un abogado por ID con datos sanitizados.
     */
    public function apiPublicShow($id)
    {
        $abogado = Abogado::activo()->find($id);

        if (!$abogado) {
            return response()->json([
                'status' => 'error',
                'message' => 'Abogado no encontrado o no disponible en el directorio público.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $abogado->toPublicArray()
        ]);
    }
}
