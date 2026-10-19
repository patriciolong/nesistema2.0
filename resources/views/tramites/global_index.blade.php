<x-app-layout>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Notificaciones de Éxito / Error -->
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm animate-fade-in">
                <div class="flex items-center gap-3">
                    <span class="text-xl">✅</span>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-lg font-bold">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm animate-fade-in">
                <div class="flex items-center gap-3">
                    <span class="text-xl">⚠️</span>
                    <span class="text-sm font-bold">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 text-lg font-bold">✕</button>
            </div>
        @endif

        <!-- Encabezado Principal & Acciones -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-sky-500 flex items-center justify-center text-white text-2xl shadow-md shadow-indigo-500/20">
                        📑
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Historial Global de Trámites</h1>
                        <p class="text-xs text-slate-500 mt-0.5">Explorador centralizado de todos los trámites notariales, estados y recaudaciones en tiempo real</p>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('tramites.realizados.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-emerald-600/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Exportar a Excel / CSV</span>
                </a>

                <a href="{{ route('clientes.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-indigo-600/20 transition-all">
                    <span>➕ Nuevo Trámite (por Cliente)</span>
                </a>

                <a href="{{ route('reportes.control_precios.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold transition-all" title="Auditoría de Desviaciones de Tarifas">
                    <span>⚖️ Control de Precios (±5%)</span>
                </a>
            </div>
        </div>

        <!-- 4 Tarjetas de Métricas Clave (KPIs) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- 1. Total Trámites -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-700">Total Trámites</p>
                        <h3 class="text-2xl font-black text-slate-900 mt-1">{{ number_format($totalGeneral) }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                        📂
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2 text-xs font-bold">
                    <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                        Hoy: {{ $totalHoy }}
                    </span>
                    <span class="text-slate-600">Este Mes: {{ $totalMes }}</span>
                </div>
            </div>

            <!-- 2. Monto Facturado Total -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-700">Total Facturado</p>
                        <h3 class="text-2xl font-black text-slate-900 mt-1">${{ number_format($totalMontoFacturado, 2) }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                        💵
                    </div>
                </div>
                <p class="text-xs text-slate-600 mt-3 font-bold">
                    Volumen económico de trámites generados
                </p>
            </div>

            <!-- 3. Total Recaudado / Cobrado -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-700">Recaudado / En Caja</p>
                        <h3 class="text-2xl font-black text-emerald-800 mt-1">${{ number_format($totalMontoRecaudado, 2) }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg">
                        💰
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2 text-xs font-bold text-slate-700">
                    <span class="text-rose-700 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200">
                        Por Cobrar: ${{ number_format($totalMontoSaldo, 2) }}
                    </span>
                </div>
            </div>

            <!-- 4. Eficiencia / Trámites Completados -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-700">Listos para entregar / Entregados</p>
                        <h3 class="text-2xl font-black text-indigo-700 mt-1">{{ number_format($totalListos) }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                        ✨
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1.5 text-xs font-bold">
                    <span class="text-amber-800 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                        En Proceso: {{ $totalEnProceso }}
                    </span>
                    <span class="text-blue-800 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200">
                        Revisión: {{ $totalEnRevision }}
                    </span>
                </div>
            </div>

        </div>

        <!-- Filtros Rápidos de Fecha (Presets) -->
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wider mr-1">Rango Rápido:</span>
            <a href="{{ route('tramites.realizados.index', array_merge(request()->except(['preset', 'fecha_desde', 'fecha_hasta']), ['preset' => 'hoy'])) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('preset') === 'hoy' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
                Hoy
            </a>
            <a href="{{ route('tramites.realizados.index', array_merge(request()->except(['preset', 'fecha_desde', 'fecha_hasta']), ['preset' => '7d'])) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('preset') === '7d' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
                Últimos 7 Días
            </a>
            <a href="{{ route('tramites.realizados.index', array_merge(request()->except(['preset', 'fecha_desde', 'fecha_hasta']), ['preset' => 'mes'])) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('preset') === 'mes' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
                Este Mes
            </a>
            <a href="{{ route('tramites.realizados.index', array_merge(request()->except(['preset', 'fecha_desde', 'fecha_hasta']), ['preset' => 'anio'])) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('preset') === 'anio' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
                Este Año
            </a>
            @if(request()->hasAny(['preset', 'fecha_desde', 'fecha_hasta', 'search', 'tipo_tramite', 'estado', 'usuario_id', 'oficina', 'estado_pago']))
                <a href="{{ route('tramites.realizados.index') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition-all">
                    ✕ Limpiar Filtros
                </a>
            @endif
        </div>

        <!-- Panel de Filtros Multi-Criterio -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm">
            <form method="GET" action="{{ route('tramites.realizados.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
                
                <!-- Buscador Universal -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Buscar Trámite o Cliente</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cliente, Cédula, Teléfono, Folio o Motivo..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all pl-9">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>

                <!-- Tipo de Trámite -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Tipo de Trámite</label>
                    <select name="tipo_tramite" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500">
                        <option value="todos" {{ request('tipo_tramite') === 'todos' ? 'selected' : '' }}>📂 Todos los Tipos</option>
                        <option value="poderes" {{ request('tipo_tramite') === 'poderes' ? 'selected' : '' }}>📜 Poderes Notariales</option>
                        <option value="divorcios" {{ request('tipo_tramite') === 'divorcios' ? 'selected' : '' }}>⚖️ Registros de Divorcio</option>
                        <option value="impuestos" {{ request('tipo_tramite') === 'impuestos' ? 'selected' : '' }}>📑 Declaración Impuestos</option>
                        <option value="varios" {{ request('tipo_tramite') === 'varios' ? 'selected' : '' }}>📂 Trámites Varios</option>
                        <option value="personalizados" {{ request('tipo_tramite') === 'personalizados' ? 'selected' : '' }}>✨ Trámites Especiales</option>
                        <option value="documentos" {{ request('tipo_tramite') === 'documentos' ? 'selected' : '' }}>📄 Documentos Emitidos</option>
                    </select>
                </div>

                <!-- Estado del Trámite -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Estado del Trámite</label>
                    <select name="estado" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500">
                        <option value="todos" {{ request('estado') === 'todos' ? 'selected' : '' }}>🔄 Todos los Estados</option>
                        <option value="en_proceso" {{ request('estado') === 'en_proceso' ? 'selected' : '' }}>🟡 En Proceso</option>
                        <option value="en_revision" {{ request('estado') === 'en_revision' ? 'selected' : '' }}>🔵 En Revisión</option>
                        <option value="listo" {{ request('estado') === 'listo' ? 'selected' : '' }}>🟢 Listo para entregar</option>
                        <option value="entregado" {{ request('estado') === 'entregado' ? 'selected' : '' }}>⚪ Entregado al Cliente</option>
                    </select>
                </div>

                <!-- Estado de Pago -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Estado de Pago</label>
                    <select name="estado_pago" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500">
                        <option value="todos" {{ request('estado_pago') === 'todos' ? 'selected' : '' }}>💳 Todos los Pagos</option>
                        <option value="pagado" {{ request('estado_pago') === 'pagado' ? 'selected' : '' }}>🟢 100% Pagados</option>
                        <option value="pendiente" {{ request('estado_pago') === 'pendiente' ? 'selected' : '' }}>🔴 Saldo Pendiente</option>
                    </select>
                </div>

                <!-- Asesor / Creador -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Asesor / Usuario</label>
                    <select name="usuario_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500">
                        <option value="">👤 Todos los Asesores</option>
                        @foreach($usuarios as $u)
                            <option value="{{ $u->id }}" {{ request('usuario_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sucursal / Oficina -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Oficina / Sucursal</label>
                    <select name="oficina" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500">
                        <option value="">🏢 Todas las Oficinas</option>
                        @foreach($oficinas as $of)
                            <option value="{{ $of }}" {{ request('oficina') === $of ? 'selected' : '' }}>{{ $of }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Fecha Desde -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Desde</label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Fecha Hasta -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Ordenamiento -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Ordenar Por</label>
                    <select name="orden" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500">
                        <option value="recientes" {{ request('orden', 'recientes') === 'recientes' ? 'selected' : '' }}>🕒 Más Recientes</option>
                        <option value="antiguos" {{ request('orden') === 'antiguos' ? 'selected' : '' }}>⏳ Más Antiguos</option>
                        <option value="mayor_monto" {{ request('orden') === 'mayor_monto' ? 'selected' : '' }}>💲 Mayor Valor ($)</option>
                        <option value="menor_monto" {{ request('orden') === 'menor_monto' ? 'selected' : '' }}>📉 Menor Valor ($)</option>
                        <option value="cliente_asc" {{ request('orden') === 'cliente_asc' ? 'selected' : '' }}>🔤 Cliente (A-Z)</option>
                    </select>
                </div>

                <!-- Botones de Filtrar / Limpiar -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs py-2.5 px-4 rounded-xl shadow-md shadow-indigo-600/20 transition-all h-[38px] flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        <span>Filtrar</span>
                    </button>
                    @if(request()->hasAny(['search', 'tipo_tramite', 'estado', 'usuario_id', 'oficina', 'estado_pago', 'fecha_desde', 'fecha_hasta', 'orden', 'preset']))
                        <a href="{{ route('tramites.realizados.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all h-[38px] flex items-center justify-center" title="Limpiar Filtros">
                            ✕
                        </a>
                    @endif
                </div>

            </form>
        </div>

        <!-- Tabla Principal de Trámites Realizados -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                        Listado de Trámites Realizados
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Haz clic en cualquier fila o en "🔍 Ver Detalle" para ver la ficha completa, pagos y auditoría de caja</p>
                </div>
                <span class="text-xs font-bold text-slate-700 bg-white px-3 py-1 rounded-full border border-slate-200 shadow-sm">
                    Mostrando {{ $tramites->count() }} de {{ $tramites->total() }} registros
                </span>
            </div>

            @if($tramites->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-800">
                        <thead class="bg-slate-50 uppercase text-[11px] font-bold text-slate-700 border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4">Folio / Tipo</th>
                                <th class="py-3.5 px-4">Fecha & Hora</th>
                                <th class="py-3.5 px-4">Cliente / Contacto</th>
                                <th class="py-3.5 px-4">Asesor / Oficina</th>
                                <th class="py-3.5 px-4 text-center">Estado Trámite</th>
                                <th class="py-3.5 px-4 text-right">Importe / Estado</th>
                                <th class="py-3.5 px-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($tramites as $t)
                                <tr class="hover:bg-indigo-50/40 cursor-pointer transition-colors tramite-row" data-tramite="{{ json_encode($t) }}" onclick="abrirModalDesdeElemento(this)">
                                    
                                    <!-- Folio & Tipo de Trámite -->
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-2xl shrink-0">{{ $t['tipo_icono'] }}</span>
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-extrabold text-slate-900 text-xs">
                                                        {{ $t['tipo_nombre'] }}
                                                    </span>
                                                    <span class="text-[11px] font-black text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100">
                                                        #{{ str_pad($t['id'], 5, '0', STR_PAD_LEFT) }}
                                                    </span>
                                                </div>
                                                <span class="text-[11px] text-slate-500 font-medium truncate block max-w-[200px]" title="{{ $t['subtipo'] }}">
                                                    {{ $t['subtipo'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Fecha & Hora -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <p class="font-bold text-slate-800">{{ $t['fecha_hora_formatted'] }}</p>
                                        <span class="text-[11px] text-slate-500 font-medium">{{ $t['hora'] }}</span>
                                    </td>

                                    <!-- Cliente -->
                                    <td class="py-3.5 px-4">
                                        <p class="font-bold text-slate-900 text-xs truncate max-w-[170px]">{{ $t['cliente_nombre'] }}</p>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-500 font-medium mt-0.5">
                                            <span>ID: {{ $t['cliente_identificacion'] }}</span>
                                            @if($t['cliente_telefono'] && $t['cliente_telefono'] !== 'S/T')
                                                <span>• 📞 {{ $t['cliente_telefono'] }}</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Asesor & Oficina -->
                                    <td class="py-3.5 px-4">
                                        <p class="font-bold text-slate-800 text-xs">{{ $t['usuario'] }}</p>
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 mt-0.5">
                                            🏢 {{ $t['oficina'] }}
                                        </span>
                                    </td>

                                    <!-- Estado Interactivo del Trámite -->
                                    <td class="py-3.5 px-4 text-center" onclick="event.stopPropagation()">
                                        <select onchange="actualizarEstadoTramite(this, '{{ $t['tipo_key'] }}', {{ $t['id'] }}, {{ $t['cliente_id'] ?? 0 }})" 
                                                data-previous="{{ $t['estado'] }}"
                                                class="estado-select text-xs font-black py-1.5 px-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all shadow-sm {{ $t['estado'] === 'listo' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : ($t['estado'] === 'en_revision' ? 'bg-blue-50 text-blue-700 border-blue-300' : ($t['estado'] === 'entregado' ? 'bg-slate-100 text-slate-700 border-slate-300' : 'bg-amber-50 text-amber-800 border-amber-300')) }}">
                                            <option value="en_proceso" {{ $t['estado'] === 'en_proceso' ? 'selected' : '' }}>🟡 En Proceso</option>
                                            <option value="en_revision" {{ $t['estado'] === 'en_revision' ? 'selected' : '' }}>🔵 En Revisión</option>
                                            <option value="listo" {{ $t['estado'] === 'listo' ? 'selected' : '' }}>🟢 Listo para entregar</option>
                                            <option value="entregado" {{ $t['estado'] === 'entregado' ? 'selected' : '' }}>⚪ Entregado</option>
                                        </select>
                                    </td>

                                    <!-- Importe, Abono y Saldo -->
                                    <td class="py-3.5 px-4 text-right">
                                        @if($t['costo'] > 0)
                                             <span class="text-sm font-black text-slate-900 block">
                                                ${{ number_format($t['costo'], 2) }}
                                            </span>
                                            @if($t['saldo'] <= 0)
                                                <span class="inline-block text-[10px] font-black text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200 mt-0.5">
                                                    ✓ Pagado
                                                </span>
                                            @else
                                                <span class="inline-block text-[10px] font-black text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200 mt-0.5">
                                                    Saldo: ${{ number_format($t['saldo'], 2) }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-xs font-bold text-slate-500">Plantilla Emitida</span>
                                        @endif
                                    </td>

                                    <!-- Acciones Rápidas -->
                                    <td class="py-3.5 px-4 text-center" onclick="event.stopPropagation()">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" onclick="abrirModalDesdeElemento(this)" class="p-2 rounded-xl bg-slate-100 hover:bg-indigo-600 text-slate-700 hover:text-white transition-all shadow-sm" title="Ver Detalle / Auditoría">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </button>

                                            @if(!empty($t['documento_pdf']))
                                                <a href="{{ asset($t['documento_pdf']) }}" target="_blank" class="p-2 rounded-xl bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white transition-all shadow-sm" title="Ver Documento PDF Listo">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                </a>
                                            @endif

                                            @if($t['route_pdf'] && $t['route_pdf'] !== '#')
                                                <a href="{{ $t['route_pdf'] }}" target="_blank" class="p-2 rounded-xl bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white transition-all shadow-sm" title="Imprimir Comprobante (1 Hoja)">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                </a>
                                            @endif

                                            @if($t['cliente_id'])
                                                <a href="{{ route('clientes.tramites', $t['cliente_id']) }}" class="p-2 rounded-xl bg-slate-50 hover:bg-slate-600 text-slate-600 hover:text-white transition-all shadow-sm" title="Ir al Expediente del Cliente">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-5 border-t border-slate-100 bg-slate-50/50">
                    {{ $tramites->links() }}
                </div>
            @else
                <div class="text-center py-16 px-4">
                    <div class="w-14 h-14 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                        🔍
                    </div>
                    <h4 class="text-sm font-black text-slate-800">No se encontraron trámites registrados</h4>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">No hay trámites que coincidan con los filtros aplicados o aún no se han registrado procedimientos en el sistema.</p>
                </div>
            @endif
        </div>

    </div>

    <!-- MODAL INTERACTIVO DE DETALLE COMPLETO DE TRÁMITE -->
    <div id="modalDetalleTramite" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop con blur -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="cerrarModalDetalle()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200">
                
                <!-- Modal Header -->
                <div class="bg-slate-900 px-6 py-5 text-white flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <span id="modalIcono" class="text-2xl">📜</span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-extrabold text-white" id="modalTipoNombre">Trámite Notarial</h3>
                                <span id="modalFolioBadge" class="px-2.5 py-0.5 rounded-full text-xs font-black bg-indigo-500 text-white">#00000</span>
                            </div>
                            <p class="text-xs text-slate-300 mt-0.5" id="modalSubtipo">Ficha completa de registro, asesor, pagos y caja</p>
                        </div>
                    </div>
                    <button type="button" onclick="cerrarModalDetalle()" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-colors">
                        ✕
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    
                    <!-- 4 Bloques de Información en Grilla 2x2 -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <!-- 1. Emisión y Asesor -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-indigo-700 flex items-center gap-1.5">
                                <span>👤</span> Registro & Emisión
                            </h4>
                            <div class="space-y-1 text-xs">
                                <p><strong class="text-slate-500">Asesor / Notario:</strong> <span class="font-bold text-slate-900" id="modalAsesor">N/A</span></p>
                                <p><strong class="text-slate-500">Fecha de Emisión:</strong> <span class="font-bold text-slate-900" id="modalFecha">00/00/0000</span></p>
                                <p><strong class="text-slate-500">Hora de Registro:</strong> <span class="font-bold text-slate-900" id="modalHora">--:--</span></p>
                                <p><strong class="text-slate-500">Oficina / Sucursal:</strong> <span class="font-bold text-slate-900" id="modalOficina">General</span></p>
                            </div>
                        </div>

                        <!-- 2. Caja y Recaudación -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-emerald-700 flex items-center gap-1.5">
                                <span>💵</span> Caja & Recaudación
                            </h4>
                            <div class="space-y-1 text-xs">
                                <p><strong class="text-slate-500">Turno de Caja:</strong> <span class="font-bold text-slate-900" id="modalCaja">Sin caja asociada / Cartera</span></p>
                                <p><strong class="text-slate-500">Cajero Receptor:</strong> <span class="font-bold text-slate-900" id="modalCajero">N/A</span></p>
                                <p><strong class="text-slate-500">Método de Pago:</strong> <span class="font-bold text-slate-900" id="modalMetodoPago">Efectivo</span></p>
                                <p><strong class="text-slate-500">Fecha de Cobro:</strong> <span class="font-bold text-slate-900" id="modalFechaPago">N/A</span></p>
                            </div>
                        </div>

                        <!-- 3. Información del Cliente -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                <span>👥</span> Cliente / Titular
                            </h4>
                            <div class="space-y-1 text-xs">
                                <p><strong class="text-slate-500">Nombre:</strong> <span class="font-bold text-slate-900" id="modalClienteNombre">N/A</span></p>
                                <p><strong class="text-slate-500">Cédula / ID:</strong> <span class="font-bold text-slate-900" id="modalClienteId">S/I</span></p>
                                <p><strong class="text-slate-500">Teléfono:</strong> <span class="font-bold text-slate-900" id="modalClienteTel">S/T</span></p>
                                <p><strong class="text-slate-500">Dirección:</strong> <span class="font-bold text-slate-900 truncate block" id="modalClienteDir">N/E</span></p>
                            </div>
                        </div>

                        <!-- 4. Liquidación Financiera -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-rose-700 flex items-center gap-1.5">
                                <span>📊</span> Estado de Pago
                            </h4>
                            <div class="space-y-1 text-xs">
                                <p><strong class="text-slate-500">Valor Trámite:</strong> <span class="font-bold text-slate-900" id="modalFinCosto">$0.00</span></p>
                                <p><strong class="text-slate-500">Abono Inicial:</strong> <span class="font-bold text-emerald-700" id="modalFinAbono">$0.00</span></p>
                                <p><strong class="text-slate-500">Saldo Pendiente:</strong> <span class="font-bold text-rose-700" id="modalFinSaldo">$0.00</span></p>
                            </div>
                        </div>

                    </div>

                    <!-- Observaciones Registradas -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-600">Observaciones / Razón del Trámite</h4>
                        <p class="text-xs text-slate-800 leading-relaxed" id="modalObservaciones">Sin observaciones adicionales.</p>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-3">
                    <a id="modalPdfLink" href="#" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        <span>Imprimir Comprobante (1 Hoja)</span>
                    </a>

                    <a id="modalDocFinalPdfLink" href="#" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all" style="display: none;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Descargar PDF Listo</span>
                    </a>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <a id="modalClienteTramitesLink" href="#" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold shadow-sm transition-all">
                            Expediente Cliente
                        </a>
                        <button type="button" onclick="cerrarModalDetalle()" class="w-full sm:w-auto px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-xl text-xs font-bold transition-all cursor-pointer">
                            Cerrar
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL: SUBIR PDF AL MARCAR "LISTO PARA ENTREGAR" -->
    <div id="modalSubirPdfGlobal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-pdf-global-title" role="dialog" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="cerrarModalPdfGlobal()"></div>

            <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200">
                <form id="formSubirPdfGlobal" onsubmit="enviarPdfGlobal(event)">
                    @csrf
                    <input type="hidden" name="cliente_id" id="global_pdf_cliente_id" value="">
                    <input type="hidden" name="tramite_tipo" id="global_pdf_tramite_tipo" value="">
                    <input type="hidden" name="tramite_id" id="global_pdf_tramite_id" value="">
                    <input type="hidden" name="estado" value="listo">

                    <!-- Header Modal -->
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-6 py-5 text-white flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-white/15 backdrop-blur-md rounded-xl border border-white/20">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black tracking-tight" id="modal-pdf-global-title">Cargar Documento Final PDF</h3>
                                <p class="text-xs text-emerald-100 font-medium" id="global_pdf_tramite_desc">Listo para entregar</p>
                            </div>
                        </div>
                        <button type="button" onclick="cerrarModalPdfGlobal()" class="text-white/70 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition-all cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Body Modal -->
                    <div class="p-6 space-y-4">
                        <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 leading-relaxed font-medium flex items-start gap-2.5">
                            <span class="text-base leading-none">ℹ️</span>
                            <div>
                                Para cambiar el estado a <strong>Listo para entregar</strong>, es obligatorio cargar el <strong>documento final en PDF</strong> redactado y listo para que el cliente lo consulte o retire.
                            </div>
                        </div>

                        <!-- Dropzone / Input de Archivo -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Archivo PDF del Documento <span class="text-red-500">*</span></label>
                            <div id="dropzonePdfGlobal" onclick="document.getElementById('inputDocumentoPdfGlobal').click()" 
                                 class="border-2 border-dashed border-slate-300 hover:border-emerald-500 bg-slate-50 hover:bg-emerald-50/40 rounded-2xl p-6 text-center cursor-pointer transition-all">
                                <input type="file" id="inputDocumentoPdfGlobal" name="documento_pdf" accept="application/pdf" class="hidden" onchange="actualizarPreviewPdfGlobal(this)">
                                
                                <div id="previewPdfEmptyGlobal" class="space-y-2">
                                    <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shadow-xs">
                                        📄
                                    </div>
                                    <div class="text-xs font-bold text-slate-700">
                                        <span class="text-emerald-700 underline">Haz clic para seleccionar</span> o arrastra el archivo aquí
                                    </div>
                                    <p class="text-[11px] text-slate-400 font-medium">Solo archivos PDF (Máx. 25 MB)</p>
                                </div>

                                <div id="previewPdfSelectedGlobal" class="hidden space-y-2">
                                    <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-xl shadow-md">
                                        ✓
                                    </div>
                                    <div class="text-xs font-black text-slate-900 truncate max-w-xs mx-auto" id="nombreArchivoPdfGlobal">
                                        documento.pdf
                                    </div>
                                    <p class="text-[11px] text-emerald-600 font-bold" id="tamanoArchivoPdfGlobal">0 KB</p>
                                    <button type="button" onclick="event.stopPropagation(); limpiarArchivoPdfGlobal();" class="text-[11px] font-bold text-rose-600 hover:underline">
                                        Cambiar archivo
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="globalPdfErrorMsg" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs font-bold text-rose-700"></div>
                    </div>

                    <!-- Footer Modal -->
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-3">
                        <button type="button" onclick="cerrarModalPdfGlobal()" class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" id="btnConfirmarPdfGlobal" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-extrabold shadow-md shadow-emerald-200 transition-all cursor-pointer">
                            <span>🟢 Subir PDF y Marcar Listo</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts para Cambio de Estado AJAX y Modal -->
    <script>
        let activeGlobalSelect = null;

        // Actualización de estado en tiempo real vía AJAX
        function actualizarEstadoTramite(selectElement, tramiteTipo, tramiteId, clienteId) {
            const nuevoEstado = selectElement.value;
            const prevEstado = selectElement.getAttribute('data-previous') || 'en_proceso';

            // Si es "listo", abrir modal de subida de PDF
            if (nuevoEstado === 'listo') {
                abrirModalPdfGlobal(selectElement, tramiteTipo, tramiteId, clienteId);
                return;
            }

            // Actualizar estilo visual del select inmediatamente
            selectElement.className = 'estado-select text-xs font-black py-1.5 px-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all shadow-sm ' + 
                (nuevoEstado === 'listo' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 
                (nuevoEstado === 'en_revision' ? 'bg-blue-50 text-blue-700 border-blue-300' : 
                (nuevoEstado === 'entregado' ? 'bg-slate-100 text-slate-700 border-slate-300' : 'bg-amber-50 text-amber-800 border-amber-300')));

            fetch('{{ route("tramites.cambiar_estado") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    cliente_id: clienteId,
                    tramite_tipo: tramiteTipo,
                    tramite_id: tramiteId,
                    estado: nuevoEstado
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    selectElement.setAttribute('data-previous', nuevoEstado);
                    mostrarToast('Estado actualizado: ' + (nuevoEstado === 'listo' ? 'Listo para entregar' : nuevoEstado.toUpperCase()), 'success');
                } else {
                    selectElement.value = prevEstado;
                    mostrarToast(data.message || 'Error al actualizar el estado', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                selectElement.value = prevEstado;
                mostrarToast('Error de conexión con el servidor', 'error');
            });
        }

        function abrirModalPdfGlobal(selectEl, tipo, id, clienteId) {
            activeGlobalSelect = selectEl;
            document.getElementById('global_pdf_cliente_id').value = clienteId;
            document.getElementById('global_pdf_tramite_tipo').value = tipo;
            document.getElementById('global_pdf_tramite_id').value = id;
            document.getElementById('global_pdf_tramite_desc').innerText = tipo.toUpperCase() + ' #' + id;

            limpiarArchivoPdfGlobal();
            document.getElementById('globalPdfErrorMsg').classList.add('hidden');
            document.getElementById('modalSubirPdfGlobal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalPdfGlobal() {
            document.getElementById('modalSubirPdfGlobal').classList.add('hidden');
            document.body.style.overflow = '';
            if (activeGlobalSelect) {
                const prev = activeGlobalSelect.getAttribute('data-previous') || 'en_proceso';
                activeGlobalSelect.value = prev;
                activeGlobalSelect.className = 'estado-select text-xs font-black py-1.5 px-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all shadow-sm ' + 
                    (prev === 'listo' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 
                    (prev === 'en_revision' ? 'bg-blue-50 text-blue-700 border-blue-300' : 
                    (prev === 'entregado' ? 'bg-slate-100 text-slate-700 border-slate-300' : 'bg-amber-50 text-amber-800 border-amber-300')));
                activeGlobalSelect = null;
            }
        }

        function actualizarPreviewPdfGlobal(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
                    alert('Por favor selecciona únicamente archivos en formato PDF.');
                    input.value = '';
                    return;
                }
                document.getElementById('nombreArchivoPdfGlobal').innerText = file.name;
                const sizeKb = (file.size / 1024).toFixed(1);
                document.getElementById('tamanoArchivoPdfGlobal').innerText = (sizeKb > 1024 ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB');
                document.getElementById('previewPdfEmptyGlobal').classList.add('hidden');
                document.getElementById('previewPdfSelectedGlobal').classList.remove('hidden');
            }
        }

        function limpiarArchivoPdfGlobal() {
            const input = document.getElementById('inputDocumentoPdfGlobal');
            if (input) input.value = '';
            document.getElementById('previewPdfEmptyGlobal').classList.remove('hidden');
            document.getElementById('previewPdfSelectedGlobal').classList.add('hidden');
        }

        async function enviarPdfGlobal(event) {
            event.preventDefault();
            const input = document.getElementById('inputDocumentoPdfGlobal');
            const errorBox = document.getElementById('globalPdfErrorMsg');
            const btnSubmit = document.getElementById('btnConfirmarPdfGlobal');

            if (!input.files || input.files.length === 0) {
                errorBox.innerText = '⚠️ Debes seleccionar o arrastrar el archivo PDF final del trámite.';
                errorBox.classList.remove('hidden');
                return;
            }

            const modalForm = document.getElementById('formSubirPdfGlobal');
            const formData = new FormData(modalForm);

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span>⏳ Subiendo y actualizando...</span>';
            errorBox.classList.add('hidden');

            try {
                const response = await fetch('{{ route("tramites.cambiar_estado") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    if (activeGlobalSelect) {
                        activeGlobalSelect.setAttribute('data-previous', 'listo');
                        activeGlobalSelect.value = 'listo';
                        activeGlobalSelect.className = 'estado-select text-xs font-black py-1.5 px-3 rounded-xl border focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all shadow-sm bg-emerald-50 text-emerald-700 border-emerald-300';
                    }
                    mostrarToast('🎉 ¡Trámite marcado como LISTO PARA ENTREGAR! Documento PDF cargado y alerta enviada.', 'success');
                    
                    activeGlobalSelect = null;
                    document.getElementById('modalSubirPdfGlobal').classList.add('hidden');
                    document.body.style.overflow = '';
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Error al cargar el documento.');
                }
            } catch (error) {
                console.error('Error al subir PDF listo global:', error);
                errorBox.innerText = '⚠️ ' + (error.message || 'Error al procesar el archivo.');
                errorBox.classList.remove('hidden');
            } finally {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<span>🟢 Subir PDF y Marcar Listo</span>';
            }
        }

        // Dropzone listeners
        document.addEventListener('DOMContentLoaded', function () {
            const dropzone = document.getElementById('dropzonePdfGlobal');
            if (dropzone) {
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropzone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        dropzone.classList.add('border-emerald-500', 'bg-emerald-50');
                    }, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropzone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        dropzone.classList.remove('border-emerald-500', 'bg-emerald-50');
                    }, false);
                });

                dropzone.addEventListener('drop', (e) => {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    if (files.length > 0) {
                        const input = document.getElementById('inputDocumentoPdfGlobal');
                        input.files = files;
                        actualizarPreviewPdfGlobal(input);
                    }
                }, false);
            }
        });

        // Abrir modal desde elemento
        function abrirModalDesdeElemento(el) {
            try {
                const row = el.closest('[data-tramite]');
                if (!row) return;
                const raw = row.getAttribute('data-tramite');
                if (!raw) return;
                const tramite = JSON.parse(raw);
                abrirModalDetalle(tramite);
            } catch (err) {
                console.error('Error al procesar datos del trámite:', err);
            }
        }

        function abrirModalDetalle(tramite) {
            if (!tramite) return;

            const setText = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.innerText = val !== null && val !== undefined && val !== '' ? val : 'N/A';
            };

            setText('modalIcono', tramite.tipo_icono || '📜');
            setText('modalTipoNombre', tramite.tipo_nombre || 'Trámite Notarial');
            setText('modalFolioBadge', '#' + String(tramite.id || 0).padStart(5, '0'));
            setText('modalSubtipo', tramite.subtipo || 'Ficha completa de registro');

            // Datos de Registro
            setText('modalAsesor', tramite.usuario || 'N/A');
            setText('modalFecha', tramite.fecha_hora_formatted || tramite.fecha || 'N/A');
            setText('modalHora', tramite.hora || '09:00 AM');
            setText('modalOficina', tramite.oficina || 'General');

            // Datos de Caja
            const cajaEl = document.getElementById('modalCaja');
            if (cajaEl) {
                cajaEl.innerText = tramite.caja_id ? ('Caja Turno #' + tramite.caja_id + ' (' + (tramite.caja_estado || 'Cerrada') + ')') : 'Pendiente en Cartera / Cobro Diferido';
            }
            setText('modalCajero', tramite.cajero || 'N/A');
            setText('modalMetodoPago', tramite.metodo_pago || 'Efectivo / Cartera');
            setText('modalFechaPago', tramite.fecha_pago || 'No registrada');

            // Cliente
            setText('modalClienteNombre', tramite.cliente_nombre || 'N/A');
            setText('modalClienteId', tramite.cliente_identificacion || 'S/I');
            setText('modalClienteTel', tramite.cliente_telefono || 'S/T');
            setText('modalClienteDir', tramite.cliente_direccion || 'No especificada');

            // Finanzas
            const costo = parseFloat(tramite.costo || 0);
            const abono = parseFloat(tramite.abono || 0);
            const saldo = parseFloat(tramite.saldo || 0);

            setText('modalFinCosto', '$' + costo.toFixed(2));
            setText('modalFinAbono', '$' + abono.toFixed(2));
            setText('modalFinSaldo', '$' + saldo.toFixed(2));

            // Observaciones
            setText('modalObservaciones', tramite.observaciones || 'Sin observaciones registradas.');

            // Links
            const pdfLink = document.getElementById('modalPdfLink');
            if (pdfLink) {
                if (tramite.route_pdf && tramite.route_pdf !== '#') {
                    pdfLink.href = tramite.route_pdf;
                    pdfLink.style.display = 'inline-flex';
                } else {
                    pdfLink.style.display = 'none';
                }
            }

            const docFinalLink = document.getElementById('modalDocFinalPdfLink');
            if (docFinalLink) {
                if (tramite.documento_pdf || tramite.documento_pdf_url) {
                    docFinalLink.href = tramite.documento_pdf_url || ('/' + tramite.documento_pdf);
                    docFinalLink.style.display = 'inline-flex';
                } else {
                    docFinalLink.style.display = 'none';
                }
            }

            const clienteLink = document.getElementById('modalClienteTramitesLink');
            if (clienteLink) {
                if (tramite.cliente_id) {
                    clienteLink.href = '/clientes/' + tramite.cliente_id + '/tramites';
                    clienteLink.style.display = 'inline-flex';
                } else {
                    clienteLink.style.display = 'none';
                }
            }

            // Mostrar modal
            const modal = document.getElementById('modalDetalleTramite');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function cerrarModalDetalle() {
            const modal = document.getElementById('modalDetalleTramite');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        // Cerrar con Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                cerrarModalDetalle();
                cerrarModalPdfGlobal();
            }
        });

        // Toast de Notificación
        function mostrarToast(mensaje, tipo = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-6 right-6 z-50 px-4 py-3 rounded-2xl shadow-xl text-xs font-black flex items-center gap-2 transform transition-all duration-300 ${tipo === 'success' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'}`;
            toast.innerHTML = `<span>${tipo === 'success' ? '✓' : '⚠️'}</span> <span>${mensaje}</span>`;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</x-app-layout>
