<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-2xl text-slate-800 tracking-tight leading-none">
                        {{ __('Gestión de Clientes') }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Control de clientes, saldos, abonos y trámites asociados</p>
                </div>
            </div>

            <div class="flex items-center gap-2 bg-white px-3.5 py-1.5 rounded-full border border-slate-200 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs text-slate-500 font-medium">Oficina:</span>
                <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-100">
                    {{ $user->office ?? 'General' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="w-full max-w-[1700px] mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Notificaciones y Alertas -->
            @if (session('success'))
                <div class="flex items-center p-4 text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm transition-all animate-fadeIn" role="alert">
                    <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="flex items-center p-4 text-rose-800 bg-rose-50 border border-rose-200 rounded-xl shadow-sm transition-all animate-fadeIn" role="alert">
                    <svg class="w-5 h-5 mr-3 shrink-0 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            @if (session('imprimir_recibo'))
                <script>
                    window.open('{{ session('imprimir_recibo') }}', '_blank');
                </script>
            @endif

            <!-- Tarjetas de Estadísticas / Resumen Financiero -->
            @isset($stats)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Clientes -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Clientes</p>
                        <h3 class="text-2xl font-black text-slate-800 mt-1">{{ number_format($stats['total_clientes']) }}</h3>
                        <p class="text-xs text-amber-600 font-semibold mt-1 flex items-center gap-1">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            {{ $stats['con_deuda_count'] }} con saldo pendiente
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Deuda Total Acumulada -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Facturado (Deuda)</p>
                        <h3 class="text-2xl font-black text-slate-800 mt-1">${{ number_format($stats['total_deuda'], 2) }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Monto total de trámites</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Total Abonado -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Recaudado (Abonos)</p>
                        <h3 class="text-2xl font-black text-emerald-600 mt-1">${{ number_format($stats['total_abonado'], 2) }}</h3>
                        <p class="text-xs text-emerald-600/80 font-semibold mt-1">Ingresos confirmados</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Saldo Pendiente -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Saldo por Cobrar</p>
                        <h3 class="text-2xl font-black text-amber-600 mt-1">${{ number_format($stats['total_saldo'], 2) }}</h3>
                        <p class="text-xs text-amber-600/80 font-semibold mt-1">Pendiente de pago</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            @endisset

            <!-- Card Principal de Contenido -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-6 space-y-6">
                    
                    <!-- Barra de Búsqueda, Filtros y Acciones Principales -->
                    <div class="flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4 pb-2 border-b border-slate-100">
                        <form method="GET" action="{{ route('clientes.index') }}" class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                            
                            <!-- Input Búsqueda -->
                            <div class="relative w-full sm:w-72">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre, ID o teléfono..." 
                                       class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all text-slate-800 placeholder-slate-400">
                            </div>
                            
                            <!-- Filtro Oficina (si es Admin o Supervisor) -->
                            @if($user->role === 'Administrador' || $user->role === 'Supervisor')
                            <div class="w-full sm:w-auto">
                                <select name="oficina" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all text-slate-700 font-medium">
                                    <option value="">🏢 Todas las oficinas</option>
                                    @foreach($oficinas as $oficina)
                                        <option value="{{ $oficina }}" {{ request('oficina') == $oficina ? 'selected' : '' }}>{{ $oficina }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <!-- Filtro Estado de Deuda -->
                            <div class="w-full sm:w-auto">
                                <select name="deuda" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all text-slate-700 font-medium">
                                    <option value="">📊 Todos los estados</option>
                                    <option value="con_deuda" {{ request('deuda') == 'con_deuda' ? 'selected' : '' }}>⚠️ Con Deuda Pendiente</option>
                                    <option value="sin_deuda" {{ request('deuda') == 'sin_deuda' ? 'selected' : '' }}>✅ Sin Deuda (Al día)</option>
                                </select>
                            </div>

                            <!-- Botón Filtrar -->
                            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl shadow-sm shadow-indigo-200 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                Filtrar
                            </button>
                            
                            <!-- Botón Limpiar Filtros -->
                            @if(request()->anyFilled(['buscar', 'oficina', 'deuda']))
                                <a href="{{ route('clientes.index') }}" class="inline-flex items-center gap-1 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-xl transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Limpiar
                                </a>
                            @endif

                            <!-- Exportar a Excel -->
                            @if(auth()->user()->hasPermission('clientes.export'))
                                <a href="{{ route('clientes.export', request()->all()) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-sm font-semibold rounded-xl transition-all shadow-xs">
                                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM6 20V4h7v5h5v11H6z"/>
                                        <path d="M8.5 12h2v4h-2zm3.5-1h2v6h-2zm3.5 2h2v3h-2z"/>
                                    </svg>
                                    <span>Exportar Excel</span>
                                </a>
                            @endif

                            <!-- Importar desde Excel -->
                            @if(auth()->user()->hasPermission('clientes.create'))
                                <button type="button" onclick="openImportModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-sm font-semibold rounded-xl transition-all shadow-xs cursor-pointer active:scale-95">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    <span>📥 Importar Excel</span>
                                </button>
                            @endif
                        </form>

                        <!-- Botón Crear Nuevo Cliente -->
                        @if(auth()->user()->hasPermission('clientes.create'))
                            <div class="flex shrink-0">
                                <a href="{{ route('clientes.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white text-sm font-bold rounded-xl shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Nuevo Cliente
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Estilos para hover garantizado -->
                    <style>
                        .cliente-table-row {
                            transition: background-color 0.15s ease-in-out;
                        }
                        .cliente-table-row:hover,
                        .cliente-table-row:hover > td {
                            background-color: #eef2ff !important; /* Resaltado Indigo Suave */
                        }
                    </style>

                    <!-- Tabla de Clientes Rediseñada -->
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-sm text-left text-slate-600 divide-y divide-slate-200">
                            <thead class="bg-slate-50 text-slate-700 text-xs uppercase font-extrabold tracking-wider">
                                <tr>
                                    <th scope="col" class="py-3 px-4">Identificación</th>
                                    <th scope="col" class="py-3 px-4">Cliente</th>
                                    <th scope="col" class="py-3 px-4">Contacto</th>
                                    <th scope="col" class="py-3 px-4 text-right">Deuda Total</th>
                                    <th scope="col" class="py-3 px-4 text-center">Saldo Pendiente</th>
                                    <th scope="col" class="py-3 px-4 text-center w-48">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($clientes as $cliente)
                                    <tr class="cliente-table-row group">
                                        
                                        <!-- Identificación / ID -->
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-100 group-hover:bg-white text-slate-700 border border-slate-200/80 transition-colors">
                                                {{ $cliente->c_identificacion }}
                                            </span>
                                        </td>

                                        <!-- Cliente -->
                                        <td class="py-3 px-4">
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-slate-800 truncate group-hover:text-indigo-700 transition-colors">
                                                    {{ $cliente->c_nombre }} {{ $cliente->c_apellido }}
                                                </p>
                                                @if($cliente->c_email)
                                                    <p class="text-xs text-slate-400 truncate mt-0.5">
                                                        {{ $cliente->c_email }}
                                                    </p>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Teléfono / Contacto -->
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5 text-xs font-medium text-slate-600">
                                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                </svg>
                                                <span>{{ $cliente->c_telefono }}</span>
                                            </div>
                                        </td>

                                        <!-- Deuda Total -->
                                        <td class="py-3 px-4 text-right whitespace-nowrap">
                                            <span class="text-sm font-bold text-slate-700">
                                                ${{ number_format($cliente->c_deuda, 2) }}
                                            </span>
                                        </td>

                                        <!-- Saldo Pendiente -->
                                        <td class="py-3 px-4 text-center whitespace-nowrap">
                                            @if($cliente->c_saldo > 0)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-700 border border-amber-200/80 shadow-sm">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                    ${{ number_format($cliente->c_saldo, 2) }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Al día ($0.00)
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Botones de Acción en cuadrícula 2x2 compacta -->
                                        <td class="py-2.5 px-4 whitespace-nowrap">
                                            <div class="grid grid-cols-2 gap-1.5 w-44 mx-auto">
                                                
                                                <!-- Acción 1: Abonar -->
                                                @if($cliente->c_saldo > 0)
                                                    <button onclick="openAbonarModal({{ $cliente->id_cliente }}, '{{ addslashes($cliente->c_nombre . ' ' . $cliente->c_apellido) }}', {{ $cliente->c_saldo }})" 
                                                            title="Registrar Abono"
                                                            class="inline-flex items-center justify-center gap-1 px-2 py-1.5 bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white border border-emerald-200 hover:border-emerald-600 rounded-lg text-xs font-bold transition-all shadow-xs">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v12m6-6H6"></path>
                                                        </svg>
                                                        <span>Abonar</span>
                                                    </button>
                                                @else
                                                    <span class="inline-flex items-center justify-center gap-1 px-2 py-1.5 bg-slate-50 text-slate-400 border border-slate-200/60 rounded-lg text-xs font-medium cursor-not-allowed">
                                                        <svg class="w-3.5 h-3.5 text-slate-300" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        <span>Pagado</span>
                                                    </span>
                                                @endif

                                                <!-- Acción 2: Trámites -->
                                                <a href="{{ route('clientes.tramites', $cliente->id_cliente) }}" 
                                                   title="Ver Trámites del Cliente"
                                                   class="inline-flex items-center justify-center gap-1 px-2 py-1.5 bg-teal-50 hover:bg-teal-600 text-teal-700 hover:text-white border border-teal-200 hover:border-teal-600 rounded-lg text-xs font-bold transition-all shadow-xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    <span>Trámites</span>
                                                </a>

                                                <!-- Acción 3: Historial de Pagos -->
                                                <a href="{{ route('clientes.pagos', $cliente->id_cliente) }}" 
                                                   title="Historial de Pagos y Recibos"
                                                   class="inline-flex items-center justify-center gap-1 px-2 py-1.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white border border-indigo-200 hover:border-indigo-600 rounded-lg text-xs font-bold transition-all shadow-xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                    </svg>
                                                    <span>Pagos</span>
                                                </a>

                                                <!-- Acción 4: Editar -->
                                                <a href="{{ route('clientes.edit', $cliente->id_cliente) }}" 
                                                   title="Editar Datos del Cliente"
                                                   class="inline-flex items-center justify-center gap-1 px-2 py-1.5 bg-slate-100 hover:bg-slate-800 text-slate-700 hover:text-white border border-slate-200 hover:border-slate-800 rounded-lg text-xs font-bold transition-all shadow-xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                    <span>Editar</span>
                                                </a>

                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-12 px-6 text-center">
                                            <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                                <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mb-3">
                                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                    </svg>
                                                </div>
                                                <h4 class="text-base font-bold text-slate-800">No se encontraron clientes</h4>
                                                <p class="text-xs text-slate-500 mt-1">Prueba ajustando los filtros de búsqueda o registra un nuevo cliente.</p>
                                                <a href="{{ route('clientes.create') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-sm hover:bg-indigo-700 transition-all">
                                                    + Crear Primer Cliente
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación Elegante -->
                    <div class="pt-2">
                        {{ $clientes->withQueryString()->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal Moderno para Abonar -->
    <div id="modalAbonar" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            
            <!-- Backdrop con Desenfoque -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeAbonarModal()"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
                <form id="formAbonar" method="POST" action="">
                    @csrf
                    
                    <!-- Header del Modal -->
                    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 px-6 py-5 text-white flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-emerald-500/20 text-emerald-400 rounded-xl border border-emerald-500/30">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white leading-tight" id="modal-title">Registrar Abono</h3>
                                <p class="text-xs text-slate-300 truncate" id="modalClienteNombre">Cliente</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeAbonarModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Contenido del Modal -->
                    <div class="p-6 space-y-4">

                        @if(!$cajaAbierta)
                            <!-- Advertencia: Caja Cerrada -->
                            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-start gap-3 text-rose-800 text-xs font-semibold">
                                <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <strong class="font-bold block text-rose-900">Caja Cerrada Requerida:</strong>
                                    No tienes una caja abierta actualmente. Para poder registrar cobros y abonos debes iniciar turno en el módulo de caja.
                                    <a href="{{ route('cajas.index') }}" class="mt-2 inline-flex items-center gap-1 text-indigo-700 underline font-bold">
                                        Abrir mi Caja de Turno &rarr;
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center justify-between px-3 py-2 bg-emerald-50 rounded-xl border border-emerald-200 text-xs font-bold text-emerald-800">
                                <span class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    Caja Activa #{{ $cajaAbierta->id }}
                                </span>
                                <span class="text-emerald-700 font-normal">Oficina: {{ $cajaAbierta->oficina }}</span>
                            </div>
                        @endif
                        
                        <!-- Mini Resumen Financiero -->
                        <div class="grid grid-cols-2 gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                            <div>
                                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block">Saldo Pendiente</span>
                                <span id="modalSaldoPendiente" class="text-lg font-black text-amber-600">$0.00</span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block">Nuevo Saldo</span>
                                <span id="modalNuevoSaldoPreview" class="text-lg font-black text-slate-700">$0.00</span>
                            </div>
                        </div>

                        <!-- Campo Monto -->
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label for="nuevo_abono" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Monto a Abonar ($) *</label>
                                <button type="button" onclick="setTotalAbono()" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                                    Abonar Totalidad
                                </button>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold">
                                    $
                                </div>
                                <input type="number" step="0.01" min="0.01" name="nuevo_abono" id="nuevo_abono" 
                                       placeholder="0.00"
                                       class="w-full pl-8 pr-4 py-2.5 text-base font-bold bg-white border border-slate-300 rounded-xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-slate-800 transition-all" 
                                       required 
                                       oninput="updateNuevoSaldoPreview()">
                            </div>
                        </div>

                        <!-- Selector Método de Pago -->
                        <div>
                            <label for="metodo_pago_abono" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Método de Pago *</label>
                            <select name="metodo_pago" id="metodo_pago_abono" required onchange="toggleMetodoPagoFields()"
                                    class="w-full text-sm bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 font-medium text-slate-800">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta">Tarjeta / POS (Débito/Crédito)</option>
                                <option value="Transferencia">Transferencia Bancaria</option>
                                <option value="Zelle">Zelle</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>

                        <!-- Campo Condicional: Tarjeta -->
                        <div id="campo_tarjeta" class="hidden space-y-3 p-3.5 bg-pink-50/50 rounded-2xl border border-pink-100">
                            <div>
                                <label for="tarjeta_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Tarjeta / Datáfono *</label>
                                <select name="tarjeta_id" id="tarjeta_id" class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 font-medium text-slate-800">
                                    <option value="">Seleccione la Tarjeta / POS...</option>
                                    @foreach($tarjetas as $tar)
                                        <option value="{{ $tar->id }}">
                                            {{ $tar->nombre }} ({{ $tar->franquicia }} - {{ $tar->banco->nombre ?? 'Banco' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Campo Condicional: Banco para Transferencia / Cheque / Zelle -->
                        <div id="campo_banco" class="hidden space-y-3 p-3.5 bg-blue-50/50 rounded-2xl border border-blue-100">
                            <div>
                                <label for="banco_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Banco Destino / Emisor *</label>
                                <select name="banco_id" id="banco_id" class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 font-medium text-slate-800">
                                    <option value="">Seleccione el Banco...</option>
                                    @foreach($bancos as $ban)
                                        <option value="{{ $ban->id }}">{{ $ban->nombre }} ({{ $ban->tipo_cuenta ?? 'General' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Campo Referencia / Comprobante -->
                        <div id="campo_referencia" class="hidden">
                            <label for="numero_referencia" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">N° de Voucher / Cheque / Confirmación Zelle</label>
                            <input type="text" name="numero_referencia" id="numero_referencia" placeholder="Ej: Voucher #4829, Cheque #004, Ref Zelle #9381"
                                   class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 font-medium text-slate-800">
                        </div>

                        <p class="text-xs text-slate-400 italic">
                            * Al confirmar el abono se registrará en la caja de turno y se emitirá el recibo oficial PDF.
                        </p>
                    </div>

                    <!-- Footer / Botones del Modal -->
                    <div class="bg-slate-50 px-6 py-4 flex flex-col-reverse sm:flex-row justify-end gap-2 border-t border-slate-100">
                        <button type="button" onclick="closeAbonarModal()" class="w-full sm:w-auto px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 transition-all">
                            Cancelar
                        </button>
                        <button type="submit" @if(!$cajaAbierta) disabled @endif 
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg shadow-emerald-200 transition-all disabled:bg-slate-300 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Confirmar y Generar Recibo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts para el Modal Interactivo -->
    <script>
        let currentSaldo = 0;

        function openAbonarModal(id, nombre, saldo) {
            currentSaldo = parseFloat(saldo);
            document.getElementById('modalAbonar').classList.remove('hidden');
            document.getElementById('modalClienteNombre').innerText = nombre;
            document.getElementById('modalSaldoPendiente').innerText = '$' + currentSaldo.toFixed(2);
            document.getElementById('modalNuevoSaldoPreview').innerText = '$' + currentSaldo.toFixed(2);
            
            let inputAbono = document.getElementById('nuevo_abono');
            inputAbono.max = currentSaldo.toFixed(2);
            inputAbono.value = '';
            
            // Set form action dynamically
            let form = document.getElementById('formAbonar');
            form.action = '{{ url("clientes") }}/' + id + '/abonar';
            
            toggleMetodoPagoFields();
            setTimeout(() => inputAbono.focus(), 50);
        }

        function setTotalAbono() {
            let inputAbono = document.getElementById('nuevo_abono');
            inputAbono.value = currentSaldo.toFixed(2);
            updateNuevoSaldoPreview();
        }

        function updateNuevoSaldoPreview() {
            let abono = parseFloat(document.getElementById('nuevo_abono').value) || 0;
            let restante = currentSaldo - abono;
            if (restante < 0) restante = 0;
            document.getElementById('modalNuevoSaldoPreview').innerText = '$' + restante.toFixed(2);
        }

        function toggleMetodoPagoFields() {
            const metodo = document.getElementById('metodo_pago_abono').value;
            const campoTarjeta = document.getElementById('campo_tarjeta');
            const campoBanco = document.getElementById('campo_banco');
            const campoRef = document.getElementById('campo_referencia');

            campoTarjeta.classList.add('hidden');
            campoBanco.classList.add('hidden');
            campoRef.classList.add('hidden');

            if (metodo === 'Tarjeta') {
                campoTarjeta.classList.remove('hidden');
                campoRef.classList.remove('hidden');
            } else if (metodo === 'Transferencia' || metodo === 'Cheque' || metodo === 'Zelle') {
                campoBanco.classList.remove('hidden');
                campoRef.classList.remove('hidden');
            }
        }

        function closeAbonarModal() {
            document.getElementById('modalAbonar').classList.add('hidden');
        }

        // --- Funciones para Modal de Importación Excel ---
        function openImportModal() {
            document.getElementById('modalImportar').classList.remove('hidden');
            clearSelectedFile();
        }

        function closeImportModal() {
            document.getElementById('modalImportar').classList.add('hidden');
        }

        function handleFileSelect(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                showFileDetails(file);
            }
        }

        function showFileDetails(file) {
            document.getElementById('uploadPlaceholder').classList.add('hidden');
            const infoBox = document.getElementById('fileSelectedInfo');
            infoBox.classList.remove('hidden');
            document.getElementById('fileNameDisplay').textContent = file.name;
            document.getElementById('fileSizeDisplay').textContent = (file.size / 1024).toFixed(1) + ' KB - Listo para importar';
        }

        function clearSelectedFile() {
            const fileInput = document.getElementById('archivo_excel');
            if (fileInput) fileInput.value = '';
            document.getElementById('uploadPlaceholder').classList.remove('hidden');
            document.getElementById('fileSelectedInfo').classList.add('hidden');
        }

        function handleDragOver(event) {
            event.preventDefault();
            event.stopPropagation();
            document.getElementById('dropZone').classList.add('border-indigo-500', 'bg-indigo-50/50');
        }

        function handleDragLeave(event) {
            event.preventDefault();
            event.stopPropagation();
            document.getElementById('dropZone').classList.remove('border-indigo-500', 'bg-indigo-50/50');
        }

        function handleFileDrop(event) {
            event.preventDefault();
            event.stopPropagation();
            document.getElementById('dropZone').classList.remove('border-indigo-500', 'bg-indigo-50/50');

            if (event.dataTransfer.files && event.dataTransfer.files.length > 0) {
                const file = event.dataTransfer.files[0];
                const fileInput = document.getElementById('archivo_excel');
                fileInput.files = event.dataTransfer.files;
                showFileDetails(file);
            }
        }

        function handleImportSubmit(event) {
            const btn = document.getElementById('btnSubmitImport');
            const btnText = document.getElementById('btnSubmitImportText');
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            btnText.textContent = 'Procesando archivo...';
        }

        // Cerrar con Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeAbonarModal();
                closeImportModal();
            }
        });
    </script>

    <!-- Modal para Importación Masiva de Clientes por Excel -->
    <div id="modalImportar" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-import-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeImportModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-200">
                
                <form action="{{ route('clientes.import') }}" method="POST" enctype="multipart/form-data" id="formImportClientes" onsubmit="handleImportSubmit(event)">
                    @csrf
                    
                    <!-- Header Modal -->
                    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 p-6 text-white flex items-center justify-between border-b border-slate-700">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-600/40 border border-indigo-400/30 flex items-center justify-center text-2xl shadow-inner shrink-0">
                                📥
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-white tracking-tight" id="modal-import-title">
                                    Importar Clientes desde Excel
                                </h3>
                                <p class="text-xs text-slate-300 mt-0.5">
                                    Carga masiva de clientes en formato .xlsx, .xls o .csv
                                </p>
                            </div>
                        </div>
                        <button type="button" onclick="closeImportModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white flex items-center justify-center transition-colors cursor-pointer">
                            ✕
                        </button>
                    </div>

                    <div class="p-6 space-y-5">
                        <!-- Banner de Plantilla Modelo Descargable -->
                        <div class="bg-indigo-50/70 border border-indigo-200 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <span class="text-2xl">📋</span>
                                <div>
                                    <h4 class="text-xs font-black text-indigo-900">¿No tienes el formato exacto?</h4>
                                    <p class="text-[11px] text-indigo-700 mt-0.5">Descarga la plantilla con encabezados estándar y datos de ejemplo.</p>
                                </div>
                            </div>
                            <a href="{{ route('clientes.template') }}" class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Descargar Plantilla</span>
                            </a>
                        </div>

                        <!-- Zona de Subida Drag & Drop -->
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">
                                Selecciona el Archivo Excel o CSV *
                            </label>
                            <div id="dropZone" 
                                 onclick="document.getElementById('archivo_excel').click()"
                                 ondragover="handleDragOver(event)" 
                                 ondragleave="handleDragLeave(event)" 
                                 ondrop="handleFileDrop(event)"
                                 class="border-2 border-dashed border-slate-300 hover:border-indigo-500 rounded-2xl p-6 text-center bg-slate-50/70 hover:bg-indigo-50/30 transition-all cursor-pointer group">
                                <input type="file" name="archivo_excel" id="archivo_excel" accept=".xlsx, .xls, .csv, text/csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" class="hidden" required onchange="handleFileSelect(this)">
                                
                                <div id="uploadPlaceholder" class="space-y-2 pointer-events-none">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center mx-auto text-2xl group-hover:scale-110 transition-transform">
                                        📊
                                    </div>
                                    <p class="text-xs font-bold text-slate-700">
                                        Arrastra tu archivo aquí o <span class="text-indigo-600 underline">haz clic para examinar</span>
                                    </p>
                                    <p class="text-[11px] text-slate-400">
                                        Formatos compatibles: .xlsx, .xls, .csv (Máx. 20 MB)
                                    </p>
                                </div>

                                <div id="fileSelectedInfo" class="hidden flex items-center justify-center gap-3 text-left bg-white p-3 rounded-xl border border-indigo-200">
                                    <span class="text-2xl">📗</span>
                                    <div class="min-w-0 flex-1">
                                        <p id="fileNameDisplay" class="text-xs font-black text-slate-800 truncate"></p>
                                        <p id="fileSizeDisplay" class="text-[10px] font-semibold text-slate-400"></p>
                                    </div>
                                    <button type="button" onclick="event.stopPropagation(); clearSelectedFile();" class="text-rose-500 hover:text-rose-700 text-xs font-black px-2 py-1 rounded cursor-pointer">
                                        Cambiar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Opciones de Configuración -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1.5">
                                    Si la cédula ya existe:
                                </label>
                                <select name="duplicados" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 font-bold text-slate-700 focus:bg-white focus:border-indigo-500">
                                    <option value="actualizar" selected>🔄 Actualizar datos existentes</option>
                                    <option value="omitir">⏭️ Omitir duplicados</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1.5">
                                    Oficina por defecto:
                                </label>
                                <select name="oficina_registro" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 font-bold text-slate-700 focus:bg-white focus:border-indigo-500">
                                    <option value="{{ $user->office ?? 'General' }}">{{ $user->office ?? 'Oficina Actual' }} (Predeterminada)</option>
                                    @foreach($oficinas as $ofi)
                                        @if($ofi && $ofi !== ($user->office ?? 'General'))
                                            <option value="{{ $ofi }}">{{ $ofi }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Resumen Informativo -->
                        <div class="text-[11px] text-slate-500 bg-slate-100 p-3 rounded-xl border border-slate-200 space-y-1">
                            <p class="font-bold text-slate-700">💡 Columnas reconocidas automáticamente:</p>
                            <p><code class="text-indigo-600 font-mono">identificacion / cedula</code>, <code class="text-indigo-600 font-mono">nombres</code>, <code class="text-indigo-600 font-mono">apellidos</code>, <code class="text-indigo-600 font-mono">telefono</code>, <code class="text-indigo-600 font-mono">email</code>, <code class="text-indigo-600 font-mono">direccion</code>, <code class="text-indigo-600 font-mono">ciudad</code>, <code class="text-indigo-600 font-mono">pais</code>, <code class="text-indigo-600 font-mono">deuda</code>, <code class="text-indigo-600 font-mono">abono</code>.</p>
                        </div>
                    </div>

                    <!-- Footer Modal -->
                    <div class="bg-slate-50 px-6 py-4 flex flex-col-reverse sm:flex-row justify-end gap-2.5 border-t border-slate-100">
                        <button type="button" onclick="closeImportModal()" class="w-full sm:w-auto px-5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 transition-all cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" id="btnSubmitImport" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-xl text-xs font-black shadow-lg shadow-indigo-200 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span id="btnSubmitImportText">Iniciar Importación</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
