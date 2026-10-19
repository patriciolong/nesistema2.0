<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-2xl text-slate-800 tracking-tight leading-none">
                        Configurar Permisos & Accesos del Sistema
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Control granular de acciones (Ver, Crear, Editar, Eliminar o Todo) para <strong class="text-slate-800">{{ $user->name }}</strong>
                    </p>
                </div>
            </div>

            <!-- Botones de Acción Superiores Visibles Siempre -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <button type="submit" form="formPermisos" onclick="document.getElementById('return_to_input').value='';" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs font-black shadow-lg shadow-emerald-200 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>💾 GUARDAR CAMBIOS</span>
                </button>

                <a href="{{ route('permisos.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs font-bold shadow-sm transition-all">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Volver al Listado
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Estilos de Alta Fidelidad para Switches y Tarjetas Interactivas -->
    <style>
        .custom-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
            flex-shrink: 0;
        }
        .custom-switch input {
            opacity: 0;
            width: 0;
            height: 0;
            position: absolute;
        }
        .custom-slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #cbd5e1;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 9999px;
        }
        .custom-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: #ffffff;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.25);
        }
        .custom-switch input:checked + .custom-slider {
            background-color: #4f46e5;
        }
        .custom-switch input:checked + .custom-slider:before {
            transform: translateX(20px);
        }
        .action-card-active {
            border-color: #6366f1 !important;
            background-color: #f8fafc !important;
            box-shadow: 0 1px 4px rgba(99, 102, 241, 0.12);
        }
        .action-card-active .action-title {
            color: #4338ca !important;
        }
        .action-badge-view { background-color: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .action-badge-create { background-color: #ecfdf5; color: #047857; border-color: #a7f3d0; }
        .action-badge-edit { background-color: #fffbeb; color: #b45309; border-color: #fde68a; }
        .action-badge-delete { background-color: #fef2f2; color: #b91c1c; border-color: #fecaca; }
        .action-badge-special { background-color: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }
    </style>

    <div class="py-6 space-y-6">
        <div class="w-full max-w-[1700px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Mensajes Flash de Éxito / Alerta -->
            @if(session('success'))
                <div class="bg-emerald-50 border-2 border-emerald-500/80 text-emerald-950 p-4 rounded-2xl flex items-center justify-between shadow-lg shadow-emerald-500/10 animate-fade-in">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white font-bold flex items-center justify-center text-lg shrink-0">
                            ✓
                        </div>
                        <div>
                            <h4 class="font-extrabold text-sm text-emerald-900 leading-tight">¡Guardado Exitoso!</h4>
                            <p class="text-xs text-emerald-800 font-medium mt-0.5">{{ session('success') }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-950 text-sm font-black px-2.5 py-1.5 rounded-lg hover:bg-emerald-100 transition-colors">✕</button>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-rose-50 border-2 border-rose-500/80 text-rose-950 p-4 rounded-2xl flex items-center justify-between shadow-lg shadow-rose-500/10">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-rose-500 text-white font-bold flex items-center justify-center text-lg shrink-0">
                            !
                        </div>
                        <div>
                            <h4 class="font-extrabold text-sm text-rose-900 leading-tight">Atención</h4>
                            <p class="text-xs text-rose-800 font-medium mt-0.5">{{ session('error') }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-950 text-sm font-black px-2.5 py-1.5 rounded-lg hover:bg-rose-100 transition-colors">✕</button>
                </div>
            @endif

            <!-- Ficha de Usuario y Presets Rápidos -->
            <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 p-6 rounded-3xl text-white shadow-xl flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 border border-slate-700">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-600/40 border border-indigo-400/30 text-white font-black text-2xl flex items-center justify-center shadow-inner shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-xl font-black text-white tracking-tight">{{ $user->name }}</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold
                                {{ $user->role === 'Administrador' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : '' }}
                                {{ $user->role === 'Supervisor' ? 'bg-sky-500/20 text-sky-300 border border-sky-500/30' : '' }}
                                {{ $user->role === 'Empleado' || $user->role === 'Asesor' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : '' }}">
                                Rol: {{ $user->role }}
                            </span>
                            @if($isCustom)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    ⚙️ Matriz Personalizada Activa ({{ count($effectivePermissions) }} permisos)
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-700/50 text-slate-300 border border-slate-600">
                                    📋 Valores Predeterminados por Rol
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-300 mt-1.5 flex items-center gap-4 flex-wrap">
                            <span>✉️ {{ $user->email }}</span>
                            <span>👤 @<strong>{{ $user->username ?? $user->email }}</strong></span>
                            <span>🏢 {{ $user->office ?? 'Sin Oficina Asignada' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Barra de Plantillas Rápidas y Botón de Guardado Superior -->
                <div class="flex flex-wrap items-center gap-2 w-full xl:w-auto">
                    <button type="submit" form="formPermisos" onclick="document.getElementById('return_to_input').value='';" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white rounded-xl text-xs font-black shadow-md shadow-emerald-950/40 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>💾 GUARDAR AHORA</span>
                    </button>
                    
                    <span class="text-xs font-bold text-slate-400 hidden xl:inline-block mx-1">|</span>

                    <button type="button" onclick="marcarTodos()" class="px-3 py-2 bg-indigo-600/80 hover:bg-indigo-600 text-white rounded-xl text-xs font-bold border border-indigo-400/30 shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Todo el Sistema</span>
                    </button>
                    <button type="button" onclick="aplicarPresetSupervisor()" class="px-3 py-2 bg-sky-500/20 hover:bg-sky-500/30 text-sky-300 rounded-xl text-xs font-bold border border-sky-500/30 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <span>👔 Supervisor</span>
                    </button>
                    <button type="button" onclick="aplicarPresetCajero()" class="px-3 py-2 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 rounded-xl text-xs font-bold border border-emerald-500/30 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <span>💵 Cajero</span>
                    </button>
                    <button type="button" onclick="aplicarPresetSoloLectura()" class="px-3 py-2 bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 rounded-xl text-xs font-bold border border-amber-500/30 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <span>👁️ Solo Lectura</span>
                    </button>
                    <button type="button" onclick="desmarcarTodos()" class="px-3 py-2 bg-white/10 hover:bg-white/20 text-rose-300 rounded-xl text-xs font-bold border border-white/10 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Limpiar</span>
                    </button>
                </div>
            </div>

            <!-- Formulario Principal de Permisos -->
            <form action="{{ route('permisos.update', $user) }}" method="POST" id="formPermisos">
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="save_permissions">
                <input type="hidden" name="return_to" id="return_to_input" value="">

                <!-- Barra de Instrucción Rápida con Botón Guardar Integrado -->
                <div class="bg-indigo-50/70 border border-indigo-100 rounded-2xl p-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-3 text-xs text-indigo-900">
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">💡</span>
                        <span>
                            Cada módulo contiene sus acciones: <strong>Ver (👁️)</strong>, <strong>Crear (➕)</strong>, <strong>Editar (✏️)</strong> y <strong>Eliminar (🗑️)</strong>. Puedes activar o desactivar cada acción de forma independiente o presionar <strong>"🟢 Todo el Módulo"</strong> para seleccionar todo el bloque.
                        </span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="submit" onclick="document.getElementById('return_to_input').value='';" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl font-black text-xs transition-all shadow-md shadow-emerald-200 cursor-pointer flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>💾 GUARDAR CAMBIOS</span>
                        </button>
                    </div>
                </div>

                <!-- Grid de Módulos del Sistema (19 Módulos) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-28">
                    @foreach($availableGroups as $groupKey => $group)
                        @php
                            $totalGrupo = count($group['permisos']);
                        @endphp
                        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden flex flex-col justify-between transition-all hover:shadow-md" id="card_grupo_{{ $groupKey }}">
                            
                            <!-- Header del Módulo con Botón "Todo el Módulo" y Botón Guardar Rápido -->
                            <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/90 flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-white border border-slate-200/80 flex items-center justify-center text-xl shadow-xs shrink-0">
                                        {{ $group['icono'] }}
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 leading-tight">
                                            {{ $group['nombre'] }}
                                        </h4>
                                        <p class="text-[11px] text-slate-500 font-semibold mt-0.5">
                                            <span id="badge_count_{{ $groupKey }}" class="font-extrabold text-indigo-600">0</span> de {{ $totalGrupo }} permisos activos
                                        </p>
                                    </div>
                                </div>

                                <!-- Botón "Todo el Módulo" -->
                                <div class="flex items-center gap-2">
                                    <button type="button" 
                                            onclick="alternarTodoModulo('{{ $groupKey }}', event)" 
                                            id="btn_toggle_{{ $groupKey }}"
                                            class="inline-flex items-center gap-1.5 text-xs font-black text-indigo-700 bg-indigo-50 hover:bg-indigo-600 hover:text-white border border-indigo-200 px-3 py-1.5 rounded-xl transition-all shadow-xs cursor-pointer active:scale-95">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>🟢 Todo el Módulo</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Grilla de Acciones Granulares (4 Puntos + Extras) -->
                            <div class="p-4 sm:p-5 space-y-2.5 flex-1" id="grupo_{{ $groupKey }}">
                                @foreach($group['permisos'] as $permKey => $perm)
                                    @php
                                        $isChecked = in_array($permKey, $effectivePermissions, true);
                                        $accion = $perm['accion'] ?? 'special';
                                        
                                        $badgeClass = match($accion) {
                                            'view' => 'action-badge-view',
                                            'create' => 'action-badge-create',
                                            'edit' => 'action-badge-edit',
                                            'delete' => 'action-badge-delete',
                                            default => 'action-badge-special',
                                        };

                                        $accionIcon = match($accion) {
                                            'view' => '👁️',
                                            'create' => '➕',
                                            'edit' => '✏️',
                                            'delete' => '🗑️',
                                            'abonar', 'cobrar' => '💵',
                                            'export' => '📥',
                                            'operar', 'historial' => '⚙️',
                                            'generar' => '📄',
                                            'ia' => '✨',
                                            default => '🔒',
                                        };
                                    @endphp
                                    
                                    <div class="perm-item-card flex items-center justify-between gap-3 p-3 rounded-2xl border transition-all cursor-pointer select-none {{ $isChecked ? 'action-card-active border-indigo-300 bg-slate-50/80' : 'border-slate-200/80 bg-white hover:bg-slate-50/60' }}"
                                         onclick="clickCardItem(this, event)">
                                        
                                        <div class="flex items-center gap-3 flex-1 min-w-0 pointer-events-none">
                                            <span class="w-7 h-7 rounded-xl border flex items-center justify-center text-xs font-bold shrink-0 {{ $badgeClass }}">
                                                {{ $accionIcon }}
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="action-title text-xs font-black text-slate-800 transition-colors">
                                                        {{ $perm['nombre'] }}
                                                    </span>
                                                    <code class="text-[10px] font-mono font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200/60">
                                                        {{ $permKey }}
                                                    </code>
                                                </div>
                                                <p class="text-[11px] text-slate-500 mt-0.5 leading-snug line-clamp-1">
                                                    {{ $perm['descripcion'] }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Toggle Switch -->
                                        <label class="custom-switch shrink-0" onclick="event.stopPropagation()">
                                            <input type="checkbox" 
                                                   name="permissions[]" 
                                                   value="{{ $permKey }}" 
                                                   id="perm_{{ str_replace('.', '_', $permKey) }}"
                                                   data-group="{{ $groupKey }}"
                                                   data-key="{{ $permKey }}"
                                                   data-action="{{ $accion }}"
                                                   class="perm-checkbox"
                                                   {{ $isChecked ? 'checked' : '' }}
                                                   onchange="handleCheckboxChange(this)">
                                            <span class="custom-slider"></span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    @endforeach

                    <!-- Banner de Guardado al Final de la Grilla -->
                    <div class="col-span-1 lg:col-span-2 bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-700 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 border border-emerald-500/40">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-white/20 border border-white/30 flex items-center justify-center text-3xl shadow-inner shrink-0">
                                💾
                            </div>
                            <div>
                                <h3 class="text-xl font-black tracking-tight text-white">¿Finalizaste la configuración?</h3>
                                <p class="text-xs text-emerald-100 mt-1 max-w-xl">
                                    Haz clic en el botón de guardar para aplicar inmediatamente la matriz de permisos a la cuenta de <strong>{{ $user->name }}</strong>.
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                            <button type="submit" onclick="document.getElementById('return_to_input').value='';" class="flex-1 md:flex-none px-6 py-3.5 bg-white hover:bg-emerald-50 active:scale-95 text-emerald-900 rounded-2xl text-xs font-black shadow-xl transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>💾 GUARDAR CAMBIOS</span>
                            </button>
                            <button type="submit" onclick="document.getElementById('return_to_input').value='index';" class="flex-1 md:flex-none px-5 py-3.5 bg-emerald-900/50 hover:bg-emerald-900/80 border border-white/20 active:scale-95 text-white rounded-2xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <span>Guardar y Volver al Listado</span>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Barra Flotante de Guardado y Estado en Vivo (Sticky Bottom) -->
                <div class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/90 p-4 shadow-2xl">
                    <div class="max-w-[1700px] mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-4 text-xs font-bold text-slate-700">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Permisos activos: <strong id="contadorPermisos" class="text-indigo-600 text-sm font-black">0</strong></span>
                            </div>
                            <div class="w-36 h-2.5 bg-slate-100 rounded-full overflow-hidden border border-slate-200 hidden md:block">
                                <div id="barraProgreso" class="h-full rounded-full bg-indigo-600 transition-all duration-300" style="width: 0%;"></div>
                            </div>
                            <span id="porcentajePermisos" class="text-indigo-600 font-black hidden md:inline-block">0%</span>
                        </div>

                        <div class="flex items-center gap-2.5 w-full sm:w-auto">
                            <button type="button" onclick="confirmarRestablecer()" class="w-full sm:w-auto px-4 py-2.5 bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-all border border-slate-200 cursor-pointer">
                                Restablecer a Rol ({{ $user->role }})
                            </button>
                            <button type="submit" onclick="document.getElementById('return_to_input').value='index';" class="w-full sm:w-auto px-4 py-2.5 bg-slate-800 hover:bg-slate-900 active:scale-95 text-white rounded-xl text-xs font-bold transition-all cursor-pointer hidden md:flex items-center justify-center gap-1.5">
                                <span>Guardar y Salir</span>
                            </button>
                            <button type="submit" id="btnGuardarPermisos" onclick="document.getElementById('return_to_input').value='';" class="w-full sm:w-auto px-7 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs font-black shadow-lg shadow-emerald-200 transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>💾 GUARDAR CAMBIOS DE PERMISOS</span>
                            </button>
                        </div>
                    </div>
                </div>

            </form>

            <!-- Formulario Separado para Restablecer a Rol -->
            <form action="{{ route('permisos.update', $user) }}" method="POST" id="formResetRole" style="display: none;">
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="reset_to_role">
            </form>

        </div>
    </div>

    <!-- Lógica Interactiva en Tiempo Real -->
    <script>
        const PRESET_CAJERO = @json(\App\Models\User::getRoleDefaultPermissions('Empleado'));
        const PRESET_SUPERVISOR = @json(\App\Models\User::getRoleDefaultPermissions('Supervisor'));
        const PRESET_ADMIN = @json(\App\Models\User::getRoleDefaultPermissions('Administrador'));

        function guardarFormularioPermisos(returnTo = '') {
            const returnInput = document.getElementById('return_to_input');
            if (returnInput) {
                returnInput.value = returnTo;
            }
            const form = document.getElementById('formPermisos');
            if (form) {
                form.submit();
            }
        }

        function handleCheckboxChange(checkbox) {
            actualizarEstadoCard(checkbox);
            actualizarContador();
        }

        function clickCardItem(cardElement, event) {
            const checkbox = cardElement.querySelector('.perm-checkbox');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                handleCheckboxChange(checkbox);
            }
        }

        function actualizarEstadoCard(checkbox) {
            const card = checkbox.closest('.perm-item-card');
            if (!card) return;
            if (checkbox.checked) {
                card.classList.add('action-card-active');
            } else {
                card.classList.remove('action-card-active');
            }
        }

        function alternarTodoModulo(groupKey, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            const container = document.getElementById('grupo_' + groupKey);
            if (!container) return;

            const checkboxes = container.querySelectorAll('.perm-checkbox');
            if (!checkboxes.length) return;

            // Si hay alguno desmarcado, marcamos todos; si todos están marcados, desmarcamos todos
            const hayDesmarcado = Array.from(checkboxes).some(cb => !cb.checked);
            const nuevoEstado = hayDesmarcado;

            checkboxes.forEach(cb => {
                cb.checked = nuevoEstado;
                actualizarEstadoCard(cb);
            });

            actualizarContador();
        }

        function marcarTodos() {
            document.querySelectorAll('.perm-checkbox').forEach(cb => {
                cb.checked = true;
                actualizarEstadoCard(cb);
            });
            actualizarContador();
        }

        function desmarcarTodos() {
            document.querySelectorAll('.perm-checkbox').forEach(cb => {
                cb.checked = false;
                actualizarEstadoCard(cb);
            });
            actualizarContador();
        }

        function aplicarPresetCajero() {
            document.querySelectorAll('.perm-checkbox').forEach(cb => {
                cb.checked = PRESET_CAJERO.includes(cb.dataset.key);
                actualizarEstadoCard(cb);
            });
            actualizarContador();
        }

        function aplicarPresetSupervisor() {
            document.querySelectorAll('.perm-checkbox').forEach(cb => {
                cb.checked = PRESET_SUPERVISOR.includes(cb.dataset.key);
                actualizarEstadoCard(cb);
            });
            actualizarContador();
        }

        function aplicarPresetSoloLectura() {
            document.querySelectorAll('.perm-checkbox').forEach(cb => {
                const action = cb.dataset.action;
                const key = cb.dataset.key;
                cb.checked = action === 'view' || key.startsWith('reportes.');
                actualizarEstadoCard(cb);
            });
            actualizarContador();
        }

        function confirmarRestablecer() {
            if (confirm('¿Restablecer permisos a los valores predeterminados del rol {{ $user->role }}?')) {
                document.getElementById('formResetRole').submit();
            }
        }

        // Atajo de teclado: Ctrl + S / Cmd + S para guardar directamente
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                guardarFormularioPermisos();
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            actualizarContador();
        });

        function actualizarContador() {
            const allCheckboxes = document.querySelectorAll('.perm-checkbox');
            let activosTotal = 0;
            const total = allCheckboxes.length;

            const grupos = {};

            allCheckboxes.forEach(cb => {
                const group = cb.dataset.group;
                if (!grupos[group]) {
                    grupos[group] = { activos: 0, total: 0 };
                }
                grupos[group].total++;
                if (cb.checked) {
                    activosTotal++;
                    grupos[group].activos++;
                }
                actualizarEstadoCard(cb);
            });

            // Actualizar badges individuales de cada módulo
            Object.keys(grupos).forEach(groupKey => {
                const badgeEl = document.getElementById('badge_count_' + groupKey);
                if (badgeEl) {
                    badgeEl.textContent = grupos[groupKey].activos;
                }
                
                // Actualizar estilo del botón "Todo el Módulo" si está 100% cubierto
                const btnToggle = document.getElementById('btn_toggle_' + groupKey);
                if (btnToggle) {
                    if (grupos[groupKey].activos === grupos[groupKey].total) {
                        btnToggle.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg><span>🟢 Módulo Completo</span>`;
                        btnToggle.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                        btnToggle.classList.remove('bg-indigo-50', 'text-indigo-700', 'border-indigo-200');
                    } else {
                        btnToggle.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg><span>🟢 Todo el Módulo</span>`;
                        btnToggle.classList.remove('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                        btnToggle.classList.add('bg-indigo-50', 'text-indigo-700', 'border-indigo-200');
                    }
                }
            });

            // Actualizar barra flotante
            const contadorEl = document.getElementById('contadorPermisos');
            if (contadorEl) {
                contadorEl.textContent = `${activosTotal} de ${total}`;
            }

            const pct = total > 0 ? Math.round((activosTotal / total) * 100) : 0;
            const barraEl = document.getElementById('barraProgreso');
            if (barraEl) {
                barraEl.style.width = pct + '%';
            }
            const pctEl = document.getElementById('porcentajePermisos');
            if (pctEl) {
                pctEl.textContent = pct + '%';
            }
        }
    </script>
</x-app-layout>
