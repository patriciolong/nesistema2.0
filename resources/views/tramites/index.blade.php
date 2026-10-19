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
                    <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                        Expediente de Trámites
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Cliente: <span class="font-bold text-indigo-700">{{ $cliente->c_nombre }} {{ $cliente->c_apellido }}</span>
                        <span class="mx-1.5 text-gray-300">|</span>
                        ID: <span class="font-mono text-gray-800 font-semibold">{{ $cliente->c_identificacion }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('plantillas.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs shadow-sm shadow-amber-200 transition duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    📄 Plantillas de Documentos
                </a>

                @if(Auth::check() && (Auth::user()->role === 'Administrador' || Auth::user()->role === 'Supervisor'))
                <a href="{{ route('tipo-tramites.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-sm shadow-indigo-200 transition duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    + Crear Nuevo Trámite
                </a>
                @endif

                <a href="{{ route('clientes.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-xl font-bold text-xs text-gray-700 uppercase tracking-wider shadow-sm hover:bg-gray-50 hover:text-indigo-700 transition duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver a Clientes
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Estilos para Drag and Drop de Tarjetas -->
    <style>
        .sortable-ghost {
            opacity: 0.35;
            transform: scale(0.96);
            filter: grayscale(40%);
        }
        .sortable-chosen {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transform: scale(1.02);
            cursor: grabbing !important;
        }
        .sortable-drag {
            opacity: 0.95;
            cursor: grabbing !important;
        }
        .drag-handle {
            cursor: grab;
        }
        .drag-handle:active {
            cursor: grabbing;
        }
    </style>

    <div class="py-8 bg-slate-100 min-h-screen">
        <div class="w-full max-w-[1700px] mx-auto sm:px-6 lg:px-10 space-y-8">
            
            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-r-xl shadow-sm" role="alert">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-900 font-semibold">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('imprimir_tramite') || session('imprimir_recibo'))
                <div x-data="{ open: true }" @keydown.window.escape="open = false" x-show="open" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
                    <!-- Background backdrop -->
                    <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="open = false"></div>

                    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                            <!-- Modal panel -->
                            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                            <h3 class="text-base font-bold leading-6 text-gray-900" id="modal-title">¡Operación registrada correctamente!</h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500">¿Qué documento deseas imprimir en este momento?</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-4 sm:px-6 flex flex-col gap-3 border-t border-gray-100">
                                    @if(session('imprimir_tramite'))
                                    <a href="{{ session('imprimir_tramite') }}" target="_blank" class="inline-flex w-full justify-center items-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        Imprimir Comprobante de Trámite
                                    </a>
                                    @endif
                                    
                                    @if(session('imprimir_recibo'))
                                    <a href="{{ session('imprimir_recibo') }}" target="_blank" class="inline-flex w-full justify-center items-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition-colors">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Imprimir Recibo de Cobro / Abono
                                    </a>
                                    @endif
                                    
                                    <button type="button" @click="open = false" class="mt-1 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">
                                        Continuar sin imprimir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Resumen Financiero y Cartera del Cliente -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Honorarios (Deuda)</p>
                        <p class="text-2xl font-black text-slate-900 mt-0.5">${{ number_format($cliente->c_deuda, 2) }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xl">
                        📊
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Total Cobrado / Abonado</p>
                        <p class="text-2xl font-black text-emerald-700 mt-0.5">${{ number_format($cliente->c_abonado, 2) }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xl">
                        ✓
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 border {{ $cliente->c_saldo > 0 ? 'border-amber-300 bg-amber-50/50' : 'border-slate-200' }} shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold {{ $cliente->c_saldo > 0 ? 'text-amber-800' : 'text-slate-500' }} uppercase tracking-wider">Saldo Pendiente en Cartera</p>
                        <p class="text-2xl font-black {{ $cliente->c_saldo > 0 ? 'text-amber-700' : 'text-slate-900' }} mt-0.5">${{ number_format($cliente->c_saldo, 2) }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-2xl {{ $cliente->c_saldo > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center font-bold text-xl">
                        💵
                    </div>
                </div>
            </div>

            <!-- Hub de Creación - Tarjetas Arrastrables y Reordenables -->
            <div class="pt-2">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
                        <h3 class="text-xl font-extrabold text-gray-900">
                            Iniciar Nuevo Trámite
                        </h3>
                        <span class="text-xs text-gray-400 font-medium ml-2 hidden sm:inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                            (Arrastra las tarjetas para reordenarlas a tu gusto)
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        @if(Auth::check() && (Auth::user()->role === 'Administrador' || Auth::user()->role === 'Supervisor'))
                        <a href="{{ route('tipo-tramites.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition-colors">
                            ⚙️ Administrar Trámites
                        </a>
                        @endif

                        <!-- Botón Restablecer Orden -->
                        <button type="button" onclick="resetHubOrder()" id="btnResetOrder" class="hidden text-xs font-semibold text-gray-500 hover:text-indigo-600 items-center gap-1 px-3 py-1.5 rounded-lg bg-white border border-gray-200 shadow-xs hover:border-indigo-300 transition-all">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Restablecer orden
                        </button>
                    </div>
                </div>

                <!-- Grid de Tarjetas Sortable (Nativas + Personalizadas) -->
                <div id="hub-tramites-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <!-- 1. Trámites Varios -->
                    <div data-id="tramite_vario" class="tramite-hub-card relative group">
                        <a href="{{ route('tramites-varios.create', ['cliente' => $cliente->id_cliente]) }}" 
                           style="background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);"
                           class="block rounded-2xl p-6 shadow-lg hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-300 border border-indigo-400/30 relative">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white" style="background-color: rgba(255, 255, 255, 0.25);">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <span class="drag-handle p-1 text-white/50 hover:text-white transition-colors rounded-lg bg-black/10 hover:bg-black/20" title="Arrastrar para mover">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8.5 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm7-12a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/></svg>
                                    </span>
                                    <span class="text-white/80 group-hover:translate-x-1 transition-transform font-bold text-lg">&rarr;</span>
                                </div>
                            </div>
                            <h4 class="text-xl font-black text-white mb-1 tracking-wide">Trámite Vario</h4>
                            <p class="text-indigo-100 text-xs font-medium leading-relaxed">Traducciones, notarizaciones, cartas y más.</p>
                        </a>
                    </div>

                    <!-- 2. Divorcio -->
                    <div data-id="divorcio" class="tramite-hub-card relative group">
                        <a href="{{ route('divorcios.create', ['cliente' => $cliente->id_cliente]) }}" 
                           style="background: linear-gradient(135deg, #e11d48 0%, #9f1239 100%);"
                           class="block rounded-2xl p-6 shadow-lg hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-300 border border-rose-400/30 relative">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white" style="background-color: rgba(255, 255, 255, 0.25);">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <span class="drag-handle p-1 text-white/50 hover:text-white transition-colors rounded-lg bg-black/10 hover:bg-black/20" title="Arrastrar para mover">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8.5 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm7-12a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/></svg>
                                    </span>
                                    <span class="text-white/80 group-hover:translate-x-1 transition-transform font-bold text-lg">&rarr;</span>
                                </div>
                            </div>
                            <h4 class="text-xl font-black text-white mb-1 tracking-wide">Divorcio</h4>
                            <p class="text-rose-100 text-xs font-medium leading-relaxed">Separación, trámites notariales o consensuados.</p>
                        </a>
                    </div>

                    <!-- 3. Impuestos -->
                    <div data-id="impuestos" class="tramite-hub-card relative group">
                        <a href="{{ route('impuestos.create', ['cliente' => $cliente->id_cliente]) }}" 
                           style="background: linear-gradient(135deg, #059669 0%, #064e3b 100%);"
                           class="block rounded-2xl p-6 shadow-lg hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-300 border border-emerald-400/30 relative">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white" style="background-color: rgba(255, 255, 255, 0.25);">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <span class="drag-handle p-1 text-white/50 hover:text-white transition-colors rounded-lg bg-black/10 hover:bg-black/20" title="Arrastrar para mover">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8.5 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm7-12a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/></svg>
                                    </span>
                                    <span class="text-white/80 group-hover:translate-x-1 transition-transform font-bold text-lg">&rarr;</span>
                                </div>
                            </div>
                            <h4 class="text-xl font-black text-white mb-1 tracking-wide">Impuestos</h4>
                            <p class="text-emerald-100 text-xs font-medium leading-relaxed">Declaraciones, número ITIN y reporte anual.</p>
                        </a>
                    </div>

                    <!-- 4. Poderes -->
                    <div data-id="poderes" class="tramite-hub-card relative group">
                        <a href="{{ route('poderes.create', ['cliente' => $cliente->id_cliente]) }}" 
                           style="background: linear-gradient(135deg, #d97706 0%, #78350f 100%);"
                           class="block rounded-2xl p-6 shadow-lg hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-300 border border-amber-400/30 relative">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white" style="background-color: rgba(255, 255, 255, 0.25);">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <span class="drag-handle p-1 text-white/50 hover:text-white transition-colors rounded-lg bg-black/10 hover:bg-black/20" title="Arrastrar para mover">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8.5 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm7-12a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/></svg>
                                    </span>
                                    <span class="text-white/80 group-hover:translate-x-1 transition-transform font-bold text-lg">&rarr;</span>
                                </div>
                            </div>
                            <h4 class="text-xl font-black text-white mb-1 tracking-wide">Poderes</h4>
                            <p class="text-amber-100 text-xs font-medium leading-relaxed">Autorizaciones, mandatos y envíos a Ecuador.</p>
                        </a>
                    </div>

                    <!-- 5+. Tarjetas de Trámites Dinámicos / Personalizados -->
                    @foreach($tiposPersonalizados as $tipoCustom)
                        <div data-id="custom_{{ $tipoCustom->id }}" class="tramite-hub-card relative group">
                            <a href="{{ route('tramites-personalizados.create', ['cliente' => $cliente->id_cliente, 'tipoTramite' => $tipoCustom->id]) }}" 
                               style="background: {{ $tipoCustom->color_gradient }};"
                               class="block rounded-2xl p-6 shadow-lg hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-300 border border-white/20 relative">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white" style="background-color: rgba(255, 255, 255, 0.25);">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <span class="drag-handle p-1 text-white/50 hover:text-white transition-colors rounded-lg bg-black/10 hover:bg-black/20" title="Arrastrar para mover">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8.5 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm7-12a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/></svg>
                                        </span>
                                        <span class="text-white/80 group-hover:translate-x-1 transition-transform font-bold text-lg">&rarr;</span>
                                    </div>
                                </div>
                                <h4 class="text-xl font-black text-white mb-1 tracking-wide">{{ $tipoCustom->nombre }}</h4>
                                <p class="text-white/80 text-xs font-medium leading-relaxed truncate">{{ $tipoCustom->descripcion ?? 'Trámite personalizado.' }}</p>
                            </a>
                        </div>
                    @endforeach

                </div>
            </div>

            <!-- Listado de Registros por Categoría -->
            <div class="space-y-8">
                
                <!-- Sección para Trámites Personalizados si existen -->
                @if(isset($tramitesPersonalizados) && $tramitesPersonalizados->count() > 0)
                <div class="bg-white rounded-2xl shadow-md border border-slate-200 overflow-hidden">
                    <div class="border-b border-indigo-100 px-6 py-4 flex justify-between items-center bg-gradient-to-r from-indigo-50 to-purple-50">
                        <h3 class="text-base font-extrabold text-indigo-950 flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-indigo-600 inline-block shadow-sm"></span> 
                            Otros Trámites Personalizados Registrados
                        </h3>
                        <span class="text-xs px-3 py-1 rounded-full font-extrabold bg-indigo-100 text-indigo-800">
                            {{ $tramitesPersonalizados->count() }} Registros
                        </span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-700">
                            <thead class="text-xs text-gray-700 uppercase border-b border-gray-200 bg-slate-50">
                                <tr>
                                    <th scope="col" class="py-3.5 px-6 font-bold">Trámite #</th>
                                    <th scope="col" class="py-3.5 px-6 font-bold">Tipo de Trámite</th>
                                    <th scope="col" class="py-3.5 px-6 font-bold">Fecha</th>
                                    <th scope="col" class="py-3.5 px-6 font-bold">Estado / Notificación App</th>
                                    <th scope="col" class="py-3.5 px-6 text-right font-bold">Valor / Saldo</th>
                                    <th scope="col" class="py-3.5 px-6 text-center font-bold">Comprobante</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($tramitesPersonalizados as $tp)
                                    <tr class="bg-white hover:bg-slate-50 transition-colors">
                                        <td class="py-4 px-6 font-extrabold text-gray-900">#{{ str_pad($tp->id, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td class="py-4 px-6 font-bold text-indigo-700">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold text-white shadow-xs" style="background: {{ $tp->tipoTramite->color_gradient ?? '#4f46e5' }}">
                                                {{ $tp->tipoTramite->nombre ?? 'Trámite' }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-gray-600 font-medium">{{ $tp->fecha ? date('d/m/Y', strtotime($tp->fecha)) : $tp->created_at->format('d/m/Y') }}</td>
                                        <td class="py-4 px-6">
                                            <form method="POST" action="{{ route('tramites.cambiar_estado') }}" class="form-cambiar-estado inline-flex items-center m-0" data-desc="{{ $tp->tipoTramite->nombre ?? 'Trámite' }} #{{ $tp->id }}">
                                                @csrf
                                                <input type="hidden" name="cliente_id" value="{{ $cliente->id_cliente }}">
                                                <input type="hidden" name="tramite_tipo" value="personalizados">
                                                <input type="hidden" name="tramite_id" value="{{ $tp->id }}">
                                                <select name="estado" data-previous="{{ $tp->estado ?? 'en_proceso' }}" class="estado-select text-[11px] font-bold py-1 px-2.5 rounded-lg border focus:ring-1 focus:ring-indigo-500 transition-all cursor-pointer {{ ($tp->estado ?? 'en_proceso') === 'listo' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : (($tp->estado ?? '') === 'en_revision' ? 'bg-blue-50 text-blue-700 border-blue-300' : (($tp->estado ?? '') === 'entregado' ? 'bg-slate-100 text-slate-700 border-slate-300' : 'bg-amber-50 text-amber-800 border-amber-300')) }}">
                                                    <option value="en_proceso" {{ ($tp->estado ?? 'en_proceso') === 'en_proceso' ? 'selected' : '' }}>🟡 En Proceso</option>
                                                    <option value="en_revision" {{ ($tp->estado ?? '') === 'en_revision' ? 'selected' : '' }}>🔵 En Revisión</option>
                                                    <option value="listo" {{ ($tp->estado ?? '') === 'listo' ? 'selected' : '' }}>🟢 Listo para entregar</option>
                                                    <option value="entregado" {{ ($tp->estado ?? '') === 'entregado' ? 'selected' : '' }}>⚪ Entregado</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td class="py-4 px-6 text-right font-semibold">
                                            <div class="text-xs">
                                                <span class="text-slate-800 font-bold">${{ number_format($tp->valor_tramite, 2) }}</span>
                                                @if($tp->saldo > 0)
                                                    <span class="text-amber-600 block text-[11px] font-bold">Saldo: ${{ number_format($tp->saldo, 2) }}</span>
                                                @else
                                                    <span class="text-emerald-600 block text-[11px] font-bold">✓ Pagado</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                                @if($tp->saldo > 0)
                                                    <button type="button" onclick="abrirModalCobro('personalizados', {{ $tp->id }}, '{{ $tp->tipoTramite->nombre ?? 'Trámite' }} #{{ $tp->id }}', {{ $tp->saldo }})" class="inline-flex items-center gap-1 font-bold text-xs px-2.5 py-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs cursor-pointer">
                                                        💵 Cobrar
                                                    </button>
                                                @endif
                                                @if(!empty($tp->documento_pdf))
                                                    <a href="{{ asset($tp->documento_pdf) }}" target="_blank" class="inline-flex items-center gap-1 font-bold text-xs px-2.5 py-1.5 rounded-lg border shadow-sm bg-emerald-50 text-emerald-800 border-emerald-300 hover:bg-emerald-600 hover:text-white" title="Ver Documento PDF Listo">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                        <span>PDF Final</span>
                                                    </a>
                                                @endif
                                                <a href="{{ route('tramites-personalizados.show', $tp->id) }}" target="_blank" class="inline-flex items-center gap-1 font-bold text-xs px-2.5 py-1.5 rounded-lg transition-colors border shadow-sm bg-indigo-50 text-indigo-800 border-indigo-200 hover:bg-indigo-600 hover:text-white" title="Imprimir Formulario">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                    <span>Hoja</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Tabla Trámites Varios -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="border-b border-indigo-100 px-6 py-4 flex justify-between items-center" style="background-color: #f5f3ff;">
                        <h3 class="text-base font-extrabold text-indigo-950 flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-indigo-600 inline-block shadow-sm"></span> Trámites Varios
                        </h3>
                        <span class="text-xs px-3 py-1 rounded-full font-extrabold" style="background-color: #e0e7ff; color: #3730a3;">
                            {{ $tramitesVarios->count() }} Registros
                        </span>
                    </div>
                    <div>
                        @if($tramitesVarios->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left text-gray-700">
                                    <thead class="text-xs text-gray-700 uppercase border-b border-gray-200" style="background-color: #f8fafc;">
                                        <tr>
                                            <th scope="col" class="py-3.5 px-6 font-bold">Trámite #</th>
                                            <th scope="col" class="py-3.5 px-6 font-bold">Motivo / Tipo</th>
                                            <th scope="col" class="py-3.5 px-6 font-bold">Fecha</th>
                                            <th scope="col" class="py-3.5 px-6 font-bold">Envío</th>
                                            <th scope="col" class="py-3.5 px-6 font-bold">Estado / Notificación App</th>
                                            <th scope="col" class="py-3.5 px-6 text-right font-bold">Valor / Saldo</th>
                                            <th scope="col" class="py-3.5 px-6 text-center font-bold">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($tramitesVarios as $tramite)
                                            <tr class="bg-white hover:bg-slate-50 transition-colors">
                                                <td class="py-4 px-6 font-extrabold text-gray-900">#{{ str_pad($tramite->id_tramite_varios, 5, '0', STR_PAD_LEFT) }}</td>
                                                <td class="py-4 px-6 font-semibold text-gray-800">{{ $tramite->tv_motivo }}</td>
                                                <td class="py-4 px-6 text-gray-600 font-medium">{{ $tramite->tv_fecha ? date('d/m/Y', strtotime($tramite->tv_fecha)) : '-' }}</td>
                                                <td class="py-4 px-6">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                                        {{ $tramite->tv_oenvio ?? 'Presencial' }}
                                                    </span>
                                                </td>
                                                <td class="py-4 px-6">
                                                    <form method="POST" action="{{ route('tramites.cambiar_estado') }}" class="form-cambiar-estado inline-flex items-center m-0" data-desc="Trámite Vario #{{ $tramite->id_tramite_varios }}">
                                                        @csrf
                                                        <input type="hidden" name="cliente_id" value="{{ $cliente->id_cliente }}">
                                                        <input type="hidden" name="tramite_tipo" value="varios">
                                                        <input type="hidden" name="tramite_id" value="{{ $tramite->id_tramite_varios }}">
                                                        <select name="estado" data-previous="{{ $tramite->estado ?? 'en_proceso' }}" class="estado-select text-[11px] font-bold py-1 px-2.5 rounded-lg border focus:ring-1 focus:ring-indigo-500 transition-all cursor-pointer {{ ($tramite->estado ?? 'en_proceso') === 'listo' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : (($tramite->estado ?? '') === 'en_revision' ? 'bg-blue-50 text-blue-700 border-blue-300' : (($tramite->estado ?? '') === 'entregado' ? 'bg-slate-100 text-slate-700 border-slate-300' : 'bg-amber-50 text-amber-800 border-amber-300')) }}">
                                                            <option value="en_proceso" {{ ($tramite->estado ?? 'en_proceso') === 'en_proceso' ? 'selected' : '' }}>🟡 En Proceso</option>
                                                            <option value="en_revision" {{ ($tramite->estado ?? '') === 'en_revision' ? 'selected' : '' }}>🔵 En Revisión</option>
                                                            <option value="listo" {{ ($tramite->estado ?? '') === 'listo' ? 'selected' : '' }}>🟢 Listo para entregar</option>
                                                            <option value="entregado" {{ ($tramite->estado ?? '') === 'entregado' ? 'selected' : '' }}>⚪ Entregado</option>
                                                        </select>
                                                    </form>
                                                </td>
                                                <td class="py-4 px-6 text-right font-semibold">
                                                    <div class="text-xs">
                                                        <span class="text-slate-800 font-bold">${{ number_format($tramite->tv_valor_tramite ?? 0, 2) }}</span>
                                                        @if(($tramite->tv_saldo ?? 0) > 0)
                                                            <span class="text-amber-600 block text-[11px] font-bold">Saldo: ${{ number_format($tramite->tv_saldo, 2) }}</span>
                                                        @else
                                                            <span class="text-emerald-600 block text-[11px] font-bold">✓ Pagado</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="py-4 px-6 text-center">
                                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                                        @if(($tramite->tv_saldo ?? 0) > 0)
                                                            <button type="button" onclick="abrirModalCobro('varios', {{ $tramite->id_tramite_varios }}, 'Trámite Vario #{{ $tramite->id_tramite_varios }}', {{ $tramite->tv_saldo }})" class="inline-flex items-center gap-1 font-bold text-xs px-2.5 py-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs cursor-pointer">
                                                                💵 Cobrar
                                                            </button>
                                                        @endif
                                                        @if(!empty($tramite->documento_pdf))
                                                            <a href="{{ asset($tramite->documento_pdf) }}" target="_blank" class="inline-flex items-center gap-1 font-bold text-xs px-2.5 py-1.5 rounded-lg border shadow-sm bg-emerald-50 text-emerald-800 border-emerald-300 hover:bg-emerald-600 hover:text-white" title="Ver Documento PDF Listo">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                <span>PDF Final</span>
                                                            </a>
                                                        @endif
                                                        <a href="{{ route('tramites-varios.show', $tramite->id_tramite_varios) }}" target="_blank" class="inline-flex items-center gap-1 font-bold text-xs px-2.5 py-1.5 rounded-lg transition-colors border shadow-sm" style="background-color: #e0e7ff; color: #312e81; border-color: #c7d2fe;" title="Imprimir Comprobante">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                            <span>Hoja</span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-8 text-center text-gray-500 font-medium">
                                <p class="text-xs">No hay trámites varios registrados.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Tarjetas inferiores: Divorcios, Impuestos y Poderes -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Divorcios -->
                    <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden flex flex-col justify-between">
                        <div>
                            <div class="border-b border-rose-100 px-5 py-4 flex justify-between items-center" style="background-color: #fff1f2;">
                                <h3 class="text-base font-extrabold text-rose-950 flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-rose-600 inline-block shadow-sm"></span> Divorcios
                                </h3>
                                <span class="text-xs px-2.5 py-0.5 rounded-full font-extrabold" style="background-color: #ffe4e6; color: #9f1239;">
                                    {{ $divorcios->count() }} Registros
                                </span>
                            </div>
                            <div class="p-4 text-sm">
                                @if($divorcios->count() > 0)
                                    <ul class="space-y-3">
                                        @foreach($divorcios as $tramite)
                                            <li class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-rose-300 hover:shadow-sm transition-all space-y-2.5">
                                                
                                                <!-- Fila 1: ID, Fecha y Selector de Estado -->
                                                <div class="flex items-center justify-between gap-2">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <span class="font-black text-slate-900 text-sm">#{{ str_pad($tramite->id_tram_div, 5, '0', STR_PAD_LEFT) }}</span>
                                                        @if(!empty($tramite->td_fecha))
                                                            <span class="text-xs text-slate-500 font-semibold truncate">• {{ date('d/m/Y', strtotime($tramite->td_fecha)) }}</span>
                                                        @endif
                                                    </div>
                                                    <form method="POST" action="{{ route('tramites.cambiar_estado') }}" class="form-cambiar-estado inline-flex items-center m-0 shrink-0" data-desc="Divorcio #{{ $tramite->id_tram_div }}">
                                                        @csrf
                                                        <input type="hidden" name="cliente_id" value="{{ $cliente->id_cliente }}">
                                                        <input type="hidden" name="tramite_tipo" value="divorcios">
                                                        <input type="hidden" name="tramite_id" value="{{ $tramite->id_tram_div }}">
                                                        <select name="estado" data-previous="{{ $tramite->estado ?? 'en_proceso' }}" class="estado-select text-xs font-bold py-1 px-2.5 rounded-lg border focus:ring-1 focus:ring-indigo-500 transition-all cursor-pointer {{ ($tramite->estado ?? 'en_proceso') === 'listo' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : (($tramite->estado ?? '') === 'en_revision' ? 'bg-blue-50 text-blue-700 border-blue-300' : (($tramite->estado ?? '') === 'entregado' ? 'bg-slate-100 text-slate-700 border-slate-300' : 'bg-amber-50 text-amber-800 border-amber-300')) }}">
                                                            <option value="en_proceso" {{ ($tramite->estado ?? 'en_proceso') === 'en_proceso' ? 'selected' : '' }}>🟡 En Proceso</option>
                                                            <option value="en_revision" {{ ($tramite->estado ?? '') === 'en_revision' ? 'selected' : '' }}>🔵 En Revisión</option>
                                                            <option value="listo" {{ ($tramite->estado ?? '') === 'listo' ? 'selected' : '' }}>🟢 Listo para entregar</option>
                                                            <option value="entregado" {{ ($tramite->estado ?? '') === 'entregado' ? 'selected' : '' }}>⚪ Entregado</option>
                                                        </select>
                                                    </form>
                                                </div>

                                                <!-- Fila 2: Detalles Cónyuge / Motivo -->
                                                @if(!empty($tramite->td_nombre_c))
                                                    <p class="text-xs text-slate-700 font-medium truncate">
                                                        Cónyuge: <strong class="text-slate-900 font-bold">{{ $tramite->td_nombre_c }}</strong>
                                                    </p>
                                                @endif

                                                <!-- Fila 3: Valor / Saldo a la izquierda, Botones a la derecha -->
                                                <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between gap-2">
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="text-slate-800 font-bold text-xs">${{ number_format($tramite->td_valor ?? 0, 2) }}</span>
                                                        @if(($tramite->td_saldo ?? 0) > 0)
                                                            <span class="text-rose-700 bg-rose-50 px-2 py-0.5 rounded-md font-bold text-[11px] border border-rose-200">Saldo: ${{ number_format($tramite->td_saldo, 2) }}</span>
                                                        @else
                                                            <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md font-bold text-[11px] border border-emerald-200">✓ Pagado</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center gap-1.5 shrink-0 flex-wrap">
                                                        @if(($tramite->td_saldo ?? 0) > 0)
                                                            <button type="button" onclick="abrirModalCobro('divorcios', {{ $tramite->id_tram_div }}, 'Divorcio #{{ $tramite->id_tram_div }}', {{ $tramite->td_saldo }})" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition-all cursor-pointer">
                                                                <span>💵 Cobrar</span>
                                                            </button>
                                                        @endif
                                                        @if(!empty($tramite->documento_pdf))
                                                            <a href="{{ asset($tramite->documento_pdf) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold shadow-sm border transition-colors bg-emerald-50 text-emerald-800 border-emerald-300 hover:bg-emerald-600 hover:text-white" title="Ver Documento PDF Listo">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                <span>PDF Final</span>
                                                            </a>
                                                        @endif
                                                        <a href="{{ route('divorcios.show', $tramite->id_tram_div) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold shadow-sm border transition-colors bg-rose-50 text-rose-800 border-rose-200 hover:bg-rose-100" title="Imprimir Comprobante">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                            <span>Hoja</span>
                                                        </a>
                                                    </div>
                                                </div>

                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="py-6 text-center text-gray-400 font-medium">
                                        <p class="text-xs">Sin registros de divorcio</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Impuestos -->
                    <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden flex flex-col justify-between">
                        <div>
                            <div class="border-b border-emerald-100 px-5 py-4 flex justify-between items-center" style="background-color: #ecfdf5;">
                                <h3 class="text-base font-extrabold text-emerald-950 flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-emerald-600 inline-block shadow-sm"></span> Impuestos
                                </h3>
                                <span class="text-xs px-2.5 py-0.5 rounded-full font-extrabold" style="background-color: #d1fae5; color: #065f46;">
                                    {{ $impuestos->count() }} Registros
                                </span>
                            </div>
                            <div class="p-4 text-sm">
                                @if($impuestos->count() > 0)
                                    <ul class="space-y-3">
                                        @foreach($impuestos as $tramite)
                                            <li class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-emerald-300 hover:shadow-sm transition-all space-y-2.5">
                                                
                                                <!-- Fila 1: ID, Fecha y Selector de Estado -->
                                                <div class="flex items-center justify-between gap-2">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <span class="font-black text-slate-900 text-sm">#{{ str_pad($tramite->id_tram_impuestos, 5, '0', STR_PAD_LEFT) }}</span>
                                                        @if(!empty($tramite->ti_fecha))
                                                             <span class="text-xs text-slate-500 font-semibold truncate">• {{ date('d/m/Y', strtotime($tramite->ti_fecha)) }}</span>
                                                        @endif
                                                    </div>
                                                    <form method="POST" action="{{ route('tramites.cambiar_estado') }}" class="form-cambiar-estado inline-flex items-center m-0 shrink-0" data-desc="Impuestos #{{ $tramite->id_tram_impuestos }}">
                                                        @csrf
                                                        <input type="hidden" name="cliente_id" value="{{ $cliente->id_cliente }}">
                                                        <input type="hidden" name="tramite_tipo" value="impuestos">
                                                        <input type="hidden" name="tramite_id" value="{{ $tramite->id_tram_impuestos }}">
                                                        <select name="estado" data-previous="{{ $tramite->estado ?? 'en_proceso' }}" class="estado-select text-xs font-bold py-1 px-2.5 rounded-lg border focus:ring-1 focus:ring-indigo-500 transition-all cursor-pointer {{ ($tramite->estado ?? 'en_proceso') === 'listo' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : (($tramite->estado ?? '') === 'en_revision' ? 'bg-blue-50 text-blue-700 border-blue-300' : (($tramite->estado ?? '') === 'entregado' ? 'bg-slate-100 text-slate-700 border-slate-300' : 'bg-amber-50 text-amber-800 border-amber-300')) }}">
                                                            <option value="en_proceso" {{ ($tramite->estado ?? 'en_proceso') === 'en_proceso' ? 'selected' : '' }}>🟡 En Proceso</option>
                                                            <option value="en_revision" {{ ($tramite->estado ?? '') === 'en_revision' ? 'selected' : '' }}>🔵 En Revisión</option>
                                                            <option value="listo" {{ ($tramite->estado ?? '') === 'listo' ? 'selected' : '' }}>🟢 Listo para entregar</option>
                                                            <option value="entregado" {{ ($tramite->estado ?? '') === 'entregado' ? 'selected' : '' }}>⚪ Entregado</option>
                                                        </select>
                                                    </form>
                                                </div>

                                                <!-- Fila 2: Año Reporte -->
                                                @if(!empty($tramite->ti_anio_reporte))
                                                    <p class="text-xs text-slate-700 font-medium truncate">
                                                        Año Reporte: <strong class="text-slate-900 font-bold">{{ $tramite->ti_anio_reporte }}</strong>
                                                    </p>
                                                @endif

                                                <!-- Fila 3: Valor / Saldo a la izquierda, Botones a la derecha -->
                                                <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between gap-2">
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="text-slate-800 font-bold text-xs">${{ number_format($tramite->ti_costo_tramite ?? 0, 2) }}</span>
                                                        @if(($tramite->ti_saldo ?? 0) > 0)
                                                            <span class="text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md font-bold text-[11px] border border-amber-200">Saldo: ${{ number_format($tramite->ti_saldo, 2) }}</span>
                                                        @else
                                                            <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md font-bold text-[11px] border border-emerald-200">✓ Pagado</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center gap-1.5 shrink-0 flex-wrap">
                                                        @if(($tramite->ti_saldo ?? 0) > 0)
                                                            <button type="button" onclick="abrirModalCobro('impuestos', {{ $tramite->id_tram_impuestos }}, 'Impuestos #{{ $tramite->id_tram_impuestos }}', {{ $tramite->ti_saldo }})" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition-all cursor-pointer">
                                                                <span>💵 Cobrar</span>
                                                            </button>
                                                        @endif
                                                        @if(!empty($tramite->documento_pdf))
                                                            <a href="{{ asset($tramite->documento_pdf) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold shadow-sm border transition-colors bg-emerald-50 text-emerald-800 border-emerald-300 hover:bg-emerald-600 hover:text-white" title="Ver Documento PDF Listo">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                <span>PDF Final</span>
                                                            </a>
                                                        @endif
                                                        <a href="{{ route('impuestos.show', $tramite->id_tram_impuestos) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold shadow-sm border transition-colors bg-emerald-50 text-emerald-800 border-emerald-200 hover:bg-emerald-100" title="Imprimir Formulario">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                            <span>Hoja</span>
                                                        </a>
                                                    </div>
                                                </div>

                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="py-6 text-center text-gray-400 font-medium">
                                        <p class="text-xs">Sin registros de impuestos</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Poderes -->
                    <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden flex flex-col justify-between">
                        <div>
                            <div class="border-b border-amber-100 px-5 py-4 flex justify-between items-center" style="background-color: #fffbeb;">
                                <h3 class="text-base font-extrabold text-amber-950 flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-amber-600 inline-block shadow-sm"></span> Poderes
                                </h3>
                                <span class="text-xs px-2.5 py-0.5 rounded-full font-extrabold" style="background-color: #fef3c7; color: #92400e;">
                                    {{ $poderes->count() }} Registros
                                </span>
                            </div>
                            <div class="p-4 text-sm">
                                @if($poderes->count() > 0)
                                    <ul class="space-y-3">
                                        @foreach($poderes as $tramite)
                                            <li class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-amber-300 hover:shadow-sm transition-all space-y-2.5">
                                                
                                                <!-- Fila 1: ID, Fecha y Selector de Estado -->
                                                <div class="flex items-center justify-between gap-2">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <span class="font-black text-slate-900 text-sm">#{{ str_pad($tramite->id_tram_poderes, 5, '0', STR_PAD_LEFT) }}</span>
                                                        @if(!empty($tramite->tp_fecha))
                                                            <span class="text-xs text-slate-500 font-semibold truncate">• {{ date('d/m/Y', strtotime($tramite->tp_fecha)) }}</span>
                                                        @endif
                                                    </div>
                                                    <form method="POST" action="{{ route('tramites.cambiar_estado') }}" class="form-cambiar-estado inline-flex items-center m-0 shrink-0" data-desc="Poder #{{ $tramite->id_tram_poderes }} ({{ $tramite->tp_razon_otorga_poder ?: 'General' }})">
                                                        @csrf
                                                        <input type="hidden" name="cliente_id" value="{{ $cliente->id_cliente }}">
                                                        <input type="hidden" name="tramite_tipo" value="poderes">
                                                        <input type="hidden" name="tramite_id" value="{{ $tramite->id_tram_poderes }}">
                                                        <select name="estado" data-previous="{{ $tramite->estado ?? 'en_proceso' }}" class="estado-select text-xs font-bold py-1 px-2.5 rounded-lg border focus:ring-1 focus:ring-indigo-500 transition-all cursor-pointer {{ ($tramite->estado ?? 'en_proceso') === 'listo' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : (($tramite->estado ?? '') === 'en_revision' ? 'bg-blue-50 text-blue-700 border-blue-300' : (($tramite->estado ?? '') === 'entregado' ? 'bg-slate-100 text-slate-700 border-slate-300' : 'bg-amber-50 text-amber-800 border-amber-300')) }}">
                                                            <option value="en_proceso" {{ ($tramite->estado ?? 'en_proceso') === 'en_proceso' ? 'selected' : '' }}>🟡 En Proceso</option>
                                                            <option value="en_revision" {{ ($tramite->estado ?? '') === 'en_revision' ? 'selected' : '' }}>🔵 En Revisión</option>
                                                            <option value="listo" {{ ($tramite->estado ?? '') === 'listo' ? 'selected' : '' }}>🟢 Listo para entregar</option>
                                                            <option value="entregado" {{ ($tramite->estado ?? '') === 'entregado' ? 'selected' : '' }}>⚪ Entregado</option>
                                                        </select>
                                                    </form>
                                                </div>

                                                <!-- Fila 2: Otorga / Motivo -->
                                                @if(!empty($tramite->tp_nombres_otorga_poder))
                                                    <p class="text-xs text-slate-700 font-medium truncate">
                                                        Otorga: <strong class="text-slate-900 font-bold">{{ $tramite->tp_nombres_otorga_poder }}</strong>
                                                    </p>
                                                @endif
                                                @if(!empty($tramite->tp_razon_otorga_poder))
                                                    <p class="text-xs text-slate-500 truncate">
                                                        Motivo: {{ $tramite->tp_razon_otorga_poder }}
                                                    </p>
                                                @endif

                                                <!-- Fila 3: Valor / Saldo a la izquierda, Botones a la derecha -->
                                                <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between gap-2">
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="text-slate-800 font-bold text-xs">${{ number_format($tramite->tp_costo_tramite ?? 0, 2) }}</span>
                                                        @if(($tramite->tp_saldo ?? 0) > 0)
                                                            <span class="text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md font-bold text-[11px] border border-amber-200">Saldo: ${{ number_format($tramite->tp_saldo, 2) }}</span>
                                                        @else
                                                            <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md font-bold text-[11px] border border-emerald-200">✓ Pagado</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center gap-1.5 shrink-0 flex-wrap">
                                                        @if(($tramite->tp_saldo ?? 0) > 0)
                                                            <button type="button" onclick="abrirModalCobro('poderes', {{ $tramite->id_tram_poderes }}, 'Poder #{{ $tramite->id_tram_poderes }}', {{ $tramite->tp_saldo }})" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition-all cursor-pointer">
                                                                <span>💵 Cobrar</span>
                                                            </button>
                                                        @endif
                                                        @if(!empty($tramite->documento_pdf))
                                                            <a href="{{ asset($tramite->documento_pdf) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold shadow-sm border transition-colors bg-emerald-50 text-emerald-800 border-emerald-300 hover:bg-emerald-600 hover:text-white" title="Ver Documento PDF Listo">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                <span>PDF Final</span>
                                                            </a>
                                                        @endif
                                                        <a href="{{ route('poderes.show', $tramite->id_tram_poderes) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold shadow-sm border transition-colors bg-amber-50 text-amber-800 border-amber-200 hover:bg-amber-100" title="Imprimir Formulario">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                            <span>Hoja</span>
                                                        </a>
                                                    </div>
                                                </div>

                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="py-6 text-center text-gray-400 font-medium">
                                        <p class="text-xs">Sin registros de poderes</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Sección: Historial de Documentos Notariales Emitidos desde Plantillas (Expediente Digital) -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden mt-8">
                    <div class="border-b border-indigo-100 px-6 py-4 flex justify-between items-center bg-indigo-50/50">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-indigo-600 inline-block shadow-sm"></span>
                                Historial de Documentos Notariales Emitidos (Expediente Digital)
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Documentos generados y firmados para {{ $cliente->c_nombre }} {{ $cliente->c_apellido }}</p>
                        </div>
                        <span class="text-xs px-3 py-1 rounded-full font-extrabold bg-indigo-100 text-indigo-700">
                            {{ $documentosGenerados->count() }} Documentos
                        </span>
                    </div>

                    <div class="p-6">
                        @if($documentosGenerados->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($documentosGenerados as $doc)
                                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-indigo-300 hover:shadow-md transition-all flex flex-col justify-between space-y-3">
                                        <div>
                                            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium mb-1">
                                                <span>{{ $doc->created_at->format('d/m/Y H:i A') }}</span>
                                                <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-600 font-bold text-[10px]">Por: {{ $doc->usuario_creador ?? 'Sistema' }}</span>
                                            </div>
                                            <h4 class="text-sm font-bold text-slate-800 leading-tight">
                                                {{ $doc->titulo_documento }}
                                            </h4>
                                            <p class="text-xs text-slate-500 mt-1 line-clamp-2">
                                                {{ strip_tags($doc->contenido_html) }}
                                            </p>
                                        </div>

                                        <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                                            @if($doc->plantilla_id)
                                                <a href="{{ route('plantillas.generar', ['plantilla' => $doc->plantilla_id, 'cliente' => $cliente->id_cliente]) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 shadow-xs">
                                                    Abrir / Reimprimir
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <p class="text-xs font-semibold text-slate-600">No hay documentos notariales generados en el expediente de este cliente.</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Puedes emitir uno desde el catálogo de Plantillas Notariales.</p>
                                <a href="{{ route('plantillas.index') }}" class="inline-block mt-3 px-3.5 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-bold shadow-xs hover:bg-indigo-700">
                                    Ir a Catálogo de Plantillas
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal: Cobrar Trámite en Ventanilla / Caja -->
    <div id="modalCobrarTramite" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-cobro-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="cerrarModalCobro()"></div>

            <div class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                <form method="POST" action="{{ route('tramites.cobrar') }}" id="formCobroTramite">
                    @csrf
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id_cliente }}">
                    <input type="hidden" name="tramite_tipo" id="cobro_tramite_tipo" value="">
                    <input type="hidden" name="tramite_id" id="cobro_tramite_id" value="">

                    <!-- Header Modal -->
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-6 py-5 text-white flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-white/15 backdrop-blur-md rounded-xl border border-white/20">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black tracking-tight" id="modal-cobro-title">Cobro de Trámite en Caja</h3>
                                <p class="text-xs text-emerald-100 font-medium" id="cobro_tramite_titulo">Cargando...</p>
                            </div>
                        </div>
                        <button type="button" onclick="cerrarModalCobro()" class="text-white/70 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition-all cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Body Modal -->
                    <div class="p-6 space-y-5">
                        
                        <!-- Caja Status -->
                        @if($cajaAbierta)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                                <div class="flex items-center gap-2 text-xs font-bold text-emerald-800">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Caja Activa #{{ $cajaAbierta->id }}</span>
                                </div>
                                <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded-md">
                                    {{ Auth::user()->name }}
                                </span>
                            </div>
                        @else
                            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 font-semibold flex items-center gap-2">
                                <span>⚠️</span>
                                <span>No tienes una caja abierta. Se requiere abrir caja para registrar este cobro.</span>
                            </div>
                        @endif

                        <!-- Resumen Deuda / Saldo -->
                        <div class="grid grid-cols-2 gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Cliente</span>
                                <p class="text-xs font-black text-slate-800 truncate">{{ $cliente->c_nombre }} {{ $cliente->c_apellido }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Saldo Pendiente</span>
                                <p class="text-lg font-black text-amber-600">$<span id="cobro_saldo_pendiente_display">0.00</span></p>
                            </div>
                        </div>

                        <!-- Monto a Cobrar -->
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <label for="cobro_monto_pago" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Monto a Cobrar ($) <span class="text-red-500">*</span></label>
                                <button type="button" onclick="setCobroTotal()" class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 hover:bg-emerald-100 transition-colors cursor-pointer">
                                    Cobrar Todo
                                </button>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold">$</div>
                                <input type="number" step="0.01" min="0.01" name="monto_pago" id="cobro_monto_pago" required
                                       class="w-full pl-8 pr-4 py-3 text-lg font-black text-emerald-700 bg-white border border-slate-300 rounded-xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                            </div>
                        </div>

                        <!-- Método de Pago -->
                        <div>
                            <label for="cobro_metodo_pago" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Método de Pago</label>
                            <select name="metodo_pago" id="cobro_metodo_pago" onchange="toggleCobroPaymentFields()"
                                    class="w-full text-sm bg-white border border-slate-300 rounded-xl px-4 py-2.5 font-bold text-slate-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                <option value="Efectivo">💵 Efectivo</option>
                                <option value="Tarjeta">💳 Tarjeta (POS / Débito / Crédito)</option>
                                <option value="Transferencia">🏦 Transferencia Bancaria</option>
                                <option value="Zelle">⚡ Zelle</option>
                                <option value="Cheque">📜 Cheque</option>
                            </select>
                        </div>

                        <!-- Selector de Tarjeta (Condicional) -->
                        <div id="cobro_campo_tarjeta" class="hidden">
                            <label for="cobro_tarjeta_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tarjeta / Terminal POS</label>
                            <select name="tarjeta_id" id="cobro_tarjeta_id" class="w-full text-sm bg-white border border-slate-300 rounded-xl px-4 py-2.5 font-medium text-slate-800">
                                <option value="">Seleccione Tarjeta / POS...</option>
                                @foreach($tarjetas as $tar)
                                    <option value="{{ $tar->id }}">{{ $tar->nombre }} ({{ $tar->franquicia }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Selector de Banco (Condicional) -->
                        <div id="cobro_campo_banco" class="hidden">
                            <label for="cobro_banco_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Banco de Destino / Cuenta</label>
                            <select name="banco_id" id="cobro_banco_id" class="w-full text-sm bg-white border border-slate-300 rounded-xl px-4 py-2.5 font-medium text-slate-800">
                                <option value="">Seleccione Banco...</option>
                                @foreach($bancos as $ban)
                                    <option value="{{ $ban->id }}">{{ $ban->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Nro de Referencia -->
                        <div id="cobro_campo_referencia" class="hidden">
                            <label for="cobro_numero_referencia" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">N° Voucher / Ref / Confirmación</label>
                            <input type="text" name="numero_referencia" id="cobro_numero_referencia" placeholder="Ej: Aut #893412 / Ref Zelle #0482"
                                   class="w-full text-sm bg-white border border-slate-300 rounded-xl px-4 py-2.5 font-medium text-slate-800">
                        </div>

                    </div>

                    <!-- Footer Modal -->
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-3">
                        <button type="button" onclick="cerrarModalCobro()" class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer">
                            Cancelar
                        </button>
                        @if($tieneCajaAbierta)
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-extrabold shadow-md shadow-emerald-200 transition-all cursor-pointer">
                                <span>💵 Confirmar Cobro e Imprimir</span>
                            </button>
                        @else
                            <button type="button" onclick="cerrarModalCobro(); document.getElementById('modalCajaRequerida').classList.remove('hidden');" class="inline-flex items-center gap-2 px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-extrabold shadow-md transition-all cursor-pointer">
                                <span>⚠️ Abrir Caja para Cobrar</span>
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Apertura de Caja Requerida -->
    <div id="modalCajaRequerida" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="cerrarModalCajaRequerida()"></div>

            <div class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 border-2 border-amber-300">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-2xl bg-amber-100 text-amber-600 mb-4 ring-8 ring-amber-50">
                        <svg class="h-8 w-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-black text-slate-900" id="modal-title">
                        Apertura de Caja Requerida
                    </h3>
                    <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                        No puedes registrar cobros directos porque <strong>no tienes una caja de atención abierta</strong>.
                    </p>
                    <div class="mt-3 p-3 bg-amber-50 rounded-xl border border-amber-200 text-left text-[11px] text-amber-900 font-semibold space-y-1">
                        <p>✓ Abre tu caja con tu fondo inicial.</p>
                        <p>✓ Esto garantiza que tus cobros y abonos se registren en tu sesión de cuadre.</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row gap-2.5">
                    <a href="{{ route('cajas.index') }}" class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-amber-600 px-4 py-3 text-xs font-black text-white shadow-md shadow-amber-300 hover:bg-amber-700 transition-all">
                        <span>Ir a Abrir Caja</span> &rarr;
                    </a>
                    <button type="button" onclick="cerrarModalCajaRequerida()" class="w-full inline-flex justify-center rounded-xl bg-slate-100 px-4 py-3 text-xs font-bold text-slate-700 hover:bg-slate-200 transition-all cursor-pointer">
                        Entendido, cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Cargar Documento PDF al pasar a "Listo para entregar" -->
    <div id="modalSubirPdfListo" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-pdf-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="cerrarModalPdfListo()"></div>

            <div class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                <form id="formSubirPdfListo" onsubmit="enviarPdfListo(event)">
                    @csrf
                    <input type="hidden" name="cliente_id" id="pdf_cliente_id" value="">
                    <input type="hidden" name="tramite_tipo" id="pdf_tramite_tipo" value="">
                    <input type="hidden" name="tramite_id" id="pdf_tramite_id" value="">
                    <input type="hidden" name="estado" value="listo">

                    <!-- Header Modal -->
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-6 py-5 text-white flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-white/15 backdrop-blur-md rounded-xl border border-white/20">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black tracking-tight" id="modal-pdf-title">Cargar Documento Final PDF</h3>
                                <p class="text-xs text-emerald-100 font-medium" id="pdf_tramite_desc">Listo para entregar</p>
                            </div>
                        </div>
                        <button type="button" onclick="cerrarModalPdfListo()" class="text-white/70 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition-all cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Body Modal -->
                    <div class="p-6 space-y-4">
                        <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 leading-relaxed font-medium flex items-start gap-2.5">
                            <span class="text-base leading-none">ℹ️</span>
                            <div>
                                Para cambiar el estado a <strong>Listo para entregar</strong>, es obligatorio cargar el <strong>documento final en PDF</strong> ya redactado y listo para que el cliente lo descargue o retire.
                            </div>
                        </div>

                        <!-- Dropzone / Input de Archivo -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Archivo PDF del Documento <span class="text-red-500">*</span></label>
                            <div id="dropzonePdfListo" onclick="document.getElementById('inputDocumentoPdf').click()" 
                                 class="border-2 border-dashed border-slate-300 hover:border-emerald-500 bg-slate-50 hover:bg-emerald-50/40 rounded-2xl p-6 text-center cursor-pointer transition-all">
                                <input type="file" id="inputDocumentoPdf" name="documento_pdf" accept="application/pdf" class="hidden" onchange="actualizarPreviewPdf(this)">
                                
                                <div id="previewPdfEmpty" class="space-y-2">
                                    <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shadow-xs">
                                        📄
                                    </div>
                                    <div class="text-xs font-bold text-slate-700">
                                        <span class="text-emerald-700 underline">Haz clic para seleccionar</span> o arrastra el archivo aquí
                                    </div>
                                    <p class="text-[11px] text-slate-400 font-medium">Solo archivos PDF (Máx. 25 MB)</p>
                                </div>

                                <div id="previewPdfSelected" class="hidden space-y-2">
                                    <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-xl shadow-md">
                                        ✓
                                    </div>
                                    <div class="text-xs font-black text-slate-900 truncate max-w-xs mx-auto" id="nombreArchivoPdf">
                                        documento.pdf
                                    </div>
                                    <p class="text-[11px] text-emerald-600 font-bold" id="tamanoArchivoPdf">0 KB</p>
                                    <button type="button" onclick="event.stopPropagation(); limpiarArchivoPdf();" class="text-[11px] font-bold text-rose-600 hover:underline">
                                        Cambiar archivo
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="pdfErrorMsg" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs font-bold text-rose-700"></div>
                    </div>

                    <!-- Footer Modal -->
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-3">
                        <button type="button" onclick="cerrarModalPdfListo()" class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" id="btnConfirmarPdf" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-extrabold shadow-md shadow-emerald-200 transition-all cursor-pointer">
                            <span>🟢 Subir PDF y Marcar Listo</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Contenedor Flotante de Toast Notifications AJAX -->
    <div id="toastNotificationContainer" class="fixed bottom-6 right-6 z-50 flex flex-col gap-3 pointer-events-none"></div>

    <!-- Librería SortableJS y Lógica de Persistencia de Orden + AJAX de Estados -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        const tieneCajaAbierta = @json($tieneCajaAbierta ?? false);
        let currentTramiteSaldo = 0;
        let activeEstadoSelect = null;
        let activeEstadoForm = null;

        function abrirModalCobro(tramiteTipo, tramiteId, tramiteTitulo, saldoPendiente) {
            if (!tieneCajaAbierta) {
                document.getElementById('modalCajaRequerida').classList.remove('hidden');
                return;
            }

            currentTramiteSaldo = parseFloat(saldoPendiente) || 0;
            document.getElementById('cobro_tramite_tipo').value = tramiteTipo;
            document.getElementById('cobro_tramite_id').value = tramiteId;
            document.getElementById('cobro_tramite_titulo').innerText = tramiteTitulo;
            document.getElementById('cobro_saldo_pendiente_display').innerText = currentTramiteSaldo.toFixed(2);
            
            const inputMonto = document.getElementById('cobro_monto_pago');
            inputMonto.value = currentTramiteSaldo.toFixed(2);
            inputMonto.max = currentTramiteSaldo.toFixed(2);

            document.getElementById('cobro_metodo_pago').value = 'Efectivo';
            toggleCobroPaymentFields();

            document.getElementById('modalCobrarTramite').classList.remove('hidden');
        }

        function cerrarModalCobro() {
            document.getElementById('modalCobrarTramite').classList.add('hidden');
        }

        function setCobroTotal() {
            document.getElementById('cobro_monto_pago').value = currentTramiteSaldo.toFixed(2);
        }

        function toggleCobroPaymentFields() {
            const metodo = document.getElementById('cobro_metodo_pago').value;
            const campoTarjeta = document.getElementById('cobro_campo_tarjeta');
            const campoBanco = document.getElementById('cobro_campo_banco');
            const campoRef = document.getElementById('cobro_campo_referencia');

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

        function verificarAperturaCaja(event, targetUrl) {
            return true;
        }

        function cerrarModalCajaRequerida() {
            document.getElementById('modalCajaRequerida').classList.add('hidden');
        }

        // ============================================
        // Lógica de Modal de Subida de PDF al pasar a "Listo"
        // ============================================
        function abrirModalPdfListo(selectEl, formEl) {
            activeEstadoSelect = selectEl;
            activeEstadoForm = formEl;

            const clienteId = formEl.querySelector('input[name="cliente_id"]')?.value || '';
            const tramiteTipo = formEl.querySelector('input[name="tramite_tipo"]')?.value || '';
            const tramiteId = formEl.querySelector('input[name="tramite_id"]')?.value || '';
            const desc = formEl.getAttribute('data-desc') || 'Trámite';

            document.getElementById('pdf_cliente_id').value = clienteId;
            document.getElementById('pdf_tramite_tipo').value = tramiteTipo;
            document.getElementById('pdf_tramite_id').value = tramiteId;
            document.getElementById('pdf_tramite_desc').innerText = desc;

            limpiarArchivoPdf();
            document.getElementById('pdfErrorMsg').classList.add('hidden');
            document.getElementById('pdfErrorMsg').innerText = '';

            document.getElementById('modalSubirPdfListo').classList.remove('hidden');
        }

        function cerrarModalPdfListo() {
            document.getElementById('modalSubirPdfListo').classList.add('hidden');
            // Revertir el select si el modal se cierra sin enviar
            if (activeEstadoSelect) {
                const prev = activeEstadoSelect.getAttribute('data-previous') || 'en_proceso';
                activeEstadoSelect.value = prev;
                aplicarEstiloEstado(activeEstadoSelect, prev);
                activeEstadoSelect = null;
                activeEstadoForm = null;
            }
        }

        function actualizarPreviewPdf(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
                    alert('Por favor selecciona únicamente archivos en formato PDF.');
                    input.value = '';
                    return;
                }
                document.getElementById('nombreArchivoPdf').innerText = file.name;
                const sizeKb = (file.size / 1024).toFixed(1);
                document.getElementById('tamanoArchivoPdf').innerText = (sizeKb > 1024 ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB');
                document.getElementById('previewPdfEmpty').classList.add('hidden');
                document.getElementById('previewPdfSelected').classList.remove('hidden');
            }
        }

        function limpiarArchivoPdf() {
            const input = document.getElementById('inputDocumentoPdf');
            if (input) input.value = '';
            document.getElementById('previewPdfEmpty').classList.remove('hidden');
            document.getElementById('previewPdfSelected').classList.add('hidden');
        }

        async function enviarPdfListo(event) {
            event.preventDefault();
            const input = document.getElementById('inputDocumentoPdf');
            const errorBox = document.getElementById('pdfErrorMsg');
            const btnSubmit = document.getElementById('btnConfirmarPdf');

            if (!input.files || input.files.length === 0) {
                errorBox.innerText = '⚠️ Debes seleccionar o arrastrar el archivo PDF final del trámite.';
                errorBox.classList.remove('hidden');
                return;
            }

            const modalForm = document.getElementById('formSubirPdfListo');
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
                    if (activeEstadoSelect) {
                        activeEstadoSelect.setAttribute('data-previous', 'listo');
                        activeEstadoSelect.value = 'listo';
                        aplicarEstiloEstado(activeEstadoSelect, 'listo');
                    }
                    mostrarToast('🎉 ¡Trámite marcado como LISTO PARA ENTREGAR! Documento PDF cargado y alerta enviada a la App.', 'success');
                    
                    // Cerrar modal sin revertir
                    activeEstadoSelect = null;
                    activeEstadoForm = null;
                    document.getElementById('modalSubirPdfListo').classList.add('hidden');
                    
                    // Recargar ligeramente para mostrar el botón "PDF Final" actualizado
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Error al cargar el documento.');
                }
            } catch (error) {
                console.error('Error al subir PDF listo:', error);
                errorBox.innerText = '⚠️ ' + (error.message || 'Error al procesar el archivo.');
                errorBox.classList.remove('hidden');
            } finally {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<span>🟢 Subir PDF y Marcar Listo</span>';
            }
        }

        // Drag and Drop en Dropzone
        document.addEventListener('DOMContentLoaded', function () {
            const dropzone = document.getElementById('dropzonePdfListo');
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
                        const input = document.getElementById('inputDocumentoPdf');
                        input.files = files;
                        actualizarPreviewPdf(input);
                    }
                }, false);
            }
        });

        // Sistema de Toast Flotante
        function mostrarToast(mensaje, tipo = 'success') {
            const container = document.getElementById('toastNotificationContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `pointer-events-auto transform transition-all duration-300 translate-y-2 opacity-0 flex items-center gap-3 px-4 py-3 rounded-2xl shadow-xl border text-xs font-bold ${
                tipo === 'success' 
                    ? 'bg-emerald-900/95 text-emerald-100 border-emerald-700/60 shadow-emerald-950/30' 
                    : 'bg-rose-900/95 text-rose-100 border-rose-700/60 shadow-rose-950/30'
            }`;

            const icono = tipo === 'success'
                ? `<svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`
                : `<svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>`;

            toast.innerHTML = `
                ${icono}
                <div class="leading-tight">${mensaje}</div>
            `;

            container.appendChild(toast);

            // Animate In
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            });

            // Auto Remove
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-x-4');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // Manejador AJAX para cambio de estado en tiempo real
        function aplicarEstiloEstado(select, estado) {
            const colorClasses = [
                'bg-emerald-50', 'text-emerald-700', 'border-emerald-300',
                'bg-blue-50', 'text-blue-700', 'border-blue-300',
                'bg-slate-100', 'text-slate-700', 'border-slate-300',
                'bg-amber-50', 'text-amber-800', 'border-amber-300'
            ];
            select.classList.remove(...colorClasses);

            switch(estado) {
                case 'listo':
                    select.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                    break;
                case 'en_revision':
                    select.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-300');
                    break;
                case 'entregado':
                    select.classList.add('bg-slate-100', 'text-slate-700', 'border-slate-300');
                    break;
                default:
                    select.classList.add('bg-amber-50', 'text-amber-800', 'border-amber-300');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Inicializar AJAX en todos los formularios de cambio de estado
            document.querySelectorAll('.form-cambiar-estado').forEach(form => {
                const select = form.querySelector('select[name="estado"]');
                if (!select) return;

                select.addEventListener('change', async function () {
                    const nuevoEstado = this.value;
                    const prevEstado = this.getAttribute('data-previous') || 'en_proceso';

                    // Si el nuevo estado es "listo", abrir el modal para subir el documento PDF obligatorio
                    if (nuevoEstado === 'listo') {
                        abrirModalPdfListo(this, form);
                        return;
                    }

                    const formData = new FormData(form);

                    // Indicador visual de guardando
                    this.disabled = true;
                    this.style.opacity = '0.6';

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.setAttribute('data-previous', nuevoEstado);
                            aplicarEstiloEstado(this, nuevoEstado);

                            const estadoLabels = {
                                'en_proceso': '🟡 En Proceso',
                                'en_revision': '🔵 En Revisión',
                                'listo': '🟢 Listo para entregar',
                                'entregado': '⚪ Entregado'
                            };

                            mostrarToast(`✅ Estado actualizado: ${estadoLabels[nuevoEstado] || nuevoEstado}`, 'success');
                        } else {
                            throw new Error(data.message || 'No se pudo actualizar el estado.');
                        }
                    } catch (error) {
                        console.error('Error al actualizar estado:', error);
                        this.value = prevEstado;
                        aplicarEstiloEstado(this, prevEstado);
                        mostrarToast('⚠️ ' + (error.message || 'Error al actualizar el estado.'), 'error');
                    } finally {
                        this.disabled = false;
                        this.style.opacity = '1';
                    }
                });
            });

            const grid = document.getElementById('hub-tramites-grid');
            const resetBtn = document.getElementById('btnResetOrder');
            const storageKey = 'nesistema_hub_tramites_order';

            // 1. Restaurar orden guardado en localStorage
            function restoreOrder() {
                const savedOrder = localStorage.getItem(storageKey);
                if (savedOrder) {
                    try {
                        const orderArray = JSON.parse(savedOrder);
                        const items = Array.from(grid.children);
                        const itemsMap = {};
                        items.forEach(el => {
                            const id = el.getAttribute('data-id');
                            if (id) itemsMap[id] = el;
                        });

                        orderArray.forEach(id => {
                            if (itemsMap[id]) {
                                grid.appendChild(itemsMap[id]);
                            }
                        });

                        if (resetBtn) resetBtn.classList.remove('hidden');
                    } catch (e) {
                        console.error('Error restaurando orden:', e);
                    }
                }
            }

            restoreOrder();

            // 2. Inicializar SortableJS con animaciones fluidas
            if (grid) {
                new Sortable(grid, {
                    animation: 250,
                    handle: '.tramite-hub-card',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    onEnd: function () {
                        const newOrder = Array.from(grid.children)
                            .map(el => el.getAttribute('data-id'))
                            .filter(Boolean);
                        localStorage.setItem(storageKey, JSON.stringify(newOrder));
                        if (resetBtn) resetBtn.classList.remove('hidden');
                    }
                });
            }

            // 3. Función global para restablecer el orden inicial
            window.resetHubOrder = function () {
                localStorage.removeItem(storageKey);
                location.reload();
            };
        });
    </script>
</x-app-layout>
