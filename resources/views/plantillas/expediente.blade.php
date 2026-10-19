<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 py-2">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-amber-50 text-amber-600 rounded-xl border border-amber-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-2xl text-slate-800 tracking-tight leading-none">
                        Expediente de Documentos Generados
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Historial completo de documentos redactados e impresos desde plantillas notariales</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('plantillas.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold shadow-sm transition-all">
                    &larr; Volver a Plantillas
                </a>
                @if(auth()->user()->hasPermission('membretes.view'))
                    <a href="{{ route('plantillas-membretes.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold shadow-sm transition-all">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                        Encabezados y Pies
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen" x-data="{
        previewModal: false,
        previewTitle: '',
        previewHtml: '',
        previewClient: '',
        openPreview(title, client, html) {
            this.previewTitle = title;
            this.previewClient = client;
            this.previewHtml = html;
            this.previewModal = true;
        }
    }">
        <div class="w-full max-w-[1700px] mx-auto sm:px-6 lg:px-10 space-y-6">

            @if (session('success'))
                <div class="flex items-center p-4 text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm" role="alert">
                    <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Barra de Filtros y Búsqueda -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <form method="GET" action="{{ route('plantillas.expediente') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                    
                    <!-- Búsqueda General -->
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Buscar por Cliente / Título / Cédula</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </span>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Ej: Patricio, 0989272404, Poder..." 
                                   class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium transition-all">
                        </div>
                    </div>

                    <!-- Filtro por Plantilla -->
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Plantilla Base</label>
                        <select name="plantilla_id" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium">
                            <option value="todas">Todas las plantillas</option>
                            @foreach($plantillas as $pl)
                                <option value="{{ $pl->id_plantilla }}" {{ $plantillaId == $pl->id_plantilla ? 'selected' : '' }}>
                                    {{ $pl->nombre_plantilla }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Fecha Desde -->
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Desde</label>
                        <input type="date" name="fecha_desde" value="{{ $fechaDesde }}" 
                               class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium">
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center gap-2">
                        <button type="submit" class="flex-1 py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Filtrar
                        </button>
                        @if($search || ($plantillaId && $plantillaId !== 'todas') || $fechaDesde || $fechaHasta)
                            <a href="{{ route('plantillas.expediente') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition-all" title="Limpiar Filtros">
                                ✕
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabla de Expediente Digital de Plantillas -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-600">
                        Mostrando {{ $documentos->total() }} documento(s) en el expediente
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200/80 bg-slate-50 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
                                <th class="py-3.5 px-4">Fecha / Hora</th>
                                <th class="py-3.5 px-4">Cliente Asignado</th>
                                <th class="py-3.5 px-4">Documento / Plantilla</th>
                                <th class="py-3.5 px-4">Generado Por</th>
                                <th class="py-3.5 px-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse($documentos as $doc)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <!-- Fecha -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-800">{{ $doc->created_at ? $doc->created_at->format('d/m/Y') : '-' }}</div>
                                        <div class="text-[11px] text-slate-400 font-medium">{{ $doc->created_at ? $doc->created_at->format('h:i A') : '' }}</div>
                                    </td>

                                    <!-- Cliente -->
                                    <td class="py-3.5 px-4">
                                        @if($doc->cliente)
                                            <div class="font-bold text-slate-800">
                                                {{ $doc->cliente->c_nombre }} {{ $doc->cliente->c_apellido }}
                                            </div>
                                            <div class="text-[11px] text-slate-500 font-mono">
                                                C.I: {{ $doc->cliente->c_identificacion }}
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">Sin cliente asignado</span>
                                        @endif
                                    </td>

                                    <!-- Plantilla / Título -->
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-indigo-700 flex items-center gap-1.5">
                                            <span>📄</span>
                                            <span>{{ $doc->titulo_documento }}</span>
                                        </div>
                                        @if($doc->plantilla)
                                            <div class="text-[11px] text-slate-400 mt-0.5">
                                                Plantilla base: {{ $doc->plantilla->nombre_plantilla }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Creador -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            👤 {{ $doc->usuario_creador ?: 'Sistema' }}
                                        </span>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            
                                            <!-- Ver / Re-generar en Diseñador -->
                                            @if($doc->plantilla_id)
                                                <a href="{{ route('plantillas.generar', ['plantilla' => $doc->plantilla_id, 'cliente' => $doc->id_cliente]) }}" 
                                                   class="p-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg text-xs font-bold transition-all" 
                                                   title="Abrir y editar en el Generador">
                                                    ✏️
                                                </a>
                                            @endif

                                            <!-- Eliminar del Expediente -->
                                            @if(auth()->user()->hasPermission('plantillas.delete'))
                                                <form action="{{ route('plantillas.expediente.destroy', $doc->id) }}" method="POST" 
                                                      onsubmit="return confirm('¿Seguro que deseas eliminar este documento del expediente?')" 
                                                      class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs font-bold transition-all cursor-pointer" title="Eliminar registro">
                                                        🗑️
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-400">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl font-bold">
                                            📁
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-700">No hay documentos en el expediente</h4>
                                        <p class="text-xs text-slate-400 mt-1">Los documentos que generes e imprimas desde las plantillas aparecerán aquí organizados.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($documentos->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $documentos->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
