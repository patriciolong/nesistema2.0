<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermisoController extends Controller
{
    /**
     * Display a listing of users and their permission statuses.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser->hasPermission('permisos.view') && !$currentUser->hasPermission('permisos.manage') && !$currentUser->hasPermission('permisos.edit')) {
            abort(403, 'Acceso restringido: No tienes autorización para gestionar permisos de usuarios.');
        }

        $query = User::query();

        if ($request->filled('buscar')) {
            $search = $request->input('buscar');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('office', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role') && $request->role !== 'todos') {
            $query->where('role', $request->role);
        }

        if ($request->filled('office') && $request->office !== 'todas') {
            $query->where('office', $request->office);
        }

        if ($request->filled('status') && $request->status !== 'todos') {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        $roles = ['Administrador', 'Supervisor', 'Asesor', 'Empleado'];
        $oficinas = \App\Models\Oficina::orderBy('nombre')->get();
        $availableGroups = User::AVAILABLE_PERMISSIONS;

        // Calculate total available permissions count
        $totalPermissionsCount = 0;
        foreach ($availableGroups as $group) {
            $totalPermissionsCount += count($group['permisos']);
        }

        return view('permisos.index', compact('users', 'roles', 'oficinas', 'availableGroups', 'totalPermissionsCount'));
    }

    /**
     * Show the form for editing the specified user's permissions.
     */
    public function edit(User $user)
    {
        $currentUser = Auth::user();
        if (!$currentUser->hasPermission('permisos.edit') && !$currentUser->hasPermission('permisos.manage')) {
            abort(403, 'Acceso restringido: No tienes autorización para gestionar permisos de usuarios.');
        }

        $availableGroups = User::AVAILABLE_PERMISSIONS;
        $effectivePermissions = $user->getEffectivePermissions();
        $isCustom = is_array($user->permissions);

        // Calculate total available permissions count
        $totalPermissionsCount = 0;
        foreach ($availableGroups as $group) {
            $totalPermissionsCount += count($group['permisos']);
        }

        return view('permisos.edit', compact('user', 'availableGroups', 'effectivePermissions', 'isCustom', 'totalPermissionsCount'));
    }

    /**
     * Update the specified user's permissions in storage.
     */
    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();
        if (!$currentUser->hasPermission('permisos.edit') && !$currentUser->hasPermission('permisos.manage')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No tienes autorización para gestionar permisos.'], 403);
            }
            abort(403, 'Acceso restringido: No tienes autorización para gestionar permisos de usuarios.');
        }

        // Action: Reset to role default (only when explicitly requested via action=reset_to_role)
        if ($request->input('action') === 'reset_to_role') {
            $user->permissions = null;
            $user->save();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => "Permisos restablecidos al rol {$user->role}."]);
            }

            return redirect()->route('permisos.index')
                ->with('success', "Permisos de '{$user->name}' restablecidos a los valores predeterminados del rol ({$user->role}).");
        }

        // Normal Save: Save the selected permissions array
        $selectedPermissions = $request->input('permissions', []);
        if (!is_array($selectedPermissions)) {
            $selectedPermissions = [];
        }

        // Validate that each submitted permission exists in our master catalog
        $validKeys = [];
        foreach (User::AVAILABLE_PERMISSIONS as $group) {
            foreach (array_keys($group['permisos']) as $key) {
                $validKeys[] = $key;
            }
        }

        $cleanPermissions = array_values(array_intersect($selectedPermissions, $validKeys));

        // Always save as array (even empty array [] if all unchecked)
        $user->permissions = $cleanPermissions;
        $user->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Permisos guardados correctamente para '{$user->name}' (" . count($cleanPermissions) . " activos).",
                'count' => count($cleanPermissions)
            ]);
        }

        if ($request->input('return_to') === 'index') {
            return redirect()->route('permisos.index')
                ->with('success', "¡Permisos guardados correctamente para '{$user->name}'! (" . count($cleanPermissions) . " de " . count($validKeys) . " permisos activos).");
        }

        return redirect()->route('permisos.edit', $user)
            ->with('success', "¡Permisos guardados correctamente para '{$user->name}'! (" . count($cleanPermissions) . " de " . count($validKeys) . " permisos activos).");
    }

    /**
     * Apply a quick permission preset template to a user.
     */
    public function applyPreset(Request $request, User $user)
    {
        $currentUser = Auth::user();
        if (!$currentUser->hasPermission('permisos.edit') && !$currentUser->hasPermission('permisos.manage')) {
            abort(403, 'Acceso restringido: No tienes autorización para gestionar permisos.');
        }

        $preset = $request->input('preset');

        switch ($preset) {
            case 'admin':
                $user->permissions = User::getRoleDefaultPermissions('Administrador');
                break;
            case 'supervisor':
                $user->permissions = User::getRoleDefaultPermissions('Supervisor');
                break;
            case 'cajero':
            case 'empleado':
                $user->permissions = User::getRoleDefaultPermissions('Empleado');
                break;
            case 'solo_lectura':
                $user->permissions = [
                    'dashboard.view',
                    'clientes.view',
                    'cartera.view',
                    'cajas.view',
                    'tramites_global.view',
                    'poderes.view',
                    'divorcios.view',
                    'impuestos.view',
                    'tramites_varios.view',
                    'tramites_personalizados.view',
                    'plantillas.view',
                    'membretes.view',
                    'bancos.view',
                    'tarjetas.view',
                    'oficinas.view',
                    'tipo_tramites.view',
                    'users.view',
                    'permisos.view',
                    'reportes.cajas',
                    'reportes.desempeno',
                    'reportes.cartera',
                    'reportes.control_precios',
                ];
                break;
            case 'reset':
                $user->permissions = null;
                break;
            default:
                return redirect()->back()->with('error', 'Plantilla de permisos no válida.');
        }

        $user->save();

        return redirect()->back()->with('success', "Plantilla '{$preset}' aplicada correctamente a {$user->name}.");
    }
}
