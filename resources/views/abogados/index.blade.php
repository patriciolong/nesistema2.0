<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-2xl text-slate-800 tracking-tight leading-none">
                        {{ __('Red de Abogados y Asesores Legales') }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Gestión de profesionales asociados, perfiles públicos y sincronización con el directorio web</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @php
                    $solicitudesAbogadosCount = \App\Models\Lead::where('servicio', 'like', '%abogado%')->orWhere('origen', 'directorio_abogados')->count();
                @endphp
                <a href="{{ route('leads.index', ['tipo' => 'abogados']) }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 rounded-xl text-xs font-bold transition-all shadow-sm">
                    <span>📥</span> Solicitudes de Abogados
                    @if($solicitudesAbogadosCount > 0)
                        <span class="px-2 py-0.5 bg-amber-500 text-white rounded-full text-[10px] font-black">
                            {{ $solicitudesAbogadosCount }}
                        </span>
                    @endif
                </a>

                <a href="{{ env('PUBLIC_WEB_URL', 'https://notariaecuador.com') }}/abogados" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all">
                    🌐 Ver Directorio Web
                </a>

                <a href="{{ route('abogados.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-200 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    + Nuevo Abogado
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="w-full max-w-[1700px] mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 text-sm font-semibold shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Tarjetas Estadísticas -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total en Registro</span>
                        <h4 class="text-2xl font-black text-slate-900 mt-1">{{ $totalActivos + $totalInactivos }}</h4>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-emerald-100 shadow-sm flex items-center justify-between bg-emerald-50/20">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Activos en la Web</span>
                        <h4 class="text-2xl font-black text-emerald-700 mt-1">{{ $totalActivos }}</h4>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Inactivos / Ocultos</span>
                        <h4 class="text-2xl font-black text-slate-600 mt-1">{{ $totalInactivos }}</h4>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Filtros y Búsqueda -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <form method="GET" action="{{ route('abogados.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="sm:col-span-2">
                        <label class="text-xs font-bold text-slate-600 uppercase mb-1 block">Buscar por Nombre, Especialidad o Matrícula</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ej: Dr. Carlos Morales, Derecho Civil, 01-2018..." class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-600 uppercase mb-1 block">Ciudad / Sede</label>
                        <select name="ciudad" class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todas las ciudades</option>
                            @foreach($ciudades as $c)
                                <option value="{{ $c }}" {{ request('ciudad') == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-600 uppercase mb-1 block">Estado</label>
                        <select name="estado" class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos los estados</option>
                            <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo (Visible en Web)</option>
                            <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo (Oculto)</option>
                        </select>
                    </div>

                    <div class="sm:col-span-4 flex justify-end gap-2 pt-2 border-t border-slate-100">
                        @if(request()->hasAny(['search', 'ciudad', 'estado', 'especialidad']))
                            <a href="{{ route('abogados.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all">Limpiar Filtros</a>
                        @endif
                        <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition-all">Filtrar</button>
                    </div>
                </form>
            </div>

            <!-- Tabla de Abogados -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50/80 text-xs uppercase font-extrabold text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-4">Profesional</th>
                                <th class="px-5 py-4">Especialidad & Matrícula</th>
                                <th class="px-5 py-4">Ubicación & Experiencia</th>
                                <th class="px-5 py-4">Contacto Interno</th>
                                <th class="px-5 py-4 text-center">Estado Web</th>
                                <th class="px-5 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($abogados as $abogado)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <!-- Profesional con Foto -->
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
                                                @if($abogado->foto)
                                                    <img src="{{ $abogado->foto_url }}" alt="{{ $abogado->nombre_completo }}" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='{{ asset('assets/images/team/placeholder-avatar.png') }}';">
                                                @else
                                                    <span class="font-extrabold text-indigo-600 text-base">{{ substr($abogado->nombres, 0, 1) }}{{ substr($abogado->apellidos, 0, 1) }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                    <span>{{ $abogado->nombre_completo }}</span>
                                                    @if($abogado->destacado)
                                                        <span class="px-1.5 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-extrabold rounded-md">★ Destacado</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-slate-400 mt-0.5">{{ $abogado->idiomas }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Especialidad & Matrícula -->
                                    <td class="px-5 py-4">
                                        <span class="inline-block px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-lg border border-indigo-100">
                                            {{ $abogado->especialidad }}
                                        </span>
                                        <div class="text-xs text-slate-500 mt-1">
                                            {{ $abogado->matricula_profesional ?? 'Sin matrícula registrada' }}
                                        </div>
                                        @if($abogado->colegio_abogados)
                                            <div class="text-[11px] text-slate-400">{{ $abogado->colegio_abogados }}</div>
                                        @endif
                                    </td>

                                    <!-- Ubicación & Experiencia -->
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-800">{{ $abogado->ciudad }}, {{ $abogado->pais }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $abogado->experiencia_anios }} años de experiencia</div>
                                    </td>

                                    <!-- Contacto Interno (Privado) -->
                                    <td class="px-5 py-4">
                                        <div class="text-xs font-medium text-slate-700">📞 {{ $abogado->telefono_privado ?? 'No especificado' }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5">✉️ {{ $abogado->email_privado ?? 'No especificado' }}</div>
                                    </td>

                                    <!-- Estado Web con Toggle -->
                                    <td class="px-5 py-4 text-center">
                                        <form method="POST" action="{{ route('abogados.toggle_estado', $abogado) }}" class="inline-block">
                                            @csrf
                                            <button type="submit" title="Clic para alternar estado" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all {{ $abogado->estado === 'activo' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $abogado->estado === 'activo' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                                {{ ucfirst($abogado->estado) }}
                                            </button>
                                        </form>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('abogados.edit', $abogado) }}" class="p-2 text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all" title="Editar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                            <form method="POST" action="{{ route('abogados.destroy', $abogado) }}" onsubmit="return confirm('¿Seguro que deseas eliminar este abogado del directorio?')" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" title="Eliminar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                        <div class="max-w-sm mx-auto">
                                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                            <p class="font-bold text-slate-700">No se encontraron abogados</p>
                                            <p class="text-xs text-slate-500 mt-1">Registra nuevos profesionales o ajusta los criterios de búsqueda.</p>
                                            <a href="{{ route('abogados.create') }}" class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl shadow-md">+ Registrar Primer Abogado</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($abogados->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $abogados->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
