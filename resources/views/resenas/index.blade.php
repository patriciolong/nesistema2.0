<x-app-layout>
    <div class="py-8 bg-slate-900 min-h-screen text-slate-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Encabezado Principal y Acciones -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-800/80 p-6 rounded-3xl border border-slate-700/60 shadow-xl backdrop-blur-md">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-amber-400 shadow-lg shadow-amber-500/10">
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-black text-white tracking-tight">Reseñas y Encuestas de Clientes</h1>
                            <p class="text-xs text-slate-400 mt-0.5">Calificaciones de satisfacción y calidad de atención en trámites entregados.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('resenas.export_excel') }}" 
                       class="px-4 py-2.5 bg-emerald-600/20 hover:bg-emerald-600/30 border border-emerald-500/30 text-emerald-300 rounded-2xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Exportar Reporte Excel
                    </a>
                </div>
            </div>

            <!-- Notificación Flash -->
            @if(session('success'))
                <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-400 text-xs font-semibold flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Tarjetas de Métricas y Estadísticas Globales -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                
                <!-- 1. Calificación Promedio Global -->
                <div class="bg-gradient-to-br from-amber-500/10 via-slate-800 to-slate-800 p-5 rounded-3xl border border-amber-500/20 shadow-lg relative overflow-hidden">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-amber-400">Promedio General</span>
                            <div class="flex items-baseline gap-2 mt-2">
                                <span class="text-4xl font-black text-white">{{ number_format($promedioGeneral, 1) }}</span>
                                <span class="text-xs text-slate-400">/ 5.0</span>
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/20 flex items-center justify-center text-amber-400">
                            ⭐
                        </div>
                    </div>
                    <div class="flex items-center gap-1 mt-3 text-amber-400 text-sm">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= round($promedioGeneral))
                                ★
                            @else
                                <span class="text-slate-600">★</span>
                            @endif
                        @endfor
                        <span class="text-[11px] text-slate-400 ml-2">({{ $totalResenas }} opiniones)</span>
                    </div>
                </div>

                <!-- 2. Atención y Trato -->
                <div class="bg-slate-800/80 p-5 rounded-3xl border border-slate-700/60 shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-indigo-400">Trato y Atención</span>
                            <div class="flex items-baseline gap-2 mt-2">
                                <span class="text-3xl font-black text-white">{{ number_format($promedioAtencion, 1) }}</span>
                                <span class="text-xs text-slate-400">/ 5.0</span>
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                            🤝
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-3">Amabilidad y claridad del personal</p>
                </div>

                <!-- 3. Rapidez de Entrega -->
                <div class="bg-slate-800/80 p-5 rounded-3xl border border-slate-700/60 shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-sky-400">Rapidez y Puntualidad</span>
                            <div class="flex items-baseline gap-2 mt-2">
                                <span class="text-3xl font-black text-white">{{ number_format($promedioRapidez, 1) }}</span>
                                <span class="text-xs text-slate-400">/ 5.0</span>
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-sky-500/20 flex items-center justify-center text-sky-400">
                            ⚡
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-3">Tiempo de elaboración y entrega</p>
                </div>

                <!-- 4. Recomendación del Cliente -->
                <div class="bg-slate-800/80 p-5 rounded-3xl border border-slate-700/60 shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-400">Nivel de Recomendación</span>
                            <div class="flex items-baseline gap-2 mt-2">
                                <span class="text-3xl font-black text-white">{{ $porcentajeRecomiendan }}%</span>
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                            👍
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-3">Recomendarían nuestros servicios</p>
                </div>

            </div>

            <!-- Distribución de Estrellas -->
            <div class="bg-slate-800/80 p-6 rounded-3xl border border-slate-700/60 shadow-xl">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-4">Desglose de Calificaciones</h3>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    @for($s = 5; $s >= 1; $s--)
                        @php
                            $cnt = $distribucion[$s] ?? 0;
                            $pct = $totalResenas > 0 ? round(($cnt / $totalResenas) * 100) : 0;
                        @endphp
                        <div class="p-3 bg-slate-900/60 rounded-2xl border border-slate-700/40">
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <span class="font-bold text-amber-400">{{ $s }} ★</span>
                                <span class="text-slate-400 font-semibold">{{ $cnt }} ({{ $pct }}%)</span>
                            </div>
                            <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-400 rounded-full transition-all duration-500" style="width: {{ $pct }}%;"></div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Barra de Filtros y Búsqueda -->
            <form method="GET" action="{{ route('resenas.index') }}" class="bg-slate-800/80 p-5 rounded-3xl border border-slate-700/60 shadow-xl">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                    
                    <!-- Búsqueda libre -->
                    <div class="md:col-span-4">
                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Buscar por Cliente o Trámite</label>
                        <div class="relative">
                            <input type="text" name="buscar" value="{{ $filtros['buscar'] }}" placeholder="Nombre, cédula, trámite o comentario..." 
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-900 border border-slate-700 rounded-2xl text-xs font-semibold text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-0">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Filtro por Estrellas -->
                    <div class="md:col-span-3">
                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Calificación</label>
                        <select name="estrellas" class="w-full py-2.5 bg-slate-900 border border-slate-700 rounded-2xl text-xs font-semibold text-white focus:border-indigo-500 focus:ring-0">
                            <option value="todas" {{ $filtros['estrellas'] === 'todas' ? 'selected' : '' }}>Todas las estrellas</option>
                            <option value="5" {{ $filtros['estrellas'] === '5' ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (5 Estrellas)</option>
                            <option value="4" {{ $filtros['estrellas'] === '4' ? 'selected' : '' }}>⭐⭐⭐⭐ (4 Estrellas)</option>
                            <option value="3" {{ $filtros['estrellas'] === '3' ? 'selected' : '' }}>⭐⭐⭐ (3 Estrellas)</option>
                            <option value="2" {{ $filtros['estrellas'] === '2' ? 'selected' : '' }}>⭐⭐ (2 Estrellas)</option>
                            <option value="1" {{ $filtros['estrellas'] === '1' ? 'selected' : '' }}>⭐ (1 Estrella)</option>
                        </select>
                    </div>

                    <!-- Filtro por Recomendación -->
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Recomienda</label>
                        <select name="recomendaria" class="w-full py-2.5 bg-slate-900 border border-slate-700 rounded-2xl text-xs font-semibold text-white focus:border-indigo-500 focus:ring-0">
                            <option value="todas" {{ $filtros['recomendaria'] === 'todas' ? 'selected' : '' }}>Todos</option>
                            <option value="si" {{ $filtros['recomendaria'] === 'si' ? 'selected' : '' }}>👍 Sí</option>
                            <option value="no" {{ $filtros['recomendaria'] === 'no' ? 'selected' : '' }}>👎 No</option>
                        </select>
                    </div>

                    <!-- Filtro por Fecha -->
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Período</label>
                        <select name="fecha" class="w-full py-2.5 bg-slate-900 border border-slate-700 rounded-2xl text-xs font-semibold text-white focus:border-indigo-500 focus:ring-0">
                            <option value="todas" {{ $filtros['fecha'] === 'todas' ? 'selected' : '' }}>Todo el tiempo</option>
                            <option value="hoy" {{ $filtros['fecha'] === 'hoy' ? 'selected' : '' }}>Hoy</option>
                            <option value="semana" {{ $filtros['fecha'] === 'semana' ? 'selected' : '' }}>Esta semana</option>
                            <option value="mes" {{ $filtros['fecha'] === 'mes' ? 'selected' : '' }}>Este mes</option>
                        </select>
                    </div>

                    <!-- Botón Filtrar -->
                    <div class="md:col-span-1 flex gap-1">
                        <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl text-xs font-bold transition-all shadow-md flex items-center justify-center">
                            Filtrar
                        </button>
                    </div>

                </div>
            </form>

            <!-- Listado de Reseñas -->
            <div class="space-y-4">
                @forelse($resenas as $resena)
                    @php
                        $cliente = $resena->cliente;
                        $nombreCompleto = $cliente ? trim("{$cliente->c_nombre} {$cliente->c_apellido}") : 'Cliente';
                        $inicial = mb_substr($nombreCompleto, 0, 1, 'UTF-8');
                    @endphp

                    <div class="bg-slate-800/90 p-5 rounded-3xl border border-slate-700/60 shadow-xl hover:border-slate-600 transition-all flex flex-col md:flex-row md:items-start justify-between gap-5">
                        
                        <!-- Columna Izquierda: Información del Cliente -->
                        <div class="flex items-start gap-3.5 min-w-[240px]">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-white font-black text-lg shadow-md shrink-0">
                                {{ $inicial }}
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-white">{{ $nombreCompleto }}</h4>
                                <p class="text-[11px] text-slate-400">C.I: {{ $cliente ? $cliente->c_identificacion : 'S/N' }}</p>
                                @if($cliente && $cliente->c_telefono)
                                    <p class="text-[11px] text-indigo-400 mt-0.5">📞 {{ $cliente->c_telefono }}</p>
                                @endif
                                <span class="inline-block mt-2 px-2.5 py-0.5 bg-slate-900/80 border border-slate-700 rounded-full text-[10px] text-slate-400">
                                    📅 {{ $resena->fecha_resena ? $resena->fecha_resena->diffForHumans() : '' }}
                                </span>
                            </div>
                        </div>

                        <!-- Columna Central: Trámite, Calificación, Asesor y Comentario -->
                        <div class="flex-1 space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-3 py-1 bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 rounded-xl text-xs font-extrabold">
                                    📋 {{ $resena->tramite_nombre ?: 'Trámite Notarial' }}
                                </span>
                                <span class="px-2.5 py-1 bg-slate-900 border border-slate-700 text-slate-400 rounded-xl text-[10px] font-bold uppercase tracking-wider">
                                    {{ $resena->tramite_tipo }}
                                </span>
                            </div>

                            <!-- Ficha de Datos del Trámite: Quién atendió, oficina, fecha -->
                            @php
                                $det = $resena->tramite_detalle;
                            @endphp
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs bg-slate-900/80 p-3 rounded-2xl border border-slate-700/60">
                                <span class="flex items-center gap-1.5 text-indigo-300 font-extrabold">
                                    <span class="text-sm">👤</span>
                                    <span>Atendido por:</span>
                                    <span class="text-white underline decoration-indigo-400/50">{{ $det['asesor'] }}</span>
                                </span>
                                <span class="text-slate-600 hidden sm:inline">•</span>
                                <span class="flex items-center gap-1 text-slate-400">
                                    <span>🏢 Sede:</span>
                                    <span class="text-slate-200 font-semibold">{{ $det['oficina'] }}</span>
                                </span>
                                @if(!empty($det['fecha']))
                                    <span class="text-slate-600 hidden sm:inline">•</span>
                                    <span class="flex items-center gap-1 text-slate-400">
                                        <span>📅 Fecha:</span>
                                        <span class="text-slate-200 font-semibold">{{ date('d/m/Y', strtotime($det['fecha'])) }}</span>
                                    </span>
                                @endif
                                @if(!empty($det['costo']))
                                    <span class="text-slate-600 hidden sm:inline">•</span>
                                    <span class="flex items-center gap-1 text-emerald-400 font-bold">
                                        <span>💵</span>
                                        <span>${{ number_format($det['costo'], 2) }}</span>
                                    </span>
                                @endif
                                @foreach($det['detalles_extra'] as $lbl => $val)
                                    @if(!empty($val))
                                        <span class="text-slate-600 hidden sm:inline">•</span>
                                        <span class="text-slate-400">{{ $lbl }}: <strong class="text-slate-200">{{ $val }}</strong></span>
                                    @endif
                                @endforeach
                            </div>

                            <!-- Puntuaciones -->
                            <div class="flex flex-wrap items-center gap-3 text-xs">
                                <div class="flex items-center gap-1 bg-amber-500/10 px-3 py-1.5 rounded-xl border border-amber-500/20 text-amber-400 font-bold">
                                    <span>Calificación General:</span>
                                    <span>{{ str_repeat('⭐', $resena->calificacion) }}</span>
                                    <span class="text-white ml-1">({{ $resena->calificacion }}/5)</span>
                                </div>

                                <div class="flex items-center gap-1 bg-slate-900/80 px-2.5 py-1 rounded-xl border border-slate-700/60 text-slate-300">
                                    <span>Trato:</span>
                                    <span class="text-amber-400 font-bold">{{ $resena->atencion_personal }}/5 ★</span>
                                </div>

                                <div class="flex items-center gap-1 bg-slate-900/80 px-2.5 py-1 rounded-xl border border-slate-700/60 text-slate-300">
                                    <span>Rapidez:</span>
                                    <span class="text-amber-400 font-bold">{{ $resena->rapidez }}/5 ★</span>
                                </div>

                                <div>
                                    @if($resena->recomendaria)
                                        <span class="px-2.5 py-1 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl text-[11px] font-bold">
                                            👍 Recomienda el servicio
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-rose-500/10 border border-rose-500/30 text-rose-400 rounded-xl text-[11px] font-bold">
                                            👎 No recomienda
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Comentario / Testimonio -->
                            @if($resena->comentario)
                                <div class="p-3.5 bg-slate-900/90 rounded-2xl border border-slate-700/50 text-slate-200 text-xs italic relative">
                                    <span class="text-indigo-400 font-serif text-base mr-1">“</span>
                                    {{ $resena->comentario }}
                                    <span class="text-indigo-400 font-serif text-base ml-1">”</span>
                                </div>
                            @else
                                <p class="text-xs text-slate-500 italic">El cliente no dejó comentario escrito adicional.</p>
                            @endif
                        </div>

                        <!-- Columna Derecha: Acciones Administrativas e Ir al Trámite -->
                        <div class="flex flex-col items-stretch sm:items-end justify-center gap-2.5 shrink-0 min-w-[200px]">
                            <!-- Botón 1: Ir Directamente al Trámite que se reseñó -->
                            <a href="{{ $resena->tramite_detalle['url'] }}" 
                               target="_blank"
                               class="w-full px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 active:scale-95 text-white rounded-2xl text-xs font-black transition-all shadow-md shadow-indigo-600/30 flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                <span>Ver Trámite Completo</span>
                            </a>

                            <!-- Botón 2: Ver Historial de Trámites del Cliente -->
                            <a href="{{ route('clientes.tramites', $resena->id_cliente) }}" 
                               class="w-full px-3 py-2 bg-slate-900/90 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700/80 rounded-2xl text-[11px] font-bold transition-all flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Historial del Cliente</span>
                            </a>

                            <!-- Botón 3: Eliminar Reseña -->
                            <div class="flex justify-end w-full pt-1">
                                <form method="POST" action="{{ route('resenas.destroy', $resena->id) }}" onsubmit="return confirm('¿Estás seguro de eliminar esta reseña permanentemente?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 text-rose-400/80 hover:text-rose-300 hover:bg-rose-500/20 rounded-xl text-[11px] font-bold transition-all flex items-center gap-1" title="Eliminar reseña">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        <span>Eliminar</span>
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="p-12 text-center bg-slate-800/40 rounded-3xl border border-slate-700/40">
                        <div class="w-16 h-16 mx-auto rounded-3xl bg-slate-800 flex items-center justify-center text-slate-500 mb-3 text-2xl">
                            ⭐
                        </div>
                        <h3 class="text-sm font-bold text-slate-300">No se encontraron reseñas</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Cuando tus clientes califiquen sus trámites entregados desde la aplicación móvil o portal web, sus opiniones aparecerán en este módulo.</p>
                    </div>
                @endforelse
            </div>

            <!-- Paginación -->
            <div class="mt-6">
                {{ $resenas->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
