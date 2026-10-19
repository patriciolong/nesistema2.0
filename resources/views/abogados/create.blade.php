<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('abogados.index') }}" class="p-2 bg-white text-slate-600 hover:text-slate-900 rounded-xl border border-slate-200 shadow-sm transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-slate-800 tracking-tight leading-none">
                    {{ __('Registrar Nuevo Abogado') }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">Completa los datos del profesional para el sistema interno y el directorio web</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
                
                @if ($errors->any())
                    <div class="p-4 mb-6 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-sm font-semibold">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('abogados.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Datos Principales -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombres *</label>
                            <input type="text" name="nombres" value="{{ old('nombres') }}" required class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej: Carlos Eduardo">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Apellidos *</label>
                            <input type="text" name="apellidos" value="{{ old('apellidos') }}" required class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej: Morales Gómez">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Especialidad Principal *</label>
                            <input type="text" name="especialidad" value="{{ old('especialidad') }}" required class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej: Derecho Civil, Notarial y Familia">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Colegio de Abogados / Barra</label>
                            <input type="text" name="colegio_abogados" value="{{ old('colegio_abogados') }}" class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej: Colegio de Abogados del Azuay / NY State Bar">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Matrícula / Registro Profesional</label>
                            <input type="text" name="matricula_profesional" value="{{ old('matricula_profesional') }}" class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej: Matrícula N° 01-2018-9482">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Años de Experiencia *</label>
                            <input type="number" name="experiencia_anios" value="{{ old('experiencia_anios', 5) }}" min="0" max="60" required class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ciudad Sede *</label>
                            <input type="text" name="ciudad" value="{{ old('ciudad', 'New York, NY') }}" required class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej: New York, NY / Cuenca / Quito">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">País *</label>
                            <input type="text" name="pais" value="{{ old('pais', 'Ecuador / USA') }}" required class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej: Ecuador / USA">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Idiomas de Atención</label>
                            <input type="text" name="idiomas" value="{{ old('idiomas', 'Español, Inglés') }}" class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej: Español, Inglés, Kichwa">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Fotografía del Abogado (Avatar / Perfil)</label>
                            <input type="file" name="foto_file" accept="image/*" class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                    </div>

                    <!-- Datos de Contacto Interno (Privados para Notaría Ecuador) -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                        <h4 class="font-bold text-xs uppercase text-slate-700 tracking-wider mb-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Contacto Interno (Protegido - No se muestra al público)
                        </h4>
                        <p class="text-xs text-slate-500 mb-3">Estos datos son de uso exclusivo del personal de Notaría Ecuador para coordinar con el abogado cuando un cliente solicita contacto.</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Teléfono Directo Privado</label>
                                <input type="text" name="telefono_privado" value="{{ old('telefono_privado') }}" class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej: +1 (718) 555-0192">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email Directo Privado</label>
                                <input type="email" name="email_privado" value="{{ old('email_privado') }}" class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej: abogado@bufete.com">
                            </div>
                        </div>
                    </div>

                    <!-- Biografía / Perfil Profesional -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Perfil Profesional y Trayectoria (Visible en Web)</label>
                        <textarea name="biografia_corta" rows="3" class="w-full text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Resumen de experiencia, áreas de práctica, casos atendidos y trayectoria profesional...">{{ old('biografia_corta') }}</textarea>
                    </div>

                    <!-- Configuración de Visibilidad -->
                    <div class="flex flex-wrap items-center gap-6 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Estado de Visibilidad *</label>
                            <select name="estado" class="text-sm rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="activo" {{ old('estado', 'activo') == 'activo' ? 'selected' : '' }}>Activo (Visible en el Directorio Web)</option>
                                <option value="inactivo" {{ old('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo (Oculto)</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2 mt-4">
                            <input type="checkbox" name="destacado" id="destacado" value="1" {{ old('destacado') ? 'checked' : '' }} class="rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                            <label for="destacado" class="text-sm font-semibold text-slate-700 cursor-pointer">Marcar como Abogado Destacado en Portada</label>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                        <a href="{{ route('abogados.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all">Cancelar</a>
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-200 transition-all">Guardar y Publicar</button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>
