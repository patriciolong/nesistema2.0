@php
    $user = Auth::user();
    $cajaNav = $user ? $user->cajaAbierta() : null;
    $chatUnreadNavCount = \App\Models\ChatConversacion::sum('unread_admin');
    $leadsPendientesNavCount = \App\Models\Lead::where('synced_to_nesistema', false)->count();
    $resenasNavCount = \App\Models\ResenaTramite::count();
    $deudoresNavCount = \App\Models\Cliente::where('c_saldo', '>', 0)->count();

    // Check active group states for auto-expansion
    $isClientesActive = request()->routeIs('clientes.*', 'cartera.*', 'leads.*', 'chat.*', 'resenas.*');
    $isTramitesActive = request()->routeIs('tramites.realizados.*', 'plantillas.*', 'plantillas-membretes.*', 'tipo-tramites.*', 'abogados.*');
    $isCajaActive     = request()->routeIs('cajas.*', 'bancos.*', 'tarjetas.*', 'reportes.control_precios.*');
    $isReportesActive = request()->routeIs('reportes.cajas.*', 'reportes.cartera.*');
    $isConfigActive   = request()->routeIs('users.*', 'permisos.*', 'oficinas.*');
@endphp

<!-- Vertical Sidebar Component -->
<aside style="width: 275px; min-width: 275px; height: 100vh; max-height: 100vh; position: sticky; top: 0; background-color: #0f172a; color: #ffffff; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 4px 0 20px rgba(0,0,0,0.18); z-index: 40; flex-shrink: 0; overflow: hidden; box-sizing: border-box; border-right: 1px solid #1e293b;">
    
    <!-- Top Header Brand Logo -->
    <div style="padding: 16px 18px; border-bottom: 1px solid #1e293b; flex-shrink: 0; background: linear-gradient(180deg, #111827 0%, #0f172a 100%);">
        <a href="{{ route('dashboard') }}" style="text-decoration: none; display: block;">
            <div style="background-color: #ffffff; padding: 8px 12px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.15); text-align: center; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                <img src="{{ asset('img/logo_impre.png') }}" alt="NESISTEMA" style="height: 42px; width: auto; max-width: 100%; margin: 0 auto; display: block; object-fit: contain;" />
            </div>
        </a>
        <div style="margin-top: 10px; display: flex; align-items: center; justify-content: space-between;">
            <span style="font-size: 10px; font-weight: 800; color: #94a3b8; letter-spacing: 1px; text-transform: uppercase;">
                NESISTEMA 2.0
            </span>
            @if($user && $user->office)
                <span style="font-size: 10px; font-weight: 800; padding: 2px 8px; background-color: rgba(99, 102, 241, 0.2); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 9999px;">
                    📍 {{ $user->office }}
                </span>
            @endif
        </div>
    </div>

    <!-- Navigation Menu Items with Categories & Accordions -->
    <nav class="custom-sidebar-scroll" style="flex: 1; padding: 12px 14px; overflow-y: auto; overflow-x: hidden; min-height: 0; display: flex; flex-direction: column; gap: 6px;">
        
        <!-- ============================================== -->
        <!-- 1. DASHBOARD PRINCIPAL (Direct Link) -->
        <!-- ============================================== -->
        @if(!$user || $user->hasPermission('dashboard.view'))
            <a href="{{ route('dashboard') }}" 
               style="{{ request()->routeIs('dashboard') ? 'background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: #ffffff; box-shadow: 0 6px 14px -2px rgba(79,70,229,0.5); font-weight: 800;' : 'color: #cbd5e1; font-weight: 700;' }}"
               class="flex items-center justify-between px-3.5 py-2.5 text-xs rounded-xl transition-all duration-200 hover:bg-slate-800 hover:text-white text-decoration-none">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2.5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span>Dashboard Principal</span>
                </div>
                @if(request()->routeIs('dashboard'))
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                @endif
            </a>
        @endif

        <!-- ============================================== -->
        <!-- 2. CATEGORÍA: CLIENTES & ATENCIÓN COMERCIAL -->
        <!-- ============================================== -->
        @php
            $hasClientesPerm = !$user || $user->hasPermission('clientes.view') || $user->hasPermission('cartera.view') || $user->hasPermission('cartera.cobrar');
            $clientesTotalAlerts = $chatUnreadNavCount + $leadsPendientesNavCount;
        @endphp
        @if($hasClientesPerm)
            <div x-data="{ open: {{ $isClientesActive ? 'true' : 'false' }} }" class="rounded-xl overflow-hidden transition-all">
                <button type="button" @click="open = !open" 
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold rounded-xl transition-all duration-200 hover:bg-slate-800/80 cursor-pointer text-left"
                        :class="open ? 'bg-slate-800/50 text-indigo-300' : 'text-slate-400'">
                    <div class="flex items-center min-w-0">
                        <span class="text-sm mr-2">👥</span>
                        <span class="truncate uppercase tracking-wider font-extrabold text-[11px]">Clientes & Atención</span>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        @if($clientesTotalAlerts > 0)
                            <span style="font-size: 9px; font-weight: 900; padding: 1px 6px; background-color: #ef4444; color: #ffffff; border-radius: 9999px;">
                                {{ $clientesTotalAlerts }}
                            </span>
                        @endif
                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-90 text-indigo-400' : 'text-slate-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </button>

                <div x-show="open" x-transition.opacity.duration.200ms class="mt-1 pl-3 space-y-1 border-l-2 border-slate-700/60 ml-3 py-1">
                    <!-- Clientes -->
                    @if(!$user || $user->hasPermission('clientes.view'))
                        <a href="{{ route('clientes.index') }}" 
                           style="{{ request()->routeIs('clientes.*') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                           class="flex items-center px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                            <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('clientes.*') ? 'bg-white' : 'bg-emerald-400' }}"></span>
                            <span>Directorio de Clientes</span>
                        </a>
                    @endif

                    <!-- Cartera & Créditos -->
                    @if(!$user || $user->hasPermission('cartera.view') || $user->hasPermission('cartera.cobrar'))
                        <a href="{{ route('cartera.index') }}" 
                           style="{{ request()->routeIs('cartera.*') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                           class="flex items-center justify-between px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                            <div class="flex items-center truncate">
                                <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('cartera.*') ? 'bg-white' : 'bg-orange-400' }}"></span>
                                <span class="truncate">Cartera & Deudores</span>
                            </div>
                            @if($deudoresNavCount > 0)
                                <span style="font-size: 8px; font-weight: 800; padding: 1px 5px; background-color: #ea580c; color: #fff; border-radius: 9999px;">
                                    {{ $deudoresNavCount }}
                                </span>
                            @endif
                        </a>
                    @endif

                    <!-- Solicitudes Web / Leads -->
                    <a href="{{ route('leads.index') }}" 
                       style="{{ request()->routeIs('leads.*') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                       class="flex items-center justify-between px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                        <div class="flex items-center truncate">
                            <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('leads.*') ? 'bg-white' : 'bg-sky-400' }}"></span>
                            <span class="truncate">Solicitudes Web / Leads</span>
                        </div>
                        @if($leadsPendientesNavCount > 0)
                            <span style="font-size: 8px; font-weight: 800; padding: 1px 5px; background-color: #f59e0b; color: #0f172a; border-radius: 9999px;">
                                {{ $leadsPendientesNavCount }}
                            </span>
                        @endif
                    </a>

                    <!-- Chat en Vivo -->
                    <a href="{{ route('chat.index') }}" 
                       style="{{ request()->routeIs('chat.*') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                       class="flex items-center justify-between px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                        <div class="flex items-center truncate">
                            <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('chat.*') ? 'bg-white' : 'bg-purple-400' }}"></span>
                            <span class="truncate">Chat con Clientes</span>
                        </div>
                        @if($chatUnreadNavCount > 0)
                            <span style="font-size: 8px; font-weight: 800; padding: 1px 5px; background-color: #10b981; color: #fff; border-radius: 9999px;">
                                {{ $chatUnreadNavCount }}
                            </span>
                        @endif
                    </a>

                    <!-- Reseñas y Encuestas -->
                    <a href="{{ route('resenas.index') }}" 
                       style="{{ request()->routeIs('resenas.*') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                       class="flex items-center justify-between px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                        <div class="flex items-center truncate">
                            <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('resenas.*') ? 'bg-white' : 'bg-amber-400' }}"></span>
                            <span class="truncate">Reseñas & Encuestas</span>
                        </div>
                        @if($resenasNavCount > 0)
                            <span style="font-size: 8px; font-weight: 800; padding: 1px 5px; background-color: #f59e0b; color: #0f172a; border-radius: 9999px;">
                                {{ $resenasNavCount }}
                            </span>
                        @endif
                    </a>
                </div>
            </div>
        @endif

        <!-- ============================================== -->
        <!-- 3. CATEGORÍA: GESTIÓN NOTARIAL & TRÁMITES -->
        <!-- ============================================== -->
        <div x-data="{ open: {{ $isTramitesActive ? 'true' : 'false' }} }" class="rounded-xl overflow-hidden transition-all">
            <button type="button" @click="open = !open" 
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold rounded-xl transition-all duration-200 hover:bg-slate-800/80 cursor-pointer text-left"
                    :class="open ? 'bg-slate-800/50 text-indigo-300' : 'text-slate-400'">
                <div class="flex items-center min-w-0">
                    <span class="text-sm mr-2">⚖️</span>
                    <span class="truncate uppercase tracking-wider font-extrabold text-[11px]">Trámites & Notaría</span>
                </div>
                <svg class="w-3.5 h-3.5 transition-transform duration-200 shrink-0" :class="open ? 'rotate-90 text-indigo-400' : 'text-slate-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>

            <div x-show="open" x-transition.opacity.duration.200ms class="mt-1 pl-3 space-y-1 border-l-2 border-slate-700/60 ml-3 py-1">
                <!-- Historial Trámites Realizados -->
                @if(!$user || $user->hasPermission('tramites_global.view') || $user->hasPermission('tramites.view_all') || $user->hasPermission('tramites.create') || $user->role === 'Administrador' || $user->role === 'Supervisor')
                    <a href="{{ route('tramites.realizados.index') }}" 
                       style="{{ request()->routeIs('tramites.realizados.*') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                       class="flex items-center px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                        <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('tramites.realizados.*') ? 'bg-white' : 'bg-sky-400' }}"></span>
                        <span>Trámites Realizados</span>
                    </a>
                @endif

                <!-- Diseñador de Plantillas -->
                @if(!$user || $user->hasPermission('plantillas.view') || $user->hasPermission('plantillas.create') || $user->hasPermission('plantillas.generar'))
                    <a href="{{ route('plantillas.index') }}" 
                       style="{{ request()->routeIs('plantillas.index', 'plantillas.create', 'plantillas.edit', 'plantillas.show', 'plantillas.generar') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                       class="flex items-center px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                        <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('plantillas.index', 'plantillas.create', 'plantillas.edit') ? 'bg-white' : 'bg-amber-400' }}"></span>
                        <span>Diseñador de Plantillas</span>
                    </a>
                @endif

                <!-- Membretes & Diseños -->
                @if(!$user || $user->hasPermission('plantillas.view') || $user->hasPermission('plantillas.create'))
                    <a href="{{ route('plantillas-membretes.index') }}" 
                       style="{{ request()->routeIs('plantillas-membretes.*') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                       class="flex items-center px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                        <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('plantillas-membretes.*') ? 'bg-white' : 'bg-pink-400' }}"></span>
                        <span>Membretes y Encabezados</span>
                    </a>
                @endif

                <!-- Tipos de Trámites Dinámicos -->
                @if(!$user || $user->hasPermission('tipo_tramites.view') || $user->hasPermission('tipo_tramites.manage'))
                    <a href="{{ route('tipo-tramites.index') }}" 
                       style="{{ request()->routeIs('tipo-tramites.*') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                       class="flex items-center px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                        <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('tipo-tramites.*') ? 'bg-white' : 'bg-purple-400' }}"></span>
                        <span>Constructor de Trámites</span>
                    </a>
                @endif

                <!-- Red de Abogados -->
                <a href="{{ route('abogados.index') }}" 
                   style="{{ request()->routeIs('abogados.*') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                   class="flex items-center px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                    <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('abogados.*') ? 'bg-white' : 'bg-indigo-400' }}"></span>
                    <span>Red de Abogados</span>
                </a>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- 4. CATEGORÍA: CAJA & FINANZAS -->
        <!-- ============================================== -->
        @php
            $hasCajaPerm = !$user || $user->hasPermission('cajas.view') || $user->hasPermission('cajas.operar') || $user->hasPermission('bancos.view') || $user->hasPermission('tarjetas.view');
        @endphp
        @if($hasCajaPerm)
            <div x-data="{ open: {{ $isCajaActive ? 'true' : 'false' }} }" class="rounded-xl overflow-hidden transition-all">
                <button type="button" @click="open = !open" 
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold rounded-xl transition-all duration-200 hover:bg-slate-800/80 cursor-pointer text-left"
                        :class="open ? 'bg-slate-800/50 text-indigo-300' : 'text-slate-400'">
                    <div class="flex items-center min-w-0">
                        <span class="text-sm mr-2">💰</span>
                        <span class="truncate uppercase tracking-wider font-extrabold text-[11px]">Caja & Finanzas</span>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        @if($cajaNav)
                            <span style="font-size: 8px; font-weight: 900; padding: 1px 5px; background-color: #10b981; color: #ffffff; border-radius: 9999px;">
                                #{{ $cajaNav->id }}
                            </span>
                        @endif
                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-90 text-indigo-400' : 'text-slate-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </button>

                <div x-show="open" x-transition.opacity.duration.200ms class="mt-1 pl-3 space-y-1 border-l-2 border-slate-700/60 ml-3 py-1">
                    <!-- Operaciones de Caja -->
                    @if(!$user || $user->hasPermission('cajas.view') || $user->hasPermission('cajas.operar'))
                        <a href="{{ route('cajas.index') }}" 
                           style="{{ request()->routeIs('cajas.*') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                           class="flex items-center justify-between px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                            <div class="flex items-center truncate">
                                <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('cajas.*') ? 'bg-white' : 'bg-emerald-400' }}"></span>
                                <span class="truncate">Módulo de Caja</span>
                            </div>
                            @if($cajaNav)
                                <span style="font-size: 8px; font-weight: 800; padding: 1px 4px; background-color: #10b981; color: #fff; border-radius: 4px;">
                                    Abierta
                                </span>
                            @else
                                <span style="font-size: 8px; font-weight: 800; padding: 1px 4px; background-color: #ef4444; color: #fff; border-radius: 4px;">
                                    Cerrada
                                </span>
                            @endif
                        </a>
                    @endif

                    <!-- Bancos -->
                    @if(!$user || $user->hasPermission('bancos.view') || $user->hasPermission('bancos.manage'))
                        <a href="{{ route('bancos.index') }}" 
                           style="{{ request()->routeIs('bancos.*') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                           class="flex items-center px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                            <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('bancos.*') ? 'bg-white' : 'bg-cyan-400' }}"></span>
                            <span>Cuentas Bancarias</span>
                        </a>
                    @endif

                    <!-- Tarjetas / POS -->
                    @if(!$user || $user->hasPermission('tarjetas.view') || $user->hasPermission('tarjetas.manage'))
                        <a href="{{ route('tarjetas.index') }}" 
                           style="{{ request()->routeIs('tarjetas.*') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                           class="flex items-center px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                            <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('tarjetas.*') ? 'bg-white' : 'bg-pink-400' }}"></span>
                            <span>Tarjetas / POS</span>
                        </a>
                    @endif

                    <!-- Control de Precios (±5%) -->
                    @if(!$user || $user->hasPermission('reportes.control_precios') || $user->role === 'Administrador' || $user->role === 'Supervisor')
                        <a href="{{ route('reportes.control_precios.index') }}" 
                           style="{{ request()->routeIs('reportes.control_precios.*') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                           class="flex items-center px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                            <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('reportes.control_precios.*') ? 'bg-white' : 'bg-amber-400' }}"></span>
                            <span>Auditoría de Precios (±5%)</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <!-- ============================================== -->
        <!-- 5. CATEGORÍA: REPORTES & AUDITORÍA -->
        <!-- ============================================== -->
        @php
            $hasReportesPerm = !$user || $user->hasPermission('reportes.cajas') || $user->hasPermission('reportes.desempeno') || $user->hasPermission('reportes.cartera');
        @endphp
        @if($hasReportesPerm)
            <div x-data="{ open: {{ $isReportesActive ? 'true' : 'false' }} }" class="rounded-xl overflow-hidden transition-all">
                <button type="button" @click="open = !open" 
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold rounded-xl transition-all duration-200 hover:bg-slate-800/80 cursor-pointer text-left"
                        :class="open ? 'bg-slate-800/50 text-indigo-300' : 'text-slate-400'">
                    <div class="flex items-center min-w-0">
                        <span class="text-sm mr-2">📊</span>
                        <span class="truncate uppercase tracking-wider font-extrabold text-[11px]">Reportes & Auditoría</span>
                    </div>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200 shrink-0" :class="open ? 'rotate-90 text-indigo-400' : 'text-slate-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <div x-show="open" x-transition.opacity.duration.200ms class="mt-1 pl-3 space-y-1 border-l-2 border-slate-700/60 ml-3 py-1">
                    <!-- Reportes de Caja -->
                    @if(!$user || $user->hasPermission('reportes.cajas'))
                        <a href="{{ route('reportes.cajas.index') }}" 
                           style="{{ request()->routeIs('reportes.cajas.index', 'reportes.cajas.show') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                           class="flex items-center px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                            <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('reportes.cajas.index', 'reportes.cajas.show') ? 'bg-white' : 'bg-sky-400' }}"></span>
                            <span>Reportes de Caja</span>
                        </a>
                    @endif

                    <!-- Desempeño Cajeros -->
                    @if(!$user || $user->hasPermission('reportes.desempeno'))
                        <a href="{{ route('reportes.cajas.desempeno') }}" 
                           style="{{ request()->routeIs('reportes.cajas.desempeno') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                           class="flex items-center px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                            <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('reportes.cajas.desempeno') ? 'bg-white' : 'bg-purple-400' }}"></span>
                            <span>Desempeño de Cajeros</span>
                        </a>
                    @endif

                    <!-- Reporte de Cartera -->
                    @if(!$user || $user->hasPermission('reportes.cartera') || $user->hasPermission('cartera.view'))
                        <a href="{{ route('reportes.cartera.index') }}" 
                           style="{{ request()->routeIs('reportes.cartera.*') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                           class="flex items-center px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                            <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('reportes.cartera.*') ? 'bg-white' : 'bg-rose-400' }}"></span>
                            <span>Reporte de Cartera</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <!-- ============================================== -->
        <!-- 6. CATEGORÍA: CONFIGURACIÓN & ACCESOS -->
        <!-- ============================================== -->
        @php
            $hasConfigPerm = !$user || $user->hasPermission('users.view') || $user->hasPermission('users.manage') || $user->hasPermission('permisos.view') || $user->hasPermission('permisos.edit') || $user->hasPermission('oficinas.view');
        @endphp
        @if($hasConfigPerm)
            <div x-data="{ open: {{ $isConfigActive ? 'true' : 'false' }} }" class="rounded-xl overflow-hidden transition-all">
                <button type="button" @click="open = !open" 
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold rounded-xl transition-all duration-200 hover:bg-slate-800/80 cursor-pointer text-left"
                        :class="open ? 'bg-slate-800/50 text-indigo-300' : 'text-slate-400'">
                    <div class="flex items-center min-w-0">
                        <span class="text-sm mr-2">⚙️</span>
                        <span class="truncate uppercase tracking-wider font-extrabold text-[11px]">Configuración</span>
                    </div>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200 shrink-0" :class="open ? 'rotate-90 text-indigo-400' : 'text-slate-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <div x-show="open" x-transition.opacity.duration.200ms class="mt-1 pl-3 space-y-1 border-l-2 border-slate-700/60 ml-3 py-1">
                    <!-- Usuarios -->
                    @if(!$user || $user->hasPermission('users.view') || $user->hasPermission('users.manage'))
                        <a href="{{ route('users.index') }}" 
                           style="{{ request()->routeIs('users.*') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                           class="flex items-center px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                            <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('users.*') ? 'bg-white' : 'bg-purple-400' }}"></span>
                            <span>Usuarios del Sistema</span>
                        </a>
                    @endif

                    <!-- Permisos de Acceso Granulares -->
                    @if(!$user || $user->hasPermission('permisos.view') || $user->hasPermission('permisos.edit') || $user->hasPermission('permisos.manage'))
                        <a href="{{ route('permisos.index') }}" 
                           style="{{ request()->routeIs('permisos.*') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                           class="flex items-center px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                            <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('permisos.*') ? 'bg-white' : 'bg-indigo-400' }}"></span>
                            <span>Permisos Granulares</span>
                        </a>
                    @endif

                    <!-- Oficinas / Sucursales -->
                    @if(!$user || $user->hasPermission('oficinas.view') || $user->hasPermission('oficinas.manage'))
                        <a href="{{ route('oficinas.index') }}" 
                           style="{{ request()->routeIs('oficinas.*') ? 'background-color: #4f46e5; color: #ffffff; font-weight: 700;' : 'color: #cbd5e1;' }}"
                           class="flex items-center px-2.5 py-1.5 text-xs rounded-lg transition-all hover:bg-slate-800 hover:text-white text-decoration-none">
                            <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->routeIs('oficinas.*') ? 'bg-white' : 'bg-amber-400' }}"></span>
                            <span>Oficinas & Sedes</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

    </nav>

    <!-- User Panel Footer (Always Fixed at Screen Bottom) -->
    @if(Auth::check())
    <div style="padding: 12px 14px; margin: 10px 12px; background-color: #1e293b; border: 1px solid #334155; border-radius: 14px; flex-shrink: 0;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
            <div style="width: 34px; height: 34px; border-radius: 10px; background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); color: #ffffff; font-weight: 900; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(79,70,229,0.3);">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div style="min-width: 0; flex: 1;">
                <div style="font-weight: 800; font-size: 12px; color: #ffffff; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                    {{ Auth::user()->name }}
                </div>
                <div style="font-size: 10px; color: #94a3b8; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                    {{ Auth::user()->email }}
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; padding-top: 8px; border-top: 1px solid #334155;">
            <a href="{{ route('profile.edit') }}" style="display: flex; align-items: center; justify-content: center; padding: 5px; font-size: 11px; font-weight: 700; color: #cbd5e1; background-color: #334155; border-radius: 8px; text-decoration: none; transition: background 0.15s;" onmouseover="this.style.backgroundColor='#475569'" onmouseout="this.style.backgroundColor='#334155'">
                ⚙️ Perfil
            </a>
            
            <form method="POST" action="{{ route('logout') }}" style="display: block; margin: 0;">
                @csrf
                <button type="submit" style="width: 100%; display: flex; align-items: center; justify-content: center; padding: 5px; font-size: 11px; font-weight: 700; color: #fca5a5; background-color: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; cursor: pointer; transition: background 0.15s;" onmouseover="this.style.backgroundColor='rgba(239, 68, 68, 0.25)'" onmouseout="this.style.backgroundColor='rgba(239, 68, 68, 0.15)'">
                    🚪 Salir
                </button>
            </form>
        </div>
    </div>
    @endif
</aside>
