<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-2xl text-slate-800 tracking-tight leading-none">
                        {{ __('Bandeja de Solicitudes Web & Leads') }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Consultas y solicitudes de contacto recibidas en tiempo real desde la página web y el directorio de abogados</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('abogados.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all">
                    ⚖️ Ver Red de Abogados
                </a>
                <a href="{{ env('PUBLIC_WEB_URL', 'https://notariaecuador.com') }}/abogados" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition-all">
                    🌐 Ver Portal Web ➔
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
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                
                <a href="{{ route('leads.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between hover:border-indigo-300 transition-all text-decoration-none">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Solicitudes</span>
                        <h4 class="text-2xl font-black text-slate-900 mt-1">{{ $totalLeads }}</h4>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    </div>
                </a>

                <a href="{{ route('leads.index', ['tipo' => 'abogados']) }}" class="bg-white p-5 rounded-2xl border border-indigo-100 shadow-sm flex items-center justify-between hover:border-indigo-300 transition-all text-decoration-none bg-indigo-50/20">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Consultas de Abogados</span>
                        <h4 class="text-2xl font-black text-indigo-700 mt-1">{{ $totalAbogadosLeads }}</h4>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                    </div>
                </a>

                <a href="{{ route('leads.index', ['estado' => 'pendientes']) }}" class="bg-white p-5 rounded-2xl border border-amber-100 shadow-sm flex items-center justify-between hover:border-amber-300 transition-all text-decoration-none bg-amber-50/20">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-600">Pendientes de Contacto</span>
                        <h4 class="text-2xl font-black text-amber-700 mt-1">{{ $totalPendientes }}</h4>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </a>

                <a href="{{ route('leads.index', ['estado' => 'atendidos']) }}" class="bg-white p-5 rounded-2xl border border-emerald-100 shadow-sm flex items-center justify-between hover:border-emerald-300 transition-all text-decoration-none bg-emerald-50/20">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Atendidas / Contactadas</span>
                        <h4 class="text-2xl font-black text-emerald-700 mt-1">{{ $totalAtendidos }}</h4>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </a>

            </div>

            <!-- Filtros & Pestañas -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                
                <!-- Quick Filter Tabs -->
                <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-3">
                    <a href="{{ route('leads.index') }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all text-decoration-none {{ !request()->has('tipo') && !request()->has('estado') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        📥 Todas ({{ $totalLeads }})
                    </a>
                    
                    <a href="{{ route('leads.index', ['tipo' => 'abogados']) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all text-decoration-none {{ request('tipo') == 'abogados' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        ⚖️ Consultas de Abogados ({{ $totalAbogadosLeads }})
                    </a>

                    <a href="{{ route('leads.index', ['tipo' => 'tramites']) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all text-decoration-none {{ request('tipo') == 'tramites' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        📝 Trámites Generales ({{ $totalLeads - $totalAbogadosLeads }})
                    </a>

                    <a href="{{ route('leads.index', ['estado' => 'pendientes']) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all text-decoration-none {{ request('estado') == 'pendientes' ? 'bg-amber-500 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        ⏳ Solo Pendientes ({{ $totalPendientes }})
                    </a>

                    <a href="{{ route('leads.index', ['estado' => 'atendidos']) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all text-decoration-none {{ request('estado') == 'atendidos' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        ✅ Atendidas ({{ $totalAtendidos }})
                    </a>
                </div>

                <!-- Search Input Form -->
                <form method="GET" action="{{ route('leads.index') }}" class="flex flex-col sm:flex-row gap-3 items-center">
                    @if(request('tipo'))
                        <input type="hidden" name="tipo" value="{{ request('tipo') }}">
                    @endif
                    @if(request('estado'))
                        <input type="hidden" name="estado" value="{{ request('estado') }}">
                    @endif

                    <div class="flex-1 w-full relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por cliente, teléfono, abogado o detalle..." 
                               class="w-full text-sm rounded-xl border-slate-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500">
                        <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>

                    <div class="flex gap-2 w-full sm:w-auto">
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex-1 sm:flex-initial text-center">
                            Buscar
                        </button>
                        @if(request()->hasAny(['search', 'tipo', 'estado']))
                            <a href="{{ route('leads.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all text-center">
                                Limpiar
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Listado de Solicitudes -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-black uppercase tracking-wider text-slate-500">
                                <th class="py-3.5 px-4">Fecha / Hora</th>
                                <th class="py-3.5 px-4">Cliente / Interesado</th>
                                <th class="py-3.5 px-4">Trámite o Abogado Solicitado</th>
                                <th class="py-3.5 px-4">Mensaje / Detalle del Caso</th>
                                <th class="py-3.5 px-4 text-center">Estado</th>
                                <th class="py-3.5 px-4 text-center">Acciones Rápidas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($leads as $lead)
                                <tr class="hover:bg-slate-50/75 transition-colors {{ !$lead->synced_to_nesistema ? 'bg-amber-50/30' : '' }}">
                                    
                                    <!-- Fecha -->
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-800 text-xs">
                                            {{ $lead->created_at ? $lead->created_at->format('d/m/Y h:i A') : 'Reciente' }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            {{ $lead->created_at ? $lead->created_at->diffForHumans() : '' }}
                                        </div>
                                    </td>

                                    <!-- Cliente -->
                                    <td class="py-4 px-4">
                                        <div class="font-black text-slate-900 flex items-center gap-1.5">
                                            <span>👤</span> {{ $lead->nombre_completo }}
                                        </div>
                                        <div class="text-xs text-slate-500 mt-1 flex flex-col gap-0.5">
                                            <a href="tel:{{ $lead->telefono }}" class="text-indigo-600 font-bold hover:underline flex items-center gap-1">
                                                📞 {{ $lead->telefono }}
                                            </a>
                                            @if($lead->email)
                                                <span class="text-slate-400 text-[11px]">✉️ {{ $lead->email }}</span>
                                            @endif
                                            @if($lead->ciudad || $lead->pais)
                                                <span class="text-slate-400 text-[11px]">📍 {{ $lead->ciudad ? $lead->ciudad . ', ' : '' }}{{ $lead->pais }}</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Trámite / Abogado -->
                                    <td class="py-4 px-4">
                                        @if($lead->es_consulta_abogado)
                                            <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-700 font-bold text-xs">
                                                <span>⚖️</span> {{ $lead->servicio }}
                                            </div>
                                            <div class="text-[11px] text-indigo-500 font-semibold mt-1">
                                                Canal: Directorio Web de Abogados
                                            </div>
                                        @else
                                            <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-800 font-bold text-xs">
                                                <span>📜</span> {{ $lead->servicio }}
                                            </div>
                                            <div class="text-[11px] text-slate-400 mt-1">
                                                Origen: {{ ucfirst($lead->origen) }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Mensaje -->
                                    <td class="py-4 px-4 max-w-xs">
                                        <div class="p-2.5 bg-slate-50 border border-slate-200/70 rounded-xl text-xs text-slate-700 leading-relaxed max-h-24 overflow-y-auto">
                                            {{ $lead->mensaje ?: 'Sin mensaje adicional proporcionado.' }}
                                        </div>
                                    </td>

                                    <!-- Estado -->
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        <form method="POST" action="{{ route('leads.cambiar_estado', $lead) }}">
                                            @csrf
                                            @if($lead->synced_to_nesistema)
                                                <button type="submit" title="Clic para marcar como pendiente" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 hover:bg-emerald-200 transition-all cursor-pointer">
                                                    <span>✅</span> Atendida
                                                </button>
                                            @else
                                                <button type="submit" title="Clic para marcar como atendida" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800 border border-amber-300 hover:bg-amber-200 transition-all cursor-pointer">
                                                    <span>⏳</span> Pendiente
                                                </button>
                                            @endif
                                        </form>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            
                                            <!-- WhatsApp Directo -->
                                            @php
                                                $waGreeting = urlencode("¡Hola {$lead->nombre}! Te saludamos de Notaría Ecuador Inc. Recibimos tu consulta sobre '{$lead->servicio}'. ¿En qué te podemos asesorar hoy?");
                                                $cleanPhone = preg_replace('/[^0-9]/', '', $lead->telefono);
                                                if (strlen($cleanPhone) == 10 && !str_starts_with($cleanPhone, '1') && !str_starts_with($cleanPhone, '593')) {
                                                    $cleanPhone = '1' . $cleanPhone; // default US
                                                }
                                            @endphp
                                            <a href="https://api.whatsapp.com/send/?phone={{ $cleanPhone }}&text={{ $waGreeting }}" 
                                               target="_blank" 
                                               class="p-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1"
                                               title="Responder por WhatsApp">
                                                <span>💬</span> WhatsApp
                                            </a>

                                            <!-- Registrar como Cliente Oficial en 1 Clic -->
                                            <a href="{{ route('clientes.create', ['c_nombres' => $lead->nombre, 'c_apellidos' => $lead->apellidos, 'c_telefono' => $lead->telefono, 'c_email' => $lead->email, 'c_ciudad' => $lead->ciudad]) }}" 
                                               class="p-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold border border-indigo-200 transition-all"
                                               title="Registrar como Cliente Oficial en NESISTEMA">
                                                👤 + Cliente
                                            </a>

                                            <!-- Eliminar -->
                                            <form method="POST" action="{{ route('leads.destroy', $lead) }}" onsubmit="return confirm('¿Seguro que deseas eliminar esta solicitud?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 bg-slate-100 hover:bg-rose-50 text-slate-400 hover:text-rose-600 rounded-xl text-xs font-bold transition-all" title="Eliminar Solicitud">
                                                    🗑️
                                                </button>
                                            </form>

                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-500">
                                        <div class="text-4xl mb-2">📥</div>
                                        <h4 class="font-extrabold text-slate-800 text-base">No hay solicitudes registradas</h4>
                                        <p class="text-xs text-slate-400 mt-1">Las consultas enviadas por clientes desde la página web aparecerán aquí automáticamente.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($leads->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $leads->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
