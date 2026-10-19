<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 py-2">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-2xl text-slate-800 tracking-tight leading-none">
                        Plantillas de Documentos
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Diseña documentos notariales con variables de clientes o importa desde PDF</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('plantillas.expediente') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold shadow-sm transition-all">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                    Expediente de Documentos
                </a>
                @if(auth()->user()->hasPermission('membretes.view'))
                    <a href="{{ route('plantillas-membretes.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold shadow-sm transition-all">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                        Encabezados y Pies
                    </a>
                @endif
                @if(auth()->user()->hasPermission('plantillas.create'))
                    <a href="{{ route('plantillas.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg shadow-indigo-200 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        + Crear Nueva Plantilla
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen" x-data="{
        modalCliente: false,
        selectedPlantillaId: null,
        selectedPlantillaName: '',
        openModal(id, name) {
            this.selectedPlantillaId = id;
            this.selectedPlantillaName = name;
            this.modalCliente = true;
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

            <!-- Barra de Búsqueda y Filtros -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
                <form method="GET" action="{{ route('plantillas.index') }}" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto flex-1">
                    <div class="relative flex-1 max-w-md">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre o descripción..." 
                               class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>

                    <select name="categoria" onchange="this.form.submit()" class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 font-medium text-slate-700 focus:bg-white focus:border-indigo-500">
                        <option value="todos" {{ $categoria == 'todos' || !$categoria ? 'selected' : '' }}>Todas las Categorías</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat }}" {{ $categoria == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-900 transition-colors">
                        Filtrar
                    </button>
                    @if($search || ($categoria && $categoria !== 'todos'))
                        <a href="{{ route('plantillas.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition-colors inline-flex items-center">
                            Limpiar
                        </a>
                    @endif
                </form>

                <span class="text-xs text-slate-400 font-semibold shrink-0">
                    Mostrando {{ $plantillas->total() }} plantilla(s)
                </span>
            </div>

            <!-- Grid de Plantillas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($plantillas as $p)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md hover:border-slate-300 transition-all group">
                        
                        <div class="p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $p->categoria ?? 'General' }}
                                </span>
                                <span class="text-[11px] text-slate-400 font-medium">
                                    {{ $p->ultima_modificacion ? $p->ultima_modificacion->format('d/m/Y') : '' }}
                                </span>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-slate-800 group-hover:text-indigo-600 transition-colors leading-tight">
                                    {{ $p->nombre_plantilla }}
                                </h3>
                                <p class="text-xs text-slate-500 mt-1.5 line-clamp-2 leading-relaxed">
                                    {{ $p->descripcion ?? 'Plantilla notarial con variables de cliente configuradas.' }}
                                </p>
                            </div>

                            <!-- Preview Miniatura -->
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-[11px] text-slate-400 font-mono line-clamp-3 select-none pointer-events-none">
                                {{ strip_tags($p->contenido_html) }}
                            </div>
                        </div>

                        <!-- Footer de Acciones -->
                        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2">
                            @if(auth()->user()->hasPermission('plantillas.generar') || auth()->user()->hasPermission('plantillas.view'))
                                <button type="button" @click="openModal({{ $p->id_plantilla }}, '{{ addslashes($p->nombre_plantilla) }}')" 
                                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs hover:shadow transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    Usar con Cliente
                                </button>
                            @else
                                <div></div>
                            @endif

                            <div class="flex items-center gap-1">
                                <!-- Duplicar Plantilla -->
                                @if(auth()->user()->hasPermission('plantillas.create'))
                                    <form action="{{ route('plantillas.duplicar', $p->id_plantilla) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="p-2 text-slate-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Duplicar plantilla en 1 clic">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        </button>
                                    </form>
                                @endif

                                @if(auth()->user()->hasPermission('plantillas.edit'))
                                    <a href="{{ route('plantillas.edit', $p->id_plantilla) }}" class="p-2 text-slate-600 hover:text-indigo-600 hover:bg-white rounded-lg border border-transparent hover:border-slate-200 transition-all" title="Editar plantilla">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                @endif

                                @if(auth()->user()->hasPermission('plantillas.delete'))
                                    <form action="{{ route('plantillas.destroy', $p->id_plantilla) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta plantilla?')" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors" title="Eliminar plantilla">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="col-span-full py-16 text-center bg-white rounded-2xl border-2 border-dashed border-slate-200 p-8">
                        <div class="w-16 h-16 bg-indigo-50 text-indigo-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">No se encontraron plantillas</h3>
                        <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 mb-5">Diseña tus formatos notariales con variables o sube un documento PDF para convertirlo.</p>
                        @if(auth()->user()->hasPermission('plantillas.create'))
                            <a href="{{ route('plantillas.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-md hover:bg-indigo-700 transition-all">
                                + Crear Primera Plantilla
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>

            <!-- Paginación -->
            <div class="pt-4">
                {{ $plantillas->withQueryString()->links() }}
            </div>

        </div>

        <!-- Modal para Seleccionar Cliente al Generar Documento -->
        <div x-show="modalCliente" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="modalCliente" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="modalCliente = false"></div>

                <div x-show="modalCliente" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                    
                    <form :action="'{{ url('plantillas') }}/' + selectedPlantillaId + '/generar'" method="GET">
                        <div class="bg-white p-6 space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div>
                                    <h3 class="text-base font-extrabold text-slate-800">Generar Documento</h3>
                                    <p class="text-xs text-slate-400 mt-0.5" x-text="'Plantilla: ' + selectedPlantillaName"></p>
                                </div>
                                <button type="button" @click="modalCliente = false" class="text-slate-400 hover:text-slate-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Selecciona el Cliente *</label>
                                <select name="cliente" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-3 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition-all font-semibold text-slate-800" required>
                                    <option value="">-- Elige un cliente para rellenar datos --</option>
                                    @foreach($clientes as $c)
                                        <option value="{{ $c->id_cliente }}">
                                            {{ $c->c_apellido }}, {{ $c->c_nombre }} (ID: {{ $c->c_identificacion }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="bg-slate-50 px-6 py-4 flex justify-end gap-2 border-t border-slate-100">
                            <button type="button" @click="modalCliente = false" class="px-4 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100">
                                Cancelar
                            </button>
                            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm">
                                Continuar y Generar &rarr;
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>
</x-app-layout>
