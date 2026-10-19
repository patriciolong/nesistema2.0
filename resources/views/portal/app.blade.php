<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="NESISTEMA">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/img/logo.png">

    <title>NESISTEMA 2.0 - Portal Móvil de Clientes</title>

    <!-- Google Fonts Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN para máxima velocidad y portabilidad) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#5046e5', // Vibrant Electric Royal Blue
                            600: '#4338ca',
                            700: '#3730a3',
                            800: '#312e81',
                            900: '#1e1b4b',
                            950: '#0f0d2e',
                        },
                        cream: {
                            50: '#fffef7',
                            100: '#fffbe6', // Soft Warm Ivory (Lower band)
                            200: '#fff8db',
                            300: '#fff0a6', // Pastel Cream Butter (Upper band)
                            400: '#fdee98',
                            500: '#facc15',
                            600: '#eab308',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- PDF.js from Mozilla for Universal Mobile/Desktop Canvas PDF Rendering -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        if (window.pdfjsLib) {
            try {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
            } catch(e) {}
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
        }
        /* Custom Mobile Scrollbar */
        ::-webkit-scrollbar {
            width: 3px;
            height: 3px;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        .safe-bottom {
            padding-bottom: env(safe-area-inset-bottom, 16px);
        }
        .safe-top {
            padding-top: env(safe-area-inset-top, 16px);
        }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex justify-center antialiased select-none" x-data="clientPortalApp()">

    <!-- Contenedor Principal (Simula formato App Móvil centrado en pantallas grandes o 100% full en móvil) -->
    <div class="w-full max-w-md h-full min-h-screen bg-slate-900 flex flex-col justify-between shadow-2xl relative overflow-hidden border-x border-slate-800/60">

        <!-- ========================================== -->
        <!-- PANTALLA: LOGIN (Si no está autenticado)   -->
        <!-- ========================================== -->
        <div x-show="!isAuthenticated" x-cloak class="flex-1 flex flex-col justify-between p-6 safe-top safe-bottom overflow-y-auto">
            
            <div class="pt-8 pb-4 text-center">
                <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-tr from-brand-600 via-brand-500 to-indigo-600 p-0.5 shadow-xl shadow-brand-500/30 flex items-center justify-center mb-5">
                    <div class="w-full h-full bg-slate-900/90 rounded-[22px] flex items-center justify-center">
                        <svg class="w-10 h-10 text-cream-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
                <h1 class="text-2xl font-black text-white tracking-tight">NESISTEMA</h1>
                <p class="text-xs font-bold text-cream-300 uppercase tracking-widest mt-1">Portal Móvil de Trámites</p>
                <p class="text-xs text-slate-400 mt-2 max-w-xs mx-auto">Consulta el avance de tus poderes, divorcios, impuestos y documentos notariales en tiempo real.</p>
            </div>

            <div class="space-y-4 my-auto py-4">
                <div x-show="loginError" x-text="loginError" class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-2xl text-rose-400 text-xs font-semibold text-center"></div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Cédula / Identificación o Email</label>
                    <div class="relative">
                        <input type="text" x-model="loginForm.identificacion" placeholder="Ej: 1310539588" 
                               class="w-full pl-11 pr-4 py-3.5 bg-slate-800/80 border border-slate-700/80 rounded-2xl text-sm font-semibold text-white placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Contraseña</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" x-model="loginForm.password" placeholder="Tu contraseña o número de cédula" 
                               class="w-full pl-11 pr-12 py-3.5 bg-slate-800/80 border border-slate-700/80 rounded-2xl text-sm font-semibold text-white placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white">
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-400 py-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="rememberMe" class="rounded bg-slate-800 border-slate-700 text-brand-500 focus:ring-0">
                        <span>Recordar mis datos</span>
                    </label>
                    <button type="button" @click="openWhatsApp('17187173257', 'Hola necesito ayuda para ingresar a mis trámites en NESISTEMA')" class="text-cream-300 font-semibold hover:underline cursor-pointer bg-transparent border-none p-0 text-xs">¿Necesitas ayuda?</button>
                </div>

                <button type="button" @click="handleLogin()" :disabled="isLoading" 
                        class="w-full py-4 bg-gradient-to-r from-brand-600 via-brand-500 to-indigo-600 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] text-cream-100 rounded-2xl font-black text-sm shadow-xl shadow-brand-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <span x-show="!isLoading">Ingresar a mis Trámites</span>
                    <span x-show="isLoading" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-cream-300" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Iniciando sesión...
                    </span>
                </button>
            </div>

            <div class="pt-4 text-center">
                <p class="text-[11px] text-slate-500 font-medium">NESISTEMA 2.0 • Gestión Notarial & Legal</p>
            </div>
        </div>


        <!-- ========================================== -->
        <!-- APLICACIÓN PRINCIPAL (Si está autenticado) -->
        <!-- ========================================== -->
        <div x-show="isAuthenticated" x-cloak class="flex-1 flex flex-col h-full overflow-hidden">

            <!-- HEADER SUPERIOR -->
            <header class="bg-slate-900/95 backdrop-blur-md px-5 pt-5 pb-3.5 border-b border-slate-800/80 sticky top-0 z-30 flex items-center justify-between safe-top">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-brand-600 to-brand-500 flex items-center justify-center text-cream-100 font-extrabold text-sm shadow-md shadow-brand-600/30">
                        <span x-text="cliente.nombre ? cliente.nombre.charAt(0) : 'C'"></span>
                    </div>
                    <div>
                        <h2 class="text-sm font-extrabold text-white leading-tight" x-text="cliente.nombre || 'Mi Portal'"></h2>
                        <p class="text-[11px] text-cream-300 font-semibold flex items-center gap-1 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span x-text="cliente.oficina ? 'Sede ' + cliente.oficina : 'Cliente Activo'"></span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Botón Refrescar Datos -->
                    <button type="button" @click="refreshAll()" :class="isRefreshing ? 'animate-spin text-cream-300' : 'text-slate-400 hover:text-white'" class="p-2.5 bg-slate-800 hover:bg-slate-700 active:scale-95 rounded-2xl transition-all border border-slate-700/60" title="Actualizar datos">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>

                    <!-- Botón Campana Notificaciones -->
                    <button type="button" @click="activeTab = 'notificaciones'" class="relative p-2.5 bg-slate-800 hover:bg-slate-700 active:scale-95 rounded-2xl text-slate-300 transition-all border border-slate-700/60">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span x-show="unreadNotifCount > 0" x-cloak 
                              class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-rose-500 text-white text-[10px] font-black flex items-center justify-center animate-bounce shadow-md" 
                              x-text="unreadNotifCount"></span>
                    </button>
                </div>
            </header>

            <!-- CONTENIDO DE LAS PESTAÑAS (Scrollable) -->
            <main class="flex-1 overflow-y-auto px-5 py-4 space-y-5 pb-24">

                <!-- ============================== -->
                <!-- PESTAÑA 1: INICIO (DASHBOARD) -->
                <!-- ============================== -->
                <div x-show="activeTab === 'inicio'" x-cloak class="space-y-5">
                    
                    <!-- Tarjeta de Bienvenida y Estado del Cliente -->
                    <div class="rounded-3xl p-5 bg-gradient-to-br from-brand-950 via-slate-900 to-brand-900 border border-brand-500/30 shadow-xl relative overflow-hidden">
                        <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-brand-500/10 rounded-full blur-2xl"></div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-cream-300">Portal de Trámites</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-brand-500/20 text-cream-200 border border-brand-400/20">C.I: <span x-text="cliente.identificacion"></span></span>
                        </div>

                        <div class="space-y-1 mb-4">
                            <span class="text-xs text-slate-400">Bienvenido(a),</span>
                            <h3 class="text-xl font-black text-white truncate" x-text="cliente.nombre_completo || cliente.nombre"></h3>
                            <p class="text-[11px] text-cream-200/90 font-medium flex items-center gap-1.5 pt-0.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Seguimiento de documentos notariales en tiempo real</span>
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-3 border-t border-slate-800/80">
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold block">Total Registrados</span>
                                <span class="text-xs font-black text-slate-200" x-text="(stats.total || tramites.length) + ' Trámites'"></span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold block">Sede / Oficina</span>
                                <span class="text-xs font-black text-cream-300" x-text="cliente.oficina ? 'Sede ' + cliente.oficina : 'Principal'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Métricas Rápidas de Trámites -->
                    <div class="grid grid-cols-2 gap-3">
                        <div @click="activeTab = 'tramites'; statusFilter = 'en_proceso'" class="bg-slate-800/60 hover:bg-slate-800 active:scale-95 p-4 rounded-2xl border border-slate-700/60 cursor-pointer transition-all">
                            <div class="flex items-center justify-between mb-2">
                                <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-cream-300 flex items-center justify-center font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </span>
                                <span class="text-2xl font-black text-cream-300" x-text="stats.en_proceso || 0"></span>
                            </div>
                            <span class="text-xs font-bold text-slate-200 block">En Proceso</span>
                            <span class="text-[10px] text-slate-400">En redacción notarial</span>
                        </div>

                        <div @click="activeTab = 'tramites'; statusFilter = 'listo'" class="bg-slate-800/60 hover:bg-slate-800 active:scale-95 p-4 rounded-2xl border border-emerald-500/30 cursor-pointer transition-all bg-emerald-950/10">
                            <div class="flex items-center justify-between mb-2">
                                <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </span>
                                <span class="text-2xl font-black text-emerald-400" x-text="stats.listos || 0"></span>
                            </div>
                            <span class="text-xs font-bold text-emerald-300 block">Listos para entregar</span>
                            <span class="text-[10px] text-emerald-400/80 font-medium">Disponibles con PDF listo</span>
                        </div>
                    </div>

                    <!-- Banner de Notificación Urgente si hay trámites listos -->
                    <template x-if="stats.listos > 0 || listosTramites.length > 0">
                        <div class="p-4 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 rounded-3xl shadow-xl shadow-emerald-950/40 border border-emerald-400/30 space-y-3 text-white">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center shrink-0 shadow-inner">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black uppercase tracking-wider">¡Trámite Listo para Descargar!</h4>
                                        <p class="text-[11px] text-emerald-100 mt-0.5">Tienes <span class="font-extrabold" x-text="stats.listos || listosTramites.length"></span> documento(s) oficial(es) listo(s) en PDF.</p>
                                    </div>
                                </div>
                                <button type="button" @click="activeTab = 'tramites'; statusFilter = 'listo'" class="px-3.5 py-1.5 bg-cream-100 text-slate-950 hover:bg-cream-200 active:scale-95 rounded-xl text-xs font-black shrink-0 shadow-md transition-all cursor-pointer">
                                    Ver Todos
                                </button>
                            </div>

                            <!-- Carrusel rápido de documentos listos para descarga inmediata con 1 toque -->
                            <div class="space-y-2 pt-1">
                                <template x-for="t in listosTramites.slice(0, 3)" :key="'home_ready_' + t.categoria + '_' + t.id">
                                    <div class="p-3 bg-slate-900/60 backdrop-blur-xs rounded-2xl border border-white/10 flex items-center justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs">📄</span>
                                                <h5 class="text-xs font-black text-white truncate" x-text="t.nombre"></h5>
                                            </div>
                                            <p class="text-[10px] text-emerald-200 mt-0.5 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                                <span x-text="t.fecha_completado ? 'Completado: ' + t.fecha_completado : 'Listo para entrega'"></span>
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <button type="button" @click.stop="openReviewModal(t)" 
                                                    class="px-2 py-1.5 rounded-xl text-[11px] font-black flex items-center gap-1 shadow-sm transition-all cursor-pointer border"
                                                    :class="t.calificado ? 'bg-brand-500/20 text-cream-300 border-cream-300/40 hover:bg-brand-500/30' : 'bg-gradient-to-r from-cream-300 to-cream-400 text-slate-950 hover:from-cream-200 hover:to-cream-300 border-cream-400/50'"
                                                    :title="t.calificado ? 'Ver o editar calificación' : 'Calificar atención del trámite'">
                                                <span>⭐</span>
                                                <span class="hidden sm:inline" x-text="t.calificado ? (t.resena ? t.resena.calificacion + '★' : 'Calificado') : 'Calificar'"></span>
                                            </button>
                                            <button type="button" @click.stop="downloadDocument(t)" class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-400 active:scale-95 text-slate-950 rounded-xl text-xs font-black flex items-center gap-1 shrink-0 shadow-sm transition-all cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                <span>Descargar</span>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Banner: Catálogo de Servicios Notariales -->
                    <div @click="activeTab = 'servicios'" class="p-4 bg-gradient-to-r from-brand-950 via-brand-900 to-slate-900 rounded-3xl border border-brand-500/30 hover:border-cream-300/50 cursor-pointer transition-all shadow-xl space-y-2 group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-brand-600/30 border border-cream-300/30 flex items-center justify-center text-xl shrink-0">
                                    🏛️
                                </div>
                                <div>
                                    <h4 class="text-xs font-black text-white uppercase tracking-wider">Catálogo de Servicios Notariales</h4>
                                    <p class="text-[11px] text-slate-300">Consulta los requisitos y trámites que realizamos en la notaría.</p>
                                </div>
                            </div>
                            <span class="text-cream-300 font-bold text-xs group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </div>

                    <!-- Acceso Directo por Categorías -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Servicios por Categoría</h3>
                            <button type="button" @click="activeTab = 'servicios'; servicioCategoryFilter = 'todos'" class="text-xs text-cream-300 font-bold">Ver Catálogo &rarr;</button>
                        </div>

                        <div class="grid grid-cols-3 gap-2.5">
                            <button type="button" @click="activeTab = 'servicios'; servicioCategoryFilter = 'poderes'" class="p-3 bg-slate-800/80 hover:bg-slate-800 active:scale-95 rounded-2xl border border-slate-700/60 flex flex-col items-center text-center gap-2">
                                <span class="w-9 h-9 rounded-xl bg-brand-500/20 text-cream-300 flex items-center justify-center font-bold">📜</span>
                                <span class="text-[11px] font-bold text-slate-200">Poderes</span>
                            </button>

                            <button type="button" @click="activeTab = 'servicios'; servicioCategoryFilter = 'divorcios'" class="p-3 bg-slate-800/80 hover:bg-slate-800 active:scale-95 rounded-2xl border border-slate-700/60 flex flex-col items-center text-center gap-2">
                                <span class="w-9 h-9 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center font-bold">⚖️</span>
                                <span class="text-[11px] font-bold text-slate-200">Divorcios</span>
                            </button>

                            <button type="button" @click="activeTab = 'servicios'; servicioCategoryFilter = 'impuestos'" class="p-3 bg-slate-800/80 hover:bg-slate-800 active:scale-95 rounded-2xl border border-slate-700/60 flex flex-col items-center text-center gap-2">
                                <span class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">💵</span>
                                <span class="text-[11px] font-bold text-slate-200">Impuestos</span>
                            </button>
                        </div>
                    </div>

                    <!-- Contacto / Ayuda Notarial -->
                    <div class="p-4 bg-slate-800/40 rounded-2xl border border-slate-700/50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-brand-500/20 text-cream-300 flex items-center justify-center font-bold">
                                🏢
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-200 block" x-text="cliente.oficina ? 'Oficina ' + cliente.oficina : 'Atención al Cliente'"></span>
                                <span class="text-[10px] text-slate-400">¿Dudas sobre tus documentos o servicios?</span>
                            </div>
                        </div>
                        <button type="button" @click="openWhatsApp('17187173257', 'Hola, tengo una consulta sobre los servicios y trámites. Mi cédula es: ' + (cliente.identificacion || ''))" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm cursor-pointer transition-all">
                            <span>📱 WhatsApp</span>
                        </button>
                    </div>
                </div>


                <!-- =================================== -->
                <!-- PESTAÑA: SERVICIOS DE LA NOTARÍA    -->
                <!-- =================================== -->
                <div x-show="activeTab === 'servicios'" x-cloak class="space-y-4">
                    
                    <!-- Encabezado de Servicios -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-black text-white uppercase tracking-wider flex items-center gap-2">
                                <span>🏛️</span> Servicios Notariales
                            </h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-brand-500/20 text-cream-300 border border-brand-400/20" x-text="filteredServicios.length + ' Disponibles'"></span>
                        </div>
                        <p class="text-[11px] text-slate-400">Consulta los requisitos y solicita información directa sobre todos nuestros trámites.</p>
                    </div>

                    <!-- Buscador de Servicios -->
                    <div class="relative">
                        <input type="text" x-model="searchServicioQuery" placeholder="Buscar trámite por nombre o requisitos..." 
                                class="w-full pl-10 pr-4 py-3 bg-slate-800/90 border border-slate-700/80 rounded-2xl text-xs font-semibold text-white placeholder-slate-500 focus:outline-none focus:border-brand-500 transition-all">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>

                    <!-- Filtros de Categoría para Servicios -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                        <button type="button" @click="servicioCategoryFilter = 'todos'" 
                                :class="servicioCategoryFilter === 'todos' ? 'bg-brand-600 text-cream-100 shadow-md shadow-brand-600/30' : 'bg-slate-800/80 text-slate-400 hover:text-white'"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all">
                            Todos (<span x-text="servicios.length"></span>)
                        </button>
                        <button type="button" @click="servicioCategoryFilter = 'poderes'" 
                                :class="servicioCategoryFilter === 'poderes' ? 'bg-brand-500 text-cream-100 shadow-md shadow-brand-500/30' : 'bg-slate-800/80 text-slate-400 hover:text-white'"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all">
                            📜 Poderes
                        </button>
                        <button type="button" @click="servicioCategoryFilter = 'divorcios'" 
                                :class="servicioCategoryFilter === 'divorcios' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/30' : 'bg-slate-800/80 text-slate-400 hover:text-white'"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all">
                            ⚖️ Divorcios
                        </button>
                        <button type="button" @click="servicioCategoryFilter = 'impuestos'" 
                                :class="servicioCategoryFilter === 'impuestos' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'bg-slate-800/80 text-slate-400 hover:text-white'"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all">
                            💵 Impuestos
                        </button>
                        <button type="button" @click="servicioCategoryFilter = 'formularios'" 
                                :class="servicioCategoryFilter === 'formularios' ? 'bg-brand-700 text-cream-100 shadow-md shadow-brand-700/30' : 'bg-slate-800/80 text-slate-400 hover:text-white'"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all">
                            📋 Trámites Especiales
                        </button>
                        <button type="button" @click="servicioCategoryFilter = 'varios'" 
                                :class="servicioCategoryFilter === 'varios' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'bg-slate-800/80 text-slate-400 hover:text-white'"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all">
                            ✍️ Varios
                        </button>
                    </div>

                    <!-- Lista de Tarjetas de Servicios -->
                    <div class="space-y-3">
                        <template x-for="s in filteredServicios" :key="s.id">
                            <div @click="openServicioDetail(s)" 
                                 class="bg-slate-800/80 hover:bg-slate-800 active:scale-[0.99] p-4 rounded-3xl border border-slate-700/60 cursor-pointer shadow-md transition-all space-y-3 relative overflow-hidden group">
                                
                                <div class="flex items-start gap-3">
                                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-xl shrink-0 shadow-md text-white"
                                         :style="'background:' + (s.color_gradient || 'linear-gradient(135deg, #5046e5, #3730a3)')">
                                        <span x-text="s.icono_emoji || '📄'"></span>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-1 mb-1">
                                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md text-white shadow-xs inline-block"
                                                  :style="'background:' + (s.color_gradient || '#5046e5')"
                                                  x-text="s.categoria_label"></span>
                                            
                                            <template x-if="s.origen === 'tipo_tramite'">
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-brand-500/20 text-cream-300 border border-brand-500/30">Personalizado</span>
                                            </template>
                                        </div>
                                        <h4 class="text-sm font-extrabold text-white leading-snug group-hover:text-cream-300 transition-colors" x-text="s.nombre"></h4>
                                        <p class="text-[11px] text-slate-400 line-clamp-2 mt-1 leading-relaxed" x-text="s.descripcion"></p>
                                    </div>
                                </div>

                                <!-- Footer de la tarjeta con acciones -->
                                <div class="pt-2.5 border-t border-slate-700/60 flex items-center justify-between text-[11px] gap-2">
                                    <div class="flex items-center gap-1 text-slate-400 text-[10px]">
                                        <span>📋</span>
                                        <span x-text="(s.requisitos ? s.requisitos.length : 0) + ' requisitos'"></span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <button type="button" @click.stop="solicitarServicioChat(s)" class="px-2.5 py-1 bg-brand-600 hover:bg-brand-500 active:scale-95 text-cream-100 rounded-xl text-[10px] font-black shadow-sm flex items-center gap-1 transition-all">
                                            <span>💬 Solicitar</span>
                                        </button>
                                        
                                        <span class="text-cream-300 font-bold flex items-center gap-0.5 group-hover:translate-x-0.5 transition-transform text-[11px]">
                                            <span>Requisitos &rarr;</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Estado Vacío -->
                        <div x-show="filteredServicios.length === 0" x-cloak class="p-8 text-center bg-slate-800/40 rounded-3xl border border-dashed border-slate-700/60">
                            <div class="w-12 h-12 rounded-2xl bg-slate-800 text-slate-500 flex items-center justify-center mx-auto mb-3">
                                🔍
                            </div>
                            <h4 class="text-xs font-bold text-slate-300">No se encontraron servicios</h4>
                            <p class="text-[11px] text-slate-500 mt-1">Prueba con otra palabra o categoría.</p>
                        </div>
                    </div>
                </div>


                <!-- ============================== -->
                <!-- PESTAÑA: MIS TRÁMITES (ACTIVOS) -->
                <!-- ============================== -->
                <div x-show="activeTab === 'tramites'" x-cloak class="space-y-4">
                    
                    <!-- Buscador -->
                    <div class="relative">
                        <input type="text" x-model="searchQuery" placeholder="Buscar trámite por nombre o número..." 
                                class="w-full pl-10 pr-4 py-3 bg-slate-800/90 border border-slate-700/80 rounded-2xl text-xs font-semibold text-white placeholder-slate-500 focus:outline-none focus:border-brand-500">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>

                    <!-- Filtros por Estado (Chips Horizontales) -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                        <button type="button" @click="statusFilter = 'todos'" 
                                :class="statusFilter === 'todos' ? 'bg-brand-600 text-cream-100 shadow-md shadow-brand-600/30' : 'bg-slate-800 text-slate-400 hover:text-white'"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all">
                            Todos (<span x-text="stats.total || 0"></span>)
                        </button>
                        <button type="button" @click="statusFilter = 'en_proceso'" 
                                :class="statusFilter === 'en_proceso' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'bg-slate-800 text-slate-400 hover:text-white'"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all">
                            🟡 En Proceso (<span x-text="stats.en_proceso || 0"></span>)
                        </button>
                        <button type="button" @click="statusFilter = 'listo'" 
                                :class="statusFilter === 'listo' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'bg-slate-800 text-slate-400 hover:text-white'"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all">
                            🟢 Listos para entregar (<span x-text="stats.listos || 0"></span>)
                        </button>
                        <button type="button" @click="statusFilter = 'en_revision'" 
                                :class="statusFilter === 'en_revision' ? 'bg-brand-500 text-cream-100 shadow-md shadow-brand-500/30' : 'bg-slate-800 text-slate-400 hover:text-white'"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all">
                            🔵 En Revisión
                        </button>
                    </div>

                    <!-- Filtros por Categoría -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                        <button type="button" @click="categoryFilter = 'todas'" 
                                :class="categoryFilter === 'todas' ? 'border-cream-300 text-cream-300 bg-brand-500/20' : 'border-slate-700/60 text-slate-400 bg-slate-800/40'"
                                class="px-3 py-1 rounded-lg text-[11px] font-bold border whitespace-nowrap transition-all">
                            Todas
                        </button>
                        <button type="button" @click="categoryFilter = 'poderes'" 
                                :class="categoryFilter === 'poderes' ? 'border-cream-300 text-cream-300 bg-brand-500/20' : 'border-slate-700/60 text-slate-400 bg-slate-800/40'"
                                class="px-3 py-1 rounded-lg text-[11px] font-bold border whitespace-nowrap transition-all">
                            Poderes
                        </button>
                        <button type="button" @click="categoryFilter = 'divorcios'" 
                                :class="categoryFilter === 'divorcios' ? 'border-cream-300 text-cream-300 bg-brand-500/20' : 'border-slate-700/60 text-slate-400 bg-slate-800/40'"
                                class="px-3 py-1 rounded-lg text-[11px] font-bold border whitespace-nowrap transition-all">
                            Divorcios
                        </button>
                        <button type="button" @click="categoryFilter = 'impuestos'" 
                                :class="categoryFilter === 'impuestos' ? 'border-cream-300 text-cream-300 bg-brand-500/20' : 'border-slate-700/60 text-slate-400 bg-slate-800/40'"
                                class="px-3 py-1 rounded-lg text-[11px] font-bold border whitespace-nowrap transition-all">
                            Impuestos
                        </button>
                        <button type="button" @click="categoryFilter = 'varios'" 
                                :class="categoryFilter === 'varios' ? 'border-cream-300 text-cream-300 bg-brand-500/20' : 'border-slate-700/60 text-slate-400 bg-slate-800/40'"
                                class="px-3 py-1 rounded-lg text-[11px] font-bold border whitespace-nowrap transition-all">
                            Varios
                        </button>
                        <button type="button" @click="categoryFilter = 'formularios'" 
                                :class="categoryFilter === 'formularios' ? 'border-cream-300 text-cream-300 bg-brand-500/20' : 'border-slate-700/60 text-slate-400 bg-slate-800/40'"
                                class="px-3 py-1 rounded-lg text-[11px] font-bold border whitespace-nowrap transition-all">
                            Formularios
                        </button>
                    </div>

                    <!-- Lista de Tarjetas de Trámites -->
                    <div class="space-y-3">
                        <template x-for="t in filteredTramites" :key="t.categoria + '_' + t.id">
                            <div @click="openTramiteDetail(t)" 
                                 class="bg-slate-800/80 hover:bg-slate-800 active:scale-[0.99] p-4 rounded-3xl border border-slate-700/60 cursor-pointer shadow-sm transition-all space-y-3 relative overflow-hidden group">
                                
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md text-white shadow-xs inline-block mb-1" 
                                              :style="'background:' + t.color_badge" 
                                              x-text="t.categoria_label"></span>
                                        <h4 class="text-sm font-extrabold text-white leading-snug group-hover:text-cream-300 transition-colors" x-text="t.nombre"></h4>
                                        <p class="text-[11px] text-slate-400 line-clamp-1 mt-0.5" x-text="t.descripcion"></p>
                                    </div>
                                    <div class="flex flex-col items-end gap-1 shrink-0">
                                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-black border"
                                              :class="t.estado === 'listo' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : (t.estado === 'en_revision' ? 'bg-brand-500/20 text-cream-300 border-brand-500/30' : 'bg-amber-500/20 text-amber-300 border-amber-500/30')"
                                              x-text="t.estado_label"></span>
                                        <template x-if="t.documento_pdf || t.documento_pdf_url">
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-emerald-500/30 text-emerald-200 border border-emerald-500/40 flex items-center gap-1 animate-pulse">
                                                <span>📄</span> PDF Listo
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Barra de Progreso Visual -->
                                <div class="space-y-1">
                                    <div class="flex justify-between text-[10px] font-bold text-slate-400">
                                        <span>Progreso del Trámite</span>
                                        <span :class="t.porcentaje_avance === 100 ? 'text-emerald-400' : 'text-cream-300'" x-text="t.porcentaje_avance + '%'"></span>
                                    </div>
                                    <div class="w-full bg-slate-700/80 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full transition-all duration-500" 
                                             :class="t.porcentaje_avance === 100 ? 'bg-emerald-500' : 'bg-brand-500'" 
                                             :style="'width:' + t.porcentaje_avance + '%'"></div>
                                    </div>
                                </div>

                                <div class="pt-2.5 border-t border-slate-700/60 flex items-center justify-between text-[11px] gap-2">
                                    <span class="text-slate-400 shrink-0" x-text="'📅 ' + t.fecha_formateada"></span>
                                    
                                    <div class="flex items-center gap-2 flex-wrap justify-end">
                                        <!-- Botón de Encuesta / Calificación si el trámite está listo o tiene PDF -->
                                        <template x-if="t.estado === 'listo' || t.documento_pdf || t.documento_pdf_url">
                                            <button type="button" @click.stop="openReviewModal(t)" 
                                                    class="px-2.5 py-1 rounded-xl text-[11px] font-black shadow-sm flex items-center gap-1 transition-all cursor-pointer border"
                                                    :class="t.calificado ? 'bg-brand-500/20 text-cream-300 border-cream-300/40 hover:bg-brand-500/30' : 'bg-gradient-to-r from-cream-300 to-cream-400 text-slate-950 hover:from-cream-200 hover:to-cream-300 border-cream-400/50'">
                                                <span>⭐</span>
                                                <span x-text="t.calificado ? (t.resena ? t.resena.calificacion + '★ Calificado' : 'Calificado') : 'Calificar Atención'"></span>
                                            </button>
                                        </template>

                                        <!-- Botón de Descarga Directa en la tarjeta -->
                                        <template x-if="t.documento_pdf || t.documento_pdf_url">
                                            <button type="button" @click.stop="downloadDocument(t)" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white rounded-xl text-[11px] font-black shadow-md shadow-emerald-950/40 flex items-center gap-1 transition-all cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                <span>Descargar PDF</span>
                                            </button>
                                        </template>

                                        <span class="text-cream-300 font-bold text-right flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                                            <span>Ver detalles</span>
                                            <span>&rarr;</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Estado Vacío -->
                        <div x-show="filteredTramites.length === 0" x-cloak class="p-8 text-center bg-slate-800/40 rounded-3xl border border-dashed border-slate-700/60">
                            <div class="w-12 h-12 rounded-2xl bg-slate-800 text-slate-500 flex items-center justify-center mx-auto mb-3">
                                📋
                            </div>
                            <h4 class="text-xs font-bold text-slate-300">No hay trámites que coincidan</h4>
                            <p class="text-[11px] text-slate-500 mt-1">Prueba cambiando los filtros de categoría o estado.</p>
                        </div>
                    </div>
                </div>


                <!-- ============================== -->
                <!-- PESTAÑA 3: NOTIFICACIONES      -->
                <!-- ============================== -->
                <div x-show="activeTab === 'notificaciones'" x-cloak class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Bandeja de Avisos</h3>
                        <button type="button" @click="markAllAsRead()" class="text-xs text-cream-300 font-bold hover:underline">Marcar leídas</button>
                    </div>

                    <div class="space-y-2.5">
                        <template x-for="n in notificaciones" :key="n.id">
                            <div @click="handleNotifClick(n)" 
                                 class="p-4 rounded-2xl border transition-all cursor-pointer space-y-1" 
                                 :class="!n.leido ? 'bg-brand-950/40 border-brand-500/40 shadow-md shadow-brand-950/20' : 'bg-slate-800/60 border-slate-700/60 opacity-80'">
                                
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-black text-white" x-text="n.titulo"></h4>
                                    <span x-show="!n.leido" class="px-2 py-0.5 bg-brand-500 text-cream-100 text-[9px] font-black rounded-full">NUEVO</span>
                                </div>
                                <p class="text-xs text-slate-300 leading-relaxed" x-text="n.mensaje"></p>
                                <span class="text-[10px] text-slate-500 font-medium block pt-1" x-text="formatDate(n.created_at)"></span>
                            </div>
                        </template>

                        <div x-show="notificaciones.length === 0" x-cloak class="p-8 text-center bg-slate-800/40 rounded-3xl border border-slate-700/60">
                            <div class="w-12 h-12 rounded-2xl bg-slate-800 text-slate-500 flex items-center justify-center mx-auto mb-3">
                                🔔
                            </div>
                            <h4 class="text-xs font-bold text-slate-300">No tienes notificaciones pendientes</h4>
                            <p class="text-[11px] text-slate-500 mt-1">Aquí recibirás avisos inmediatos cuando tus trámites estén listos.</p>
                        </div>
                    </div>
                </div>


                <!-- ============================== -->
                <!-- PESTAÑA 4: MI CUENTA / PERFIL  -->
                <!-- ============================== -->
                <div x-show="activeTab === 'perfil'" x-cloak class="space-y-4">
                    
                    <div class="bg-slate-800/80 p-5 rounded-3xl border border-slate-700/60 text-center space-y-3">
                        <div class="w-16 h-16 rounded-3xl bg-gradient-to-tr from-brand-600 to-brand-500 mx-auto flex items-center justify-center text-cream-100 text-2xl font-black shadow-lg shadow-brand-600/30">
                            <span x-text="cliente.nombre ? cliente.nombre.charAt(0) : 'C'"></span>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-white" x-text="cliente.nombre_completo"></h3>
                            <p class="text-xs text-slate-400 mt-0.5">Identificación: <span class="font-mono font-bold text-cream-300" x-text="cliente.identificacion"></span></p>
                        </div>
                    </div>

                    <!-- Datos del Cliente -->
                    <div class="bg-slate-800/60 p-4 rounded-2xl border border-slate-700/60 space-y-2.5 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-700/50">
                            <span class="text-slate-400">Teléfono:</span>
                            <span class="font-bold text-white" x-text="cliente.telefono || 'No registrado'"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-700/50">
                            <span class="text-slate-400">Correo Electrónico:</span>
                            <span class="font-bold text-white" x-text="cliente.email || 'No registrado'"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-700/50">
                            <span class="text-slate-400">Ciudad / País:</span>
                            <span class="font-bold text-white" x-text="(cliente.ciudad || '') + ' ' + (cliente.pais || '')"></span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-slate-400">Oficina Notarial:</span>
                            <span class="font-bold text-cream-300" x-text="cliente.oficina || 'General'"></span>
                        </div>
                    </div>

                    <!-- Acciones de Cuenta -->
                    <div class="space-y-2 pt-2">
                        <button type="button" @click="showPasswordModal = true" class="w-full py-3.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-2xl text-xs font-bold border border-slate-700/80 flex items-center justify-center gap-2">
                            <span>🔑 Cambiar Contraseña de Acceso</span>
                        </button>

                        <button type="button" @click="handleLogout()" class="w-full py-3.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-2xl text-xs font-bold border border-rose-500/20 flex items-center justify-center gap-2">
                            <span>Cerrar Sesión</span>
                        </button>
                    </div>
                </div>

            </main>


            <!-- ============================== -->
            <!-- MODAL: DETALLE DEL TRÁMITE     -->
            <!-- ============================== -->
            <div x-show="selectedTramite" x-cloak class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex justify-center items-end sm:items-center p-0 sm:p-4">
                <div @click.away="selectedTramite = null" 
                     class="w-full max-w-md bg-slate-900 border-t sm:border border-slate-700/80 rounded-t-3xl sm:rounded-3xl p-5 max-h-[85vh] overflow-y-auto space-y-4 shadow-2xl safe-bottom">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <span class="text-[11px] font-black uppercase tracking-wider px-2.5 py-1 rounded-md text-white" 
                              :style="'background:' + (selectedTramite ? selectedTramite.color_badge : '#5046e5')" 
                              x-text="selectedTramite ? selectedTramite.categoria_label : ''"></span>
                        <button type="button" @click="selectedTramite = null" class="p-1.5 bg-slate-800 rounded-xl text-slate-400 hover:text-white">✕</button>
                    </div>

                    <div class="space-y-1">
                        <h3 class="text-base font-black text-white" x-text="selectedTramite ? selectedTramite.nombre : ''"></h3>
                        <p class="text-xs text-slate-400" x-text="selectedTramite ? selectedTramite.descripcion : ''"></p>
                    </div>

                    <!-- Timeline de Avance -->
                    <div class="p-4 bg-slate-800/80 rounded-2xl border border-slate-700/60 space-y-3">
                        <h4 class="text-xs font-black uppercase tracking-wider text-cream-300">Línea de Tiempo del Trámite</h4>
                        
                        <div class="space-y-3 relative pl-4 border-l-2 border-slate-700">
                            <template x-for="step in timeline" :key="step.fase">
                                <div class="relative">
                                    <div class="absolute -left-[23px] top-0.5 w-4 h-4 rounded-full border-2" 
                                         :class="step.completado ? 'bg-emerald-500 border-emerald-300' : (step.activo ? 'bg-brand-500 border-cream-300 animate-pulse' : 'bg-slate-800 border-slate-600')"></div>
                                    <h5 class="text-xs font-bold text-white" x-text="step.titulo"></h5>
                                    <p class="text-[11px] text-slate-400" x-text="step.descripcion"></p>
                                    <span x-show="step.fecha" class="text-[10px] text-cream-300 font-bold block mt-0.5" x-text="step.fecha"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Detalles del Trámite -->
                    <div class="p-4 bg-slate-800/50 rounded-2xl border border-slate-700/60 space-y-2 text-xs" x-show="selectedTramite && Object.keys(selectedTramite.detalles || {}).length > 0">
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-300 mb-1">Detalles Registrados</h4>
                        <template x-for="(val, key) in (selectedTramite ? selectedTramite.detalles : {})" :key="key">
                            <div class="flex justify-between py-1 border-b border-slate-700/40">
                                <span class="text-slate-400" x-text="key + ':'"></span>
                                <span class="font-bold text-white text-right max-w-[60%] truncate" x-text="val"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Resumen del Trámite -->
                    <div class="p-4 bg-slate-800/60 rounded-2xl border border-slate-700/60 space-y-2">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400">Estado Actual:</span>
                            <span class="font-bold text-white" x-text="selectedTramite ? selectedTramite.estado_label : ''"></span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400">Fecha de Registro:</span>
                            <span class="font-bold text-slate-200" x-text="selectedTramite ? selectedTramite.fecha_formateada : ''"></span>
                        </div>
                        <template x-if="selectedTramite && selectedTramite.fecha_completado">
                            <div class="flex justify-between text-xs pt-1 border-t border-slate-700 font-bold">
                                <span class="text-slate-300">Fecha de Finalización:</span>
                                <span class="text-emerald-400" x-text="selectedTramite.fecha_completado"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Sección de Documento Final Listo -->
                    <template x-if="selectedTramite && (selectedTramite.documento_pdf || selectedTramite.documento_pdf_url)">
                        <div class="p-4 bg-gradient-to-br from-emerald-950/60 to-slate-900 border border-emerald-500/40 rounded-3xl space-y-3 shadow-lg shadow-emerald-950/20">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center text-xl shrink-0">
                                    📄
                                </div>
                                <div>
                                    <h4 class="text-xs font-black text-emerald-300 uppercase tracking-wider">Documento Oficial Listo</h4>
                                    <p class="text-[11px] text-slate-300">Tu trámite ha sido redactado y está disponible en formato PDF oficial.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 pt-1">
                                <!-- Botón 1: Descargar archivo -->
                                <button type="button" @click="downloadDocument(selectedTramite, false)" 
                                        class="py-3 px-3 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white rounded-2xl text-xs font-black shadow-md flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Descargar PDF</span>
                                </button>

                                <!-- Botón 2: Ver / Previsualizar en pantalla completa -->
                                <button type="button" @click="downloadDocument(selectedTramite, true)" 
                                        class="py-3 px-3 bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-200 border border-slate-600 rounded-2xl text-xs font-bold shadow-md flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Ver en Pantalla</span>
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Sección de Encuesta / Calificación de Atención en el Trámite -->
                    <template x-if="selectedTramite && (selectedTramite.estado === 'listo' || selectedTramite.documento_pdf || selectedTramite.documento_pdf_url)">
                        <div class="p-4 bg-gradient-to-br from-brand-950/60 via-slate-900 to-slate-900 border border-cream-300/40 rounded-3xl space-y-3 shadow-lg shadow-brand-950/30">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-brand-500/20 border border-cream-300/30 flex items-center justify-center text-xl shrink-0 text-cream-300">
                                        ⭐
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-cream-300 uppercase tracking-wider" x-text="selectedTramite.calificado ? 'Calificación del Trámite' : 'Encuesta de Satisfacción'"></h4>
                                        <p class="text-[11px] text-slate-300" x-text="selectedTramite.calificado ? 'Ya has calificado la atención recibida en este trámite.' : '¿Cómo fue la atención y rapidez en la entrega de tu documento?'"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Si ya está calificado, mostrar resumen de estrellas y comentario -->
                            <template x-if="selectedTramite.calificado && selectedTramite.resena">
                                <div class="p-3 bg-slate-950/60 rounded-2xl border border-slate-800/80 space-y-2 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-400">Tu Calificación:</span>
                                        <span class="text-cream-300 font-black flex items-center gap-1">
                                            <template x-for="i in 5" :key="i">
                                                <span x-text="i <= (selectedTramite.resena.calificacion || 5) ? '★' : '☆'" :class="i <= (selectedTramite.resena.calificacion || 5) ? 'text-cream-300' : 'text-slate-600'"></span>
                                            </template>
                                            <span class="text-white ml-1">(<span x-text="selectedTramite.resena.calificacion"></span>/5)</span>
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-[11px] pt-1 text-slate-400 border-t border-slate-800">
                                        <div>Atención: <strong class="text-cream-300" x-text="(selectedTramite.resena.atencion_personal || 5) + '/5 ★'"></strong></div>
                                        <div>Rapidez: <strong class="text-brand-300" x-text="(selectedTramite.resena.rapidez || 5) + '/5 ★'"></strong></div>
                                    </div>
                                    <template x-if="selectedTramite.resena.comentario">
                                        <div class="pt-1.5 border-t border-slate-800 text-slate-300 italic text-[11px]">
                                            "<span x-text="selectedTramite.resena.comentario"></span>"
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <button type="button" @click="openReviewModal(selectedTramite)" 
                                    class="w-full py-3 px-4 bg-gradient-to-r from-cream-300 to-cream-400 hover:from-cream-200 hover:to-cream-300 active:scale-95 text-slate-950 rounded-2xl text-xs font-black shadow-md flex items-center justify-center gap-2 transition-all cursor-pointer">
                                <span>⭐</span>
                                <span x-text="selectedTramite.calificado ? 'Modificar mi Calificación' : 'Realizar Encuesta de Calificación'"></span>
                            </button>
                        </div>
                    </template>

                    <!-- Si el trámite aún no tiene documento -->
                    <template x-if="selectedTramite && !selectedTramite.documento_pdf && !selectedTramite.documento_pdf_url">
                        <div class="p-3.5 bg-slate-800/40 border border-slate-700/50 rounded-2xl text-[11px] text-slate-400 flex items-center gap-2.5">
                            <span class="text-base">⏳</span>
                            <span>El documento oficial se habilitará aquí para descarga tan pronto como pase a <strong class="text-emerald-400">Listo para entregar</strong>.</span>
                        </div>
                    </template>

                    <button type="button" @click="selectedTramite = null" class="w-full py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-2xl text-xs font-bold shadow-md">
                        Cerrar Detalle
                    </button>
                </div>
            </div>


            <!-- ============================================== -->
            <!-- MODAL: ENCUESTA DE SATISFACCIÓN Y CALIFICACIÓN -->
            <!-- ============================================== -->
            <div x-show="reviewModal.visible" x-cloak class="fixed inset-0 z-50 bg-slate-950/85 backdrop-blur-sm flex justify-center items-end sm:items-center p-0 sm:p-4">
                <div @click.away="closeReviewModal()" 
                     class="w-full max-w-md bg-slate-900 border-t sm:border border-slate-700/80 rounded-t-3xl sm:rounded-3xl p-5 max-h-[90vh] overflow-y-auto space-y-4 shadow-2xl safe-bottom">
                    
                    <!-- Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-brand-500/20 border border-cream-300/30 text-cream-300 flex items-center justify-center text-lg font-bold">
                                ⭐
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-white">Encuesta de Satisfacción</h3>
                                <p class="text-[10px] text-cream-300 font-bold uppercase tracking-wider">Calificación de Atención</p>
                            </div>
                        </div>
                        <button type="button" @click="closeReviewModal()" class="p-1.5 bg-slate-800 rounded-xl text-slate-400 hover:text-white">✕</button>
                    </div>

                    <!-- Trámite info -->
                    <div class="p-3 bg-slate-800/60 rounded-2xl border border-slate-700/60 flex items-center gap-2.5">
                        <span class="text-lg">📋</span>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-xs font-black text-white truncate" x-text="reviewModal.tramite ? reviewModal.tramite.nombre : ''"></h4>
                            <p class="text-[10px] text-slate-400" x-text="reviewModal.tramite ? reviewModal.tramite.categoria_label : ''"></p>
                        </div>
                    </div>

                    <!-- Mensaje de Éxito al Enviar -->
                    <div x-show="reviewModal.success" x-cloak class="p-5 bg-emerald-500/15 border border-emerald-500/30 rounded-3xl text-center space-y-2">
                        <div class="w-12 h-12 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-2xl mx-auto">
                            🎉
                        </div>
                        <h4 class="text-sm font-black text-white">¡Gracias por tu opinión!</h4>
                        <p class="text-xs text-emerald-300">Tu calificación nos ayuda a seguir mejorando la atención en NESISTEMA.</p>
                        <button type="button" @click="closeReviewModal()" class="mt-2 px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-md">
                            Aceptar
                        </button>
                    </div>

                    <!-- Formulario de Calificación -->
                    <form x-show="!reviewModal.success" @submit.prevent="submitReview()" class="space-y-4">
                        
                        <div x-show="reviewModal.error" x-text="reviewModal.error" class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-2xl text-rose-400 text-xs font-semibold text-center"></div>

                        <!-- 1. Calificación General -->
                        <div class="bg-slate-800/80 p-4 rounded-2xl border border-slate-700/60 space-y-2 text-center">
                            <label class="block text-xs font-black text-slate-200 uppercase tracking-wider">
                                1. Calificación General del Servicio
                            </label>
                            <p class="text-[11px] text-slate-400">¿Qué tan satisfecho quedaste con el trámite?</p>
                            
                            <!-- Estrellas Grandes Interactivas -->
                            <div class="flex items-center justify-center gap-2 py-2">
                                <template x-for="star in 5" :key="'main_star_' + star">
                                    <button type="button" @click="reviewModal.form.calificacion = star" 
                                            class="text-3xl transition-transform hover:scale-125 active:scale-95 focus:outline-none cursor-pointer"
                                            :class="star <= reviewModal.form.calificacion ? 'text-cream-300 drop-shadow-[0_0_8px_rgba(255,240,166,0.6)]' : 'text-slate-600'">
                                        ★
                                    </button>
                                </template>
                            </div>
                            
                            <!-- Etiqueta descriptiva de estrellas -->
                            <div class="text-xs font-black text-cream-300">
                                <span x-show="reviewModal.form.calificacion === 5">⭐⭐⭐⭐⭐ Excelente (5/5)</span>
                                <span x-show="reviewModal.form.calificacion === 4">⭐⭐⭐⭐ Muy Buena (4/5)</span>
                                <span x-show="reviewModal.form.calificacion === 3">⭐⭐⭐ Buena / Aceptable (3/5)</span>
                                <span x-show="reviewModal.form.calificacion === 2">⭐⭐ Regular (2/5)</span>
                                <span x-show="reviewModal.form.calificacion === 1">⭐ Por Mejorar (1/5)</span>
                            </div>
                        </div>

                        <!-- 2. Atención del Personal -->
                        <div class="bg-slate-800/80 p-3.5 rounded-2xl border border-slate-700/60 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-300">2. Trato y Atención del Personal</label>
                                <span class="text-xs font-black text-cream-300" x-text="reviewModal.form.atencion_personal + '/5 ★'"></span>
                            </div>
                            <p class="text-[10px] text-slate-400">Amabilidad, asesoría y resolución de dudas.</p>
                            <div class="flex items-center gap-2 pt-1">
                                <template x-for="star in 5" :key="'atn_star_' + star">
                                    <button type="button" @click="reviewModal.form.atencion_personal = star" 
                                            class="text-xl transition-transform hover:scale-110 active:scale-95 focus:outline-none cursor-pointer"
                                            :class="star <= reviewModal.form.atencion_personal ? 'text-cream-300 drop-shadow-[0_0_6px_rgba(255,240,166,0.5)]' : 'text-slate-600'">
                                        ★
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- 3. Rapidez en la Entrega -->
                        <div class="bg-slate-800/80 p-3.5 rounded-2xl border border-slate-700/60 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-300">3. Rapidez y Puntualidad</label>
                                <span class="text-xs font-black text-brand-300" x-text="reviewModal.form.rapidez + '/5 ★'"></span>
                            </div>
                            <p class="text-[10px] text-slate-400">Tiempo de espera hasta la entrega del PDF oficial.</p>
                            <div class="flex items-center gap-2 pt-1">
                                <template x-for="star in 5" :key="'rap_star_' + star">
                                    <button type="button" @click="reviewModal.form.rapidez = star" 
                                            class="text-xl transition-transform hover:scale-110 active:scale-95 focus:outline-none cursor-pointer"
                                            :class="star <= reviewModal.form.rapidez ? 'text-cream-300 drop-shadow-[0_0_6px_rgba(255,240,166,0.5)]' : 'text-slate-600'">
                                        ★
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- 4. ¿Recomendarías nuestros servicios? -->
                        <div class="bg-slate-800/80 p-3.5 rounded-2xl border border-slate-700/60 space-y-2">
                            <label class="block text-xs font-bold text-slate-300">4. ¿Recomendarías nuestros servicios a amigos o familiares?</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="reviewModal.form.recomendaria = 1" 
                                        :class="reviewModal.form.recomendaria === 1 ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 border-emerald-500' : 'bg-slate-800 text-slate-400 border-slate-700'"
                                        class="py-2.5 px-3 rounded-xl text-xs font-bold border transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>👍</span>
                                    <span>Sí, totalmente</span>
                                </button>
                                <button type="button" @click="reviewModal.form.recomendaria = 0" 
                                        :class="reviewModal.form.recomendaria === 0 ? 'bg-rose-600 text-white shadow-md shadow-rose-600/30 border-rose-500' : 'bg-slate-800 text-slate-400 border-slate-700'"
                                        class="py-2.5 px-3 rounded-xl text-xs font-bold border transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>👎</span>
                                    <span>No por ahora</span>
                                </button>
                            </div>
                        </div>

                        <!-- 5. Comentarios y Sugerencias -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                                5. Comentario u Opinión (Opcional)
                            </label>
                            <textarea x-model="reviewModal.form.comentario" rows="3" placeholder="Cuéntanos qué te pareció la atención o qué podemos hacer mejor..." 
                                      class="w-full p-3 bg-slate-800 border border-slate-700 rounded-2xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all"></textarea>
                        </div>

                        <!-- Botón Enviar -->
                        <div class="pt-2">
                            <button type="submit" :disabled="reviewModal.submitting" 
                                    class="w-full py-4 bg-gradient-to-r from-cream-300 via-cream-400 to-amber-400 hover:from-cream-200 hover:to-cream-300 active:scale-[0.98] text-slate-950 rounded-2xl font-black text-sm shadow-xl shadow-cream-400/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <span x-show="!reviewModal.submitting">Enviar Calificación ⭐</span>
                                <span x-show="reviewModal.submitting" x-cloak class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-slate-950" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Guardando opinión...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- ============================== -->
            <!-- MODAL: CAMBIAR CONTRASEÑA      -->
            <!-- ============================== -->
            <div x-show="showPasswordModal" x-cloak class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex justify-center items-center p-4">
                <div @click.away="showPasswordModal = false" class="w-full max-w-sm bg-slate-900 border border-slate-700/80 rounded-3xl p-5 space-y-4 shadow-2xl">
                    <h3 class="text-sm font-black text-white">Cambiar Contraseña</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Contraseña Actual</label>
                            <input type="password" x-model="pwdForm.actual" placeholder="Tu contraseña actual" class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Nueva Contraseña</label>
                            <input type="password" x-model="pwdForm.nueva" placeholder="Mínimo 6 caracteres" class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-brand-500">
                        </div>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="button" @click="showPasswordModal = false" class="flex-1 py-2.5 bg-slate-800 text-slate-300 rounded-xl text-xs font-bold">Cancelar</button>
                        <button type="button" @click="handleChangePassword()" class="flex-1 py-2.5 bg-brand-600 hover:bg-brand-500 text-cream-100 rounded-xl text-xs font-bold">Guardar</button>
                    </div>
                </div>
            </div>


            <!-- ========================================== -->
            <!-- MODAL: DETALLE DEL SERVICIO NOTARIAL       -->
            <!-- ========================================== -->
            <div x-show="selectedServicio" x-cloak class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex justify-center items-end sm:items-center p-0 sm:p-4">
                <div @click.away="selectedServicio = null" 
                     class="w-full max-w-md bg-slate-900 border-t sm:border border-slate-700/80 rounded-t-3xl sm:rounded-3xl p-5 max-h-[85vh] overflow-y-auto space-y-4 shadow-2xl safe-bottom">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl flex items-center justify-center text-base shadow-sm text-white" 
                                  :style="'background:' + (selectedServicio ? selectedServicio.color_gradient : '#5046e5')" 
                                  x-text="selectedServicio ? (selectedServicio.icono_emoji || '🏛️') : '🏛️'"></span>
                            <span class="text-[11px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md text-white shadow-xs" 
                                  :style="'background:' + (selectedServicio ? selectedServicio.color_gradient : '#5046e5')" 
                                  x-text="selectedServicio ? selectedServicio.categoria_label : ''"></span>
                        </div>
                        <button type="button" @click="selectedServicio = null" class="p-1.5 bg-slate-800 rounded-xl text-slate-400 hover:text-white">✕</button>
                    </div>

                    <div class="space-y-1">
                        <h3 class="text-base font-black text-white" x-text="selectedServicio ? selectedServicio.nombre : ''"></h3>
                        <p class="text-xs text-slate-300 leading-relaxed" x-text="selectedServicio ? selectedServicio.descripcion : ''"></p>
                    </div>

                    <!-- Requisitos del Trámite -->
                    <div class="p-4 bg-slate-800/70 rounded-2xl border border-slate-700/60 space-y-3">
                        <h4 class="text-xs font-black uppercase tracking-wider text-cream-300 flex items-center gap-1.5">
                            <span>📋</span> Requisitos para este Trámite
                        </h4>
                        
                        <div class="space-y-2">
                            <template x-for="(req, idx) in (selectedServicio ? (selectedServicio.requisitos || []) : [])" :key="idx">
                                <div class="p-2.5 bg-slate-900/60 rounded-xl border border-slate-700/40 flex items-start gap-2.5">
                                    <span class="w-5 h-5 rounded-full bg-brand-500/20 text-cream-300 font-black text-[10px] flex items-center justify-center shrink-0 mt-0.5" x-text="idx + 1"></span>
                                    <span class="text-xs text-slate-200 leading-snug" x-text="req"></span>
                                </div>
                            </template>

                            <template x-if="!selectedServicio || !selectedServicio.requisitos || selectedServicio.requisitos.length === 0">
                                <p class="text-[11px] text-slate-400 italic">Documento de identidad oficial vigente y documentación relacionada al acto notarial.</p>
                            </template>
                        </div>
                    </div>

                    <!-- Información de Validez y Atención -->
                    <div class="p-3.5 bg-brand-950/40 border border-brand-500/30 rounded-2xl text-[11px] text-cream-200 flex items-center gap-2.5">
                        <span class="text-lg shrink-0">⚖️</span>
                        <span>Trámite avalado con firma notarial oficial y registro legal en el sistema.</span>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="space-y-2 pt-1">
                        <!-- Botón Principal: Chat en Vivo del Sistema -->
                        <button type="button" @click="solicitarServicioChat(selectedServicio)" 
                                class="w-full py-3.5 bg-gradient-to-r from-brand-600 via-brand-500 to-indigo-600 hover:from-brand-500 hover:to-brand-600 active:scale-98 text-cream-100 rounded-2xl text-xs font-black shadow-lg shadow-brand-950/40 flex items-center justify-center gap-2 cursor-pointer transition-all">
                            <span class="text-sm">💬</span>
                            <span>Solicitar Asesoría por Chat en Vivo</span>
                        </button>

                        <!-- Botón Secundario: WhatsApp con el número +1 (718) 717-3257 -->
                        <button type="button" @click="solicitarServicioWhatsApp(selectedServicio)" 
                                class="w-full py-3 bg-slate-800 hover:bg-slate-700 active:scale-98 text-emerald-400 border border-emerald-500/30 rounded-2xl text-xs font-bold flex items-center justify-center gap-2 cursor-pointer transition-all">
                            <span>📱 Consultar por WhatsApp (+1 718-717-3257)</span>
                        </button>

                        <button type="button" @click="selectedServicio = null" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-slate-400 rounded-2xl text-xs font-bold">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>


            <!-- ========================================== -->
            <!-- MODAL: VISOR DE DOCUMENTO PDF EN PANTALLA -->
            <!-- ========================================== -->
            <div x-show="pdfViewer.visible" x-cloak class="fixed inset-0 z-50 bg-slate-950/95 backdrop-blur-md flex flex-col justify-between p-2 sm:p-4">
                <!-- Header del Visor -->
                <div class="w-full max-w-2xl mx-auto bg-slate-900 border border-slate-700/80 rounded-2xl p-3 flex items-center justify-between shadow-xl shrink-0 mb-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center font-bold text-lg shrink-0">
                            📄
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-xs font-black text-white truncate" x-text="pdfViewer.title"></h3>
                            <span class="text-[10px] font-bold text-emerald-400 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span x-text="pdfViewer.totalPages ? (pdfViewer.totalPages + ' página(s) oficiales') : 'Documento Oficial Listo'"></span>
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0">
                        <!-- Zoom Controls -->
                        <button type="button" @click="zoomPdf(-0.25)" title="Alejar" 
                                class="p-1.5 px-2 bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-300 rounded-xl text-xs font-bold transition-all">
                            🔍-
                        </button>
                        
                        <button type="button" @click="resetZoom()" title="Ajustar a 100%"
                                class="px-2 py-1 bg-slate-800/90 hover:bg-slate-700 text-slate-300 rounded-xl text-[11px] font-black min-w-[44px] text-center border border-slate-700/60 shadow-xs">
                            <span x-text="Math.round((pdfViewer.zoom || 1.0) * 100) + '%'"></span>
                        </button>

                        <button type="button" @click="zoomPdf(0.25)" title="Acercar" 
                                class="p-1.5 px-2 bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-300 rounded-xl text-xs font-bold transition-all">
                            🔍+
                        </button>

                        <button type="button" @click="downloadDocument(pdfViewer.tramite, false)" 
                                class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white rounded-xl text-[11px] font-bold shadow flex items-center gap-1 transition-all cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span class="hidden sm:inline">Descargar PDF</span>
                        </button>

                        <button type="button" @click="closePdfViewer()" 
                                class="p-2 bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-300 hover:text-white rounded-xl text-xs font-bold transition-all">
                            ✕
                        </button>
                    </div>
                </div>

                <!-- Contenido con Canvases generados por PDF.js -->
                <div id="pdfScrollArea" class="w-full max-w-2xl mx-auto flex-1 bg-slate-900/90 border border-slate-700/80 rounded-2xl overflow-auto relative shadow-2xl p-2 sm:p-3 flex flex-col items-center touch-pan-x touch-pan-y">
                    
                    <!-- Spinner Cargando -->
                    <div x-show="pdfViewer.loading" class="flex flex-col items-center justify-center my-auto p-8 text-center space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-brand-600/20 border border-brand-500/30 flex items-center justify-center text-cream-300">
                            <svg class="animate-spin h-6 w-6" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </div>
                        <h4 class="text-sm font-bold text-white">Renderizando documento PDF...</h4>
                        <p class="text-xs text-slate-400 max-w-xs">Procesando páginas oficiales con máxima nitidez.</p>
                    </div>

                    <!-- Mensaje de Error si falla -->
                    <div x-show="!pdfViewer.loading && pdfViewer.error" class="my-auto p-8 text-center space-y-3 max-w-xs">
                        <div class="w-12 h-12 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center mx-auto text-xl font-bold">
                            ⚠️
                        </div>
                        <h4 class="text-sm font-bold text-white">No se pudo visualizar el PDF</h4>
                        <p class="text-xs text-slate-400" x-text="pdfViewer.error"></p>
                        <button type="button" @click="downloadDocument(pdfViewer.tramite, false)" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-md">
                            Intentar Descarga Directa
                        </button>
                    </div>

                    <!-- Contenedor de Canvases generados por PDF.js -->
                    <div id="pdfCanvasContainer" class="w-full flex flex-col items-center space-y-4" x-show="!pdfViewer.loading && !pdfViewer.error">
                    </div>
                </div>
            </div>


            <!-- ============================================== -->
            <!-- BOTÓN FLOTANTE DE CHAT (FAB)                   -->
            <!-- ============================================== -->
            <div x-show="isAuthenticated && !chatOpen" x-cloak class="fixed bottom-20 right-4 sm:right-auto sm:translate-x-[150px] z-40">
                <button type="button" @click="openChat()" 
                        class="w-14 h-14 rounded-full bg-gradient-to-tr from-brand-600 via-brand-500 to-indigo-600 text-cream-100 shadow-2xl shadow-brand-500/40 flex items-center justify-center relative hover:scale-105 active:scale-95 transition-all group border-2 border-cream-300/40 cursor-pointer">
                    <svg class="w-7 h-7 transform group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <!-- Badge Contador No Leídos -->
                    <span x-show="chatUnreadCount > 0" x-cloak 
                          class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-cream-300 text-slate-950 text-[11px] font-black flex items-center justify-center animate-bounce shadow-lg border-2 border-slate-900"
                          x-text="chatUnreadCount"></span>
                    <span x-show="chatUnreadCount > 0" x-cloak class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-cream-300 animate-ping opacity-75"></span>
                </button>
            </div>


            <!-- ============================================== -->
            <!-- MODAL / SLIDE-OVER: CHAT EN VIVO CON NOTARÍA   -->
            <!-- ============================================== -->
            <div x-show="chatOpen" x-cloak 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="translate-y-full opacity-0"
                 x-transition:enter-end="translate-y-0 opacity-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="translate-y-0 opacity-100"
                 x-transition:leave-end="translate-y-full opacity-0"
                 class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex justify-center items-end sm:items-center p-0 sm:p-4">
                
                <div @click.away="closeChat()" 
                     class="w-full max-w-md h-[92vh] sm:h-[650px] bg-slate-900 border-t sm:border border-slate-700/80 rounded-t-3xl sm:rounded-3xl flex flex-col justify-between shadow-2xl overflow-hidden safe-bottom">
                    
                    <!-- Header del Chat -->
                    <div class="p-4 bg-slate-950/95 border-b border-slate-800 flex items-center justify-between shrink-0 shadow-md">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-brand-600 to-brand-500 flex items-center justify-center text-cream-100 font-extrabold shadow-md shadow-brand-600/30 text-base">
                                🏛️
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-white leading-tight">Atención Notarial en Vivo</h3>
                                <p class="text-[11px] text-emerald-400 font-semibold flex items-center gap-1.5 mt-0.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>Notaría NESISTEMA • En línea</span>
                                </p>
                            </div>
                        </div>

                        <button type="button" @click="closeChat()" class="p-2 bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-400 hover:text-white rounded-xl text-xs font-bold transition-all">
                            ✕
                        </button>
                    </div>

                    <!-- Hilo de Mensajes con Scroll -->
                    <div id="clientChatMessagesStream" class="flex-1 p-4 overflow-y-auto space-y-3 custom-scrollbar bg-slate-900/40">
                        
                        <!-- Mensaje de Bienvenida Automático -->
                        <div class="flex flex-col items-start space-y-1">
                            <div class="max-w-[85%] bg-slate-800/90 border border-slate-700/80 text-slate-100 p-3.5 rounded-2xl rounded-tl-xs shadow-md space-y-1">
                                <div class="flex items-center gap-1.5 text-[10px] font-black text-cream-300">
                                    <span>🏛️ Notaría NESISTEMA</span>
                                </div>
                                <p class="text-xs font-medium leading-relaxed">
                                    ¡Hola! Bienvenido al canal de atención directa. Escríbenos cualquier consulta sobre tus trámites, requisitos o documentos notariales y te responderemos a la brevedad.
                                </p>
                                <span class="text-[10px] text-slate-400 block text-right">Canal Oficial</span>
                            </div>
                        </div>

                        <!-- Lista de Mensajes Reactiva -->
                        <template x-for="msg in chatMessages" :key="msg.id">
                            <div :class="msg.remitente_tipo === 'cliente' ? 'flex flex-col items-end space-y-1' : 'flex flex-col items-start space-y-1'">
                                
                                <div :class="msg.remitente_tipo === 'cliente' 
                                        ? 'max-w-[85%] bg-gradient-to-r from-brand-600 to-brand-700 text-cream-50 p-3.5 rounded-2xl rounded-tr-xs shadow-md space-y-1' 
                                        : 'max-w-[85%] bg-slate-800 border border-slate-700 text-slate-100 p-3.5 rounded-2xl rounded-tl-xs shadow-md space-y-1'">
                                    
                                    <template x-if="msg.remitente_tipo !== 'cliente'">
                                        <div class="flex items-center gap-1.5 text-[10px] font-black text-cream-300">
                                            <span>🏛️ <span x-text="msg.nombre_remitente || 'Asesor Notaría'"></span></span>
                                        </div>
                                    </template>

                                    <p class="text-xs md:text-sm font-medium leading-relaxed break-words whitespace-pre-wrap" x-text="msg.mensaje"></p>

                                    <div class="flex items-center justify-end gap-1 text-[10px] opacity-80 pt-0.5 font-bold">
                                        <span x-text="msg.hora"></span>
                                        <template x-if="msg.remitente_tipo === 'cliente'">
                                            <span x-text="msg.leido ? '✓✓' : '✓'"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Indicador si está cargando mensajes -->
                        <div x-show="chatLoading" class="text-center py-2 text-slate-500 text-xs flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-cream-300" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Sincronizando mensajes...</span>
                        </div>
                    </div>

                    <!-- Preguntas Rápidas -->
                    <div class="px-3 py-2 bg-slate-950/70 border-t border-slate-800/80 flex items-center gap-1.5 overflow-x-auto scrollbar-none shrink-0">
                        <button type="button" @click="insertClientQuickMessage('¿Cómo va el estado de mi trámite?')" 
                                class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-300 rounded-xl text-[10px] font-bold whitespace-nowrap transition-colors border border-slate-700/60 shrink-0">
                            🔍 Estado de trámite
                        </button>
                        <button type="button" @click="insertClientQuickMessage('¿Qué requisitos necesito para un poder?')" 
                                class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-300 rounded-xl text-[10px] font-bold whitespace-nowrap transition-colors border border-slate-700/60 shrink-0">
                            📜 Requisitos de poder
                        </button>
                        <button type="button" @click="insertClientQuickMessage('¿Ya puedo retirar mi documento oficial?')" 
                                class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-300 rounded-xl text-[10px] font-bold whitespace-nowrap transition-colors border border-slate-700/60 shrink-0">
                            🟢 Retiro de documento
                        </button>
                    </div>

                    <!-- Input para escribir mensaje -->
                    <div class="p-3 bg-slate-950 border-t border-slate-800 shrink-0">
                        <form @submit.prevent="sendChatMessage()" class="flex items-center gap-2">
                            <input type="text" x-model="chatInputText" placeholder="Escribe tu mensaje aquí..." 
                                   id="clientChatInputField"
                                   autocomplete="off"
                                   class="flex-1 pl-4 pr-3 py-3 bg-slate-800/90 border border-slate-700/80 rounded-2xl text-xs sm:text-sm font-semibold text-white placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all">
                            
                            <button type="submit" :disabled="chatSending || !chatInputText.trim()" 
                                    class="p-3 bg-brand-600 hover:bg-brand-500 active:scale-95 disabled:opacity-50 text-cream-100 rounded-2xl shadow-lg shadow-brand-600/30 transition-all font-bold flex items-center justify-center shrink-0 cursor-pointer">
                                <svg class="w-5 h-5 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            </button>
                        </form>
                    </div>

                </div>
            </div>


            <!-- ============================== -->
            <!-- BOTTOM NAVIGATION BAR (Móvil)  -->
            <!-- ============================== -->
            <nav class="bg-slate-900/95 backdrop-blur-md border-t border-slate-800/80 px-4 py-2.5 fixed bottom-0 max-w-md w-full z-40 flex items-center justify-between safe-bottom">
                
                <!-- Tab 1: Inicio -->
                <button type="button" @click="activeTab = 'inicio'" 
                        :class="activeTab === 'inicio' ? 'text-cream-300 font-extrabold' : 'text-slate-400 hover:text-slate-200'"
                        class="flex flex-col items-center gap-1 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span class="text-[10px]">Inicio</span>
                </button>

                <!-- Tab 2: Servicios (NUEVO) -->
                <button type="button" @click="activeTab = 'servicios'" 
                        :class="activeTab === 'servicios' ? 'text-cream-300 font-extrabold' : 'text-slate-400 hover:text-slate-200'"
                        class="flex flex-col items-center gap-1 transition-colors relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span class="text-[10px]">Servicios</span>
                </button>

                <!-- Tab 3: Trámites -->
                <button type="button" @click="activeTab = 'tramites'" 
                        :class="activeTab === 'tramites' ? 'text-cream-300 font-extrabold' : 'text-slate-400 hover:text-slate-200'"
                        class="flex flex-col items-center gap-1 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="text-[10px]">Trámites</span>
                </button>

                <!-- Tab 4: Avisos -->
                <button type="button" @click="activeTab = 'notificaciones'" 
                        :class="activeTab === 'notificaciones' ? 'text-cream-300 font-extrabold' : 'text-slate-400 hover:text-slate-200'"
                        class="flex flex-col items-center gap-1 transition-colors relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <span class="text-[10px]">Avisos</span>
                    <span x-show="unreadNotifCount > 0" class="absolute -top-1 right-2 w-2 h-2 rounded-full bg-rose-500"></span>
                </button>

                <!-- Tab 5: Mi Cuenta -->
                <button type="button" @click="activeTab = 'perfil'" 
                        :class="activeTab === 'perfil' ? 'text-cream-300 font-extrabold' : 'text-slate-400 hover:text-slate-200'"
                        class="flex flex-col items-center gap-1 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span class="text-[10px]">Mi Cuenta</span>
                </button>

            </nav>

        </div>

    </div>

    <!-- SCRIPT DE LÓGICA REACTIVA DE LA APP -->
    <script>
        // Interceptor global para enlaces de WhatsApp y esquemas externos en WebView móvil
        document.addEventListener('click', function(e) {
            const target = e.target.closest('a');
            if (target && target.href) {
                const href = target.href;
                if (href.includes('wa.me') || href.includes('api.whatsapp.com') || href.startsWith('whatsapp://')) {
                    e.preventDefault();
                    e.stopPropagation();
                    try {
                        let phone = '17187173257';
                        let text = '';
                        if (href.includes('wa.me/')) {
                            const parts = href.split('wa.me/')[1] || '';
                            const subparts = parts.split('?');
                            phone = subparts[0] || '17187173257';
                            if (subparts[1]) {
                                const params = new URLSearchParams(subparts[1]);
                                text = params.get('text') || '';
                            }
                        } else if (href.includes('api.whatsapp.com')) {
                            const urlObj = new URL(href);
                            phone = urlObj.searchParams.get('phone') || '17187173257';
                            text = urlObj.searchParams.get('text') || '';
                        }
                        
                        if (window.AndroidBridge && typeof window.AndroidBridge.openWhatsApp === 'function') {
                            window.AndroidBridge.openWhatsApp(phone, text);
                        } else {
                            window.location.href = `https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(text)}`;
                        }
                    } catch(err) {
                        if (window.AndroidBridge && typeof window.AndroidBridge.openWhatsApp === 'function') {
                            window.AndroidBridge.openWhatsApp('17187173257', '');
                        } else {
                            window.location.href = href;
                        }
                    }
                }
            }
        }, true);

        function clientPortalApp() {
            return {
                isAuthenticated: false,
                token: localStorage.getItem('nesistema_client_token') || '',
                cliente: {},
                activeTab: 'inicio', // inicio, servicios, tramites, notificaciones, perfil
                isLoading: false,
                loginError: '',
                showPassword: false,
                rememberMe: true,
                loginForm: {
                    identificacion: localStorage.getItem('nesistema_saved_user') || '',
                    password: '',
                },
                pwdForm: {
                    actual: '',
                    nueva: '',
                },
                showPasswordModal: false,
                isRefreshing: false,
                tramites: [],
                servicios: [],
                selectedServicio: null,
                servicioCategoryFilter: 'todos',
                searchServicioQuery: '',
                notificaciones: [],
                unreadNotifCount: 0,
                stats: {
                    total: 0,
                    en_proceso: 0,
                    listos: 0,
                    en_revision: 0,
                },
                chatOpen: false,
                chatMessages: [],
                chatInputText: '',
                chatUnreadCount: 0,
                chatLoading: false,
                chatSending: false,
                statusFilter: 'todos',
                categoryFilter: 'todas',
                searchQuery: '',
                selectedTramite: null,
                timeline: [],
                pdfViewer: {
                    visible: false,
                    loading: false,
                    tramite: null,
                    title: '',
                    totalPages: 0,
                    zoom: 1.0,
                    arrayBuffer: null,
                    error: '',
                },
                reviewModal: {
                    visible: false,
                    tramite: null,
                    submitting: false,
                    success: false,
                    error: '',
                    form: {
                        calificacion: 5,
                        atencion_personal: 5,
                        rapidez: 5,
                        recomendaria: 1,
                        comentario: ''
                    }
                },

                apiUrl(endpoint) {
                    if (!endpoint) return '';
                    if (endpoint.startsWith('http://') || endpoint.startsWith('https://')) {
                        return endpoint;
                    }
                    const cleanPath = endpoint.startsWith('/') ? endpoint : '/' + endpoint;

                    // Si estamos dentro de la APK móvil en Android Studio (Capacitor nativo)
                    const isNativeCapacitor = Boolean(window.Capacitor && window.Capacitor.isNativePlatform && window.Capacitor.isNativePlatform());
                    if (isNativeCapacitor || window.location.protocol === 'capacitor:' || window.location.protocol === 'file:') {
                        const base = (localStorage.getItem('nesistema_backend_url') || 'https://nesistema.com').replace(/\/+$/, '');
                        return base + cleanPath;
                    }

                    // Si estamos en un navegador web (tanto en servidor de producción como en localhost/XAMPP)
                    return cleanPath;
                },

                init() {
                    this.fetchServicios();
                    this.requestNotificationPermissions();
                    if (this.token) {
                        this.isAuthenticated = true;
                        this.syncNativeToken(this.token);
                        this.initPushNotifications();
                        this.refreshAll();
                        this.fetchChatUnread();
                        // Polling cada 4 segundos para chequear si el admin completó un trámite o envió chat
                        setInterval(() => {
                            if (this.isAuthenticated && !document.hidden) {
                                this.fetchTramites(false);
                                this.fetchNotificaciones(false);
                                if (this.chatOpen) {
                                    this.fetchChatMessages(false);
                                } else {
                                    this.fetchChatUnread();
                                }
                            }
                        }, 4000);
                    }
                },

                syncNativeToken(token) {
                    if (window.AndroidBridge && typeof window.AndroidBridge.syncClientToken === 'function') {
                        try {
                            window.AndroidBridge.syncClientToken(token || '');
                        } catch(e) {}
                    }
                },

                async requestNotificationPermissions() {
                    try {
                        if ('Notification' in window && Notification.permission !== 'granted' && Notification.permission !== 'denied') {
                            await Notification.requestPermission();
                        }
                    } catch(e) {}

                    try {
                        if (window.Capacitor && window.Capacitor.isPluginAvailable && window.Capacitor.isPluginAvailable('LocalNotifications')) {
                            const LocalNotifications = window.Capacitor.Plugins.LocalNotifications;
                            let permStatus = await LocalNotifications.checkPermissions();
                            if (permStatus && permStatus.display !== 'granted') {
                                await LocalNotifications.requestPermissions();
                            }
                        }
                    } catch(e) {}
                },

                async initPushNotifications() {
                    try {
                        if (window.Capacitor && window.Capacitor.isPluginAvailable && window.Capacitor.isPluginAvailable('PushNotifications')) {
                            const PushNotifications = window.Capacitor.Plugins.PushNotifications;

                            PushNotifications.addListener('registration', async (token) => {
                                console.log('FCM Token registered:', token.value);
                                if (this.token && token.value) {
                                    try {
                                        await fetch(this.apiUrl('/api/v1/client/register-device?token=' + encodeURIComponent(this.token)), {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Authorization': 'Bearer ' + this.token,
                                                'X-Client-Token': this.token,
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                fcm_token: token.value,
                                                platform: 'android'
                                            })
                                        });
                                    } catch(err) {}
                                }
                            });

                            PushNotifications.addListener('pushNotificationReceived', (notification) => {
                                this.showLocalNotification(notification.title || 'NESISTEMA', notification.body || '', notification.data);
                                this.refreshAll();
                            });

                            PushNotifications.addListener('pushNotificationActionPerformed', (notification) => {
                                const data = (notification.notification && notification.notification.data) || {};
                                if (data.tipo === 'chat') {
                                    this.openChat();
                                } else if (data.tramite_id) {
                                    this.activeTab = 'tramites';
                                    this.fetchTramites();
                                }
                            });
                        }
                    } catch(e) {}
                },

                showLocalNotification(title, body, data = {}) {
                    this.playNotificationSound();
                    if (navigator.vibrate) {
                        try { navigator.vibrate([150, 80, 150]); } catch(e) {}
                    }

                    try {
                        if (window.Capacitor && window.Capacitor.isPluginAvailable && window.Capacitor.isPluginAvailable('LocalNotifications')) {
                            const LocalNotifications = window.Capacitor.Plugins.LocalNotifications;
                            LocalNotifications.schedule({
                                notifications: [
                                    {
                                        title: title,
                                        body: body,
                                        id: Math.floor(Date.now() % 100000),
                                        schedule: { at: new Date(Date.now() + 100) },
                                        sound: null,
                                        attachments: null,
                                        actionTypeId: '',
                                        extra: data
                                    }
                                ]
                            });
                            return;
                        }
                    } catch(e) {}

                    try {
                        if ('Notification' in window && Notification.permission === 'granted') {
                            new Notification(title, {
                                body: body,
                                icon: '/img/logo.png',
                                badge: '/img/logo.png',
                                data: data
                            });
                        }
                    } catch(e) {}
                },

                playNotificationSound() {
                    try {
                        const AudioContext = window.AudioContext || window.webkitAudioContext;
                        if (!AudioContext) return;
                        const ctx = new AudioContext();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(587.33, ctx.currentTime);
                        osc.frequency.setValueAtTime(880, ctx.currentTime + 0.08);
                        gain.gain.setValueAtTime(0.25, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.35);
                    } catch(e) {}
                },

                async refreshAll() {
                    if (!this.token) return;
                    this.isRefreshing = true;
                    try {
                        await Promise.all([
                            this.fetchProfile(),
                            this.fetchTramites(false),
                            this.fetchServicios(),
                            this.fetchNotificaciones(false),
                            this.fetchChatUnread()
                        ]);
                    } catch (e) {
                        console.error('Error al actualizar datos:', e);
                    } finally {
                        setTimeout(() => { this.isRefreshing = false; }, 500);
                    }
                },

                async handleLogin() {
                    this.isLoading = true;
                    this.loginError = '';
                    try {
                        const res = await fetch(this.apiUrl('/api/v1/client/login'), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(this.loginForm)
                        });

                        const data = await res.json();
                        if (data.success) {
                            this.token = data.token;
                            this.cliente = data.cliente;
                            this.isAuthenticated = true;
                            localStorage.setItem('nesistema_client_token', data.token);
                            if (this.rememberMe) {
                                localStorage.setItem('nesistema_saved_user', this.loginForm.identificacion);
                            }
                            this.syncNativeToken(data.token);
                            this.requestNotificationPermissions();
                            this.initPushNotifications();
                            await this.fetchTramites();
                            await this.fetchNotificaciones();
                        } else {
                            this.loginError = data.message || 'Credenciales incorrectas.';
                        }
                    } catch (e) {
                        this.loginError = 'Error de conexión con el servidor. Revisa tu red.';
                    } finally {
                        this.isLoading = false;
                    }
                },

                async fetchProfile() {
                    if (!this.token) return;
                    try {
                        const res = await fetch(this.apiUrl('/api/v1/client/profile?token=' + encodeURIComponent(this.token)), {
                            headers: {
                                'Authorization': 'Bearer ' + this.token,
                                'X-Client-Token': this.token,
                                'Accept': 'application/json',
                            }
                        });
                        if (res.status === 401) return this.handleLogout();
                        const data = await res.json();
                        if (data.success) {
                            this.cliente = data.cliente || {};
                        }
                    } catch (e) {
                        console.error('Error al obtener perfil:', e);
                    }
                },

                async fetchTramites(showLoader = true) {
                    if (!this.token) return;
                    try {
                        const res = await fetch(this.apiUrl('/api/v1/client/tramites?token=' + encodeURIComponent(this.token)), {
                            headers: {
                                'Authorization': 'Bearer ' + this.token,
                                'X-Client-Token': this.token,
                                'Accept': 'application/json',
                            }
                        });
                        if (res.status === 401) return this.handleLogout();
                        const data = await res.json();
                        if (data.success) {
                            this.tramites = data.tramites || [];
                            this.stats = data.stats || { total: 0, en_proceso: 0, listos: 0, en_revision: 0 };
                        }
                    } catch (e) {
                        console.error('Error al obtener trámites:', e);
                    }
                },

                async fetchNotificaciones(showLoader = true) {
                    if (!this.token) return;
                    try {
                        const res = await fetch(this.apiUrl('/api/v1/client/notificaciones?token=' + encodeURIComponent(this.token)), {
                            headers: {
                                'Authorization': 'Bearer ' + this.token,
                                'X-Client-Token': this.token,
                                'Accept': 'application/json',
                            }
                        });
                        if (res.status === 401) return this.handleLogout();
                        const data = await res.json();
                        if (data.success) {
                            const prevUnread = this.unreadNotifCount;
                            this.notificaciones = data.notificaciones || [];
                            this.unreadNotifCount = data.unread_count || 0;

                            // Si se detectan nuevas notificaciones no leídas recibidas en vivo
                            if (this.unreadNotifCount > prevUnread && prevUnread !== 0) {
                                const newest = this.notificaciones[0];
                                const notifTitle = newest ? (newest.titulo || '🔔 Trámite Actualizado') : '🔔 Actualización de Trámite';
                                const notifBody = newest ? (newest.mensaje || 'Tienes una nueva actualización en tus trámites.') : 'Tienes una nueva actualización en tus trámites.';
                                this.showLocalNotification(notifTitle, notifBody, newest);
                            }
                        }
                    } catch (e) {}
                },

                async fetchServicios() {
                    try {
                        const res = await fetch(this.apiUrl('/api/v1/client/servicios' + (this.token ? ('?token=' + encodeURIComponent(this.token)) : '')), {
                            headers: {
                                'Authorization': this.token ? ('Bearer ' + this.token) : '',
                                'X-Client-Token': this.token || '',
                                'Accept': 'application/json',
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.servicios = data.servicios || [];
                        }
                    } catch (e) {
                        console.error('Error al cargar servicios:', e);
                    }
                },

                get filteredServicios() {
                    return this.servicios.filter(s => {
                        const matchesCategory = this.servicioCategoryFilter === 'todos' || s.categoria === this.servicioCategoryFilter;
                        const q = this.searchServicioQuery.toLowerCase().trim();
                        const matchesSearch = !q || 
                            s.nombre.toLowerCase().includes(q) || 
                            (s.descripcion && s.descripcion.toLowerCase().includes(q)) ||
                            (s.requisitos && s.requisitos.some(r => r.toLowerCase().includes(q)));

                        return matchesCategory && matchesSearch;
                    });
                },

                openServicioDetail(s) {
                    this.selectedServicio = s;
                },

                async solicitarServicioChat(s) {
                    if (!s) return;
                    const nombreTramite = s.nombre || 'este trámite';
                    const msg = `Hola, deseo solicitar asesoría e iniciar el trámite notarial: "${nombreTramite}". ¿Cuáles son los pasos y requisitos?`;
                    
                    // 1. Cerrar modal de servicio
                    this.selectedServicio = null;
                    
                    // 2. Abrir chat modal
                    this.chatOpen = true;
                    this.chatUnreadCount = 0;
                    this.chatInputText = msg;
                    
                    // 3. Enviar mensaje automáticamente al chat o enfocar input
                    if (this.isAuthenticated && this.token) {
                        await this.fetchChatMessages(true);
                        await this.sendChatMessage();
                    } else {
                        await this.fetchChatMessages(true);
                        this.$nextTick(() => {
                            const input = document.getElementById('clientChatInputField');
                            if (input) input.focus();
                        });
                    }
                },

                solicitarServicioWhatsApp(s) {
                    if (!s) return;
                    const phone = '17187173257';
                    const msg = `Hola, deseo consultar y solicitar información sobre el trámite notarial: *${s.nombre}*. ¿Cuáles son los pasos a seguir?`;
                    this.openWhatsApp(phone, msg);
                },

                openWhatsApp(phone = '17187173257', message = '') {
                    const cleanPhone = String(phone || '17187173257').replace(/[^0-9]/g, '');
                    const cleanMsg = String(message || '');

                    // 1. Si está disponible el puente Android nativo (Abre directamente la App de WhatsApp)
                    if (window.AndroidBridge && typeof window.AndroidBridge.openWhatsApp === 'function') {
                        try {
                            window.AndroidBridge.openWhatsApp(cleanPhone, cleanMsg);
                            return;
                        } catch(e) {
                            console.warn('AndroidBridge.openWhatsApp error:', e);
                        }
                    }

                    // 2. Si está en Capacitor con plugin Browser
                    if (window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.Browser) {
                        try {
                            const url = `https://api.whatsapp.com/send?phone=${cleanPhone}&text=${encodeURIComponent(cleanMsg)}`;
                            window.Capacitor.Plugins.Browser.open({ url: url });
                            return;
                        } catch(e) {}
                    }

                    // 3. Fallback web / navegador móvil
                    const waUrl = `https://api.whatsapp.com/send?phone=${cleanPhone}&text=${encodeURIComponent(cleanMsg)}`;
                    try {
                        const win = window.open(waUrl, '_system');
                        if (!win || win.closed || typeof win.closed === 'undefined') {
                            window.location.href = waUrl;
                        }
                    } catch(err) {
                        window.location.href = waUrl;
                    }
                },

                get filteredTramites() {
                    return this.tramites.filter(t => {
                        const matchesStatus = this.statusFilter === 'todos' || t.estado === this.statusFilter;
                        const matchesCategory = this.categoryFilter === 'todas' || t.categoria === this.categoryFilter;
                        const q = this.searchQuery.toLowerCase().trim();
                        const matchesSearch = !q || 
                            t.nombre.toLowerCase().includes(q) || 
                            t.descripcion.toLowerCase().includes(q) ||
                            String(t.id).includes(q);

                        return matchesStatus && matchesCategory && matchesSearch;
                    });
                },

                get listosTramites() {
                    return this.tramites.filter(t => t.estado === 'listo' || Boolean(t.documento_pdf || t.documento_pdf_url));
                },

                async openPdfPreview(t) {
                    if (!t) return;
                    this.pdfViewer.tramite = t;
                    this.pdfViewer.title = t.nombre || 'Documento Oficial';
                    this.pdfViewer.visible = true;
                    this.pdfViewer.loading = true;
                    this.pdfViewer.error = '';
                    this.pdfViewer.zoom = 1.0;
                    this.pdfViewer.totalPages = 0;

                    const container = document.getElementById('pdfCanvasContainer');
                    if (container) container.innerHTML = '';

                    const downloadUrl = this.apiUrl(`/api/v1/client/tramites/${t.categoria}/${t.id}/download?token=${encodeURIComponent(this.token)}&preview=1`);

                    try {
                        const res = await fetch(downloadUrl, {
                            headers: {
                                'Authorization': 'Bearer ' + this.token,
                                'Accept': 'application/pdf',
                            }
                        });

                        if (!res.ok) {
                            throw new Error(`Error del servidor (${res.status}). El documento no pudo cargarse.`);
                        }

                        const buffer = await res.arrayBuffer();
                        this.pdfViewer.arrayBuffer = buffer;

                        if (window.pdfjsLib) {
                            await this.renderPdfPages(buffer);
                        } else {
                            const blob = new Blob([buffer], { type: 'application/pdf' });
                            const blobUrl = window.URL.createObjectURL(blob);
                            if (container) {
                                container.innerHTML = `<iframe src="${blobUrl}#toolbar=0" class="w-full h-[500px] rounded-xl bg-white"></iframe>`;
                            }
                        }
                    } catch (err) {
                        console.error('Error al visualizar PDF:', err);
                        this.pdfViewer.error = err.message || 'Error al procesar el archivo PDF.';
                    } finally {
                        this.pdfViewer.loading = false;
                    }
                },

                async renderPdfPages(arrayBuffer) {
                    if (!window.pdfjsLib || !arrayBuffer) return;
                    try {
                        const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
                        this.pdfViewer.totalPages = pdf.numPages;
                        const container = document.getElementById('pdfCanvasContainer');
                        if (!container) return;
                        container.innerHTML = '';

                        const scrollArea = document.getElementById('pdfScrollArea');
                        const containerWidth = Math.max((scrollArea ? scrollArea.clientWidth : container.clientWidth) || 340, 320);
                        const baseWidth = containerWidth - 28;

                        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                            const page = await pdf.getPage(pageNum);
                            const initialViewport = page.getViewport({ scale: 1.0 });
                            
                            // Factor de resolución 2.0 para nitidez perfecta al hacer zoom
                            const baseScale = baseWidth / initialViewport.width;
                            const renderScale = baseScale * 2.0;
                            const viewport = page.getViewport({ scale: renderScale });

                            const canvas = document.createElement('canvas');
                            canvas.className = 'rounded-xl shadow-2xl bg-white border border-slate-700/50 mb-4 transition-[width] duration-150 ease-out origin-top';
                            canvas.dataset.baseWidth = String(baseWidth);

                            canvas.height = viewport.height;
                            canvas.width = viewport.width;

                            // Ancho visual en pantalla según el nivel de zoom actual
                            const displayWidth = Math.round(baseWidth * (this.pdfViewer.zoom || 1.0));
                            canvas.style.width = displayWidth + 'px';
                            canvas.style.maxWidth = 'none';
                            canvas.style.height = 'auto';

                            const context = canvas.getContext('2d');
                            container.appendChild(canvas);

                            await page.render({
                                canvasContext: context,
                                viewport: viewport
                            }).promise;
                        }

                        this.attachPinchToZoom();
                    } catch (e) {
                        console.error('Error renderizando páginas PDF:', e);
                        throw e;
                    }
                },

                zoomPdf(delta) {
                    let currentZoom = this.pdfViewer.zoom || 1.0;
                    let newZoom = currentZoom + delta;
                    newZoom = Math.min(Math.max(0.6, Math.round(newZoom * 100) / 100), 3.0);
                    this.setZoom(newZoom);
                },

                resetZoom() {
                    this.setZoom(1.0);
                },

                setZoom(newZoom) {
                    this.pdfViewer.zoom = newZoom;
                    const container = document.getElementById('pdfCanvasContainer');
                    if (!container) return;
                    
                    const canvases = container.querySelectorAll('canvas');
                    canvases.forEach(canvas => {
                        const baseWidth = parseFloat(canvas.dataset.baseWidth) || 320;
                        const targetWidth = Math.round(baseWidth * newZoom);
                        canvas.style.width = targetWidth + 'px';
                        canvas.style.maxWidth = 'none';
                        canvas.style.height = 'auto';
                    });
                },

                attachPinchToZoom() {
                    const scrollArea = document.getElementById('pdfScrollArea');
                    if (!scrollArea || scrollArea._pinchAttached) return;
                    scrollArea._pinchAttached = true;

                    let initialDist = 0;
                    let startZoom = 1.0;

                    scrollArea.addEventListener('touchstart', (e) => {
                        if (e.touches.length === 2) {
                            initialDist = Math.hypot(
                                e.touches[0].pageX - e.touches[1].pageX,
                                e.touches[0].pageY - e.touches[1].pageY
                            );
                            startZoom = this.pdfViewer.zoom || 1.0;
                        }
                    }, { passive: true });

                    scrollArea.addEventListener('touchmove', (e) => {
                        if (e.touches.length === 2 && initialDist > 0) {
                            const currentDist = Math.hypot(
                                e.touches[0].pageX - e.touches[1].pageX,
                                e.touches[0].pageY - e.touches[1].pageY
                            );
                            const factor = currentDist / initialDist;
                            let newZoom = startZoom * factor;
                            newZoom = Math.min(Math.max(0.6, Math.round(newZoom * 100) / 100), 3.0);
                            this.setZoom(newZoom);
                        }
                    }, { passive: true });

                    scrollArea.addEventListener('touchend', (e) => {
                        if (e.touches.length < 2) {
                            initialDist = 0;
                        }
                    }, { passive: true });
                },

                closePdfViewer() {
                    this.pdfViewer.visible = false;
                    this.pdfViewer.tramite = null;
                    this.pdfViewer.arrayBuffer = null;
                    this.pdfViewer.error = '';
                    this.pdfViewer.zoom = 1.0;
                    const container = document.getElementById('pdfCanvasContainer');
                    if (container) container.innerHTML = '';
                },

                async downloadDocument(t, preview = false) {
                    if (!t) return;
                    
                    if (preview) {
                        return this.openPdfPreview(t);
                    }

                    const downloadUrl = this.apiUrl(`/api/v1/client/tramites/${t.categoria}/${t.id}/download?token=${encodeURIComponent(this.token)}`);
                    const fileName = (t.nombre ? t.nombre.replace(/[^a-zA-Z0-9_-]/g, '_') : 'Documento_Tramite') + '.pdf';

                    this.isLoading = true;

                    try {
                        let arrayBuffer = null;

                        // Si el PDF ya está abierto en memoria en el visor
                        if (this.pdfViewer.visible && this.pdfViewer.arrayBuffer && this.pdfViewer.tramite && this.pdfViewer.tramite.id === t.id) {
                            arrayBuffer = this.pdfViewer.arrayBuffer;
                        } else {
                            const res = await fetch(downloadUrl, {
                                headers: {
                                    'Authorization': 'Bearer ' + this.token,
                                    'X-Client-Token': this.token,
                                    'Accept': 'application/pdf',
                                }
                            });

                            if (res.ok) {
                                arrayBuffer = await res.arrayBuffer();
                            }
                        }

                        // 1. Si estamos en Android nativo con puente AndroidBridge (Guarda directamente en Descargas del celular)
                        if (window.AndroidBridge) {
                            if (arrayBuffer && typeof window.AndroidBridge.saveBase64Pdf === 'function') {
                                const bytes = new Uint8Array(arrayBuffer);
                                let binary = '';
                                const len = bytes.byteLength;
                                for (let i = 0; i < len; i++) {
                                    binary += String.fromCharCode(bytes[i]);
                                }
                                const base64 = window.btoa(binary);
                                window.AndroidBridge.saveBase64Pdf(base64, fileName);
                                this.isLoading = false;
                                return;
                            } else if (typeof window.AndroidBridge.downloadFile === 'function') {
                                window.AndroidBridge.downloadFile(downloadUrl, fileName);
                                this.isLoading = false;
                                return;
                            }
                        }

                        // 2. Si tenemos el buffer descargado en navegador web / PWA
                        if (arrayBuffer) {
                            const blob = new Blob([arrayBuffer], { type: 'application/pdf' });

                            // Intentar compartir o guardar con Web Share API (Móvil)
                            try {
                                const file = new File([blob], fileName, { type: 'application/pdf' });
                                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                                    await navigator.share({
                                        files: [file],
                                        title: fileName,
                                        text: `Documento oficial del trámite: ${t.nombre || ''}`
                                    });
                                    this.isLoading = false;
                                    return;
                                }
                            } catch (shareErr) {
                                if (shareErr.name === 'AbortError') {
                                    this.isLoading = false;
                                    return;
                                }
                            }

                            // Descarga clásica mediante Blob
                            const blobUrl = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = blobUrl;
                            a.download = fileName;
                            document.body.appendChild(a);
                            a.click();

                            setTimeout(() => {
                                document.body.removeChild(a);
                                window.URL.revokeObjectURL(blobUrl);
                            }, 3000);
                        } else {
                            // Fallback
                            if (window.AndroidBridge && typeof window.AndroidBridge.openExternalUrl === 'function') {
                                window.AndroidBridge.openExternalUrl(downloadUrl);
                            } else {
                                window.open(downloadUrl, '_blank');
                            }
                        }
                    } catch (err) {
                        console.warn('Fallback al método alternativo de descarga:', err);
                        if (window.AndroidBridge && typeof window.AndroidBridge.openExternalUrl === 'function') {
                            window.AndroidBridge.openExternalUrl(downloadUrl);
                        } else {
                            window.open(downloadUrl, '_blank');
                        }
                    } finally {
                        this.isLoading = false;
                    }
                },

                async openTramiteDetail(t) {
                    if (!t) return;
                    this.selectedTramite = t;
                    try {
                        const res = await fetch(this.apiUrl(`/api/v1/client/tramites/${t.categoria}/${t.id}?token=` + encodeURIComponent(this.token)), {
                            headers: {
                                'Authorization': 'Bearer ' + this.token,
                                'X-Client-Token': this.token,
                                'Accept': 'application/json',
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.timeline = data.timeline;
                        }
                    } catch (e) {}
                },

                async handleNotifClick(n) {
                    // Marcar como leída
                    if (!n.leido) {
                        n.leido = true;
                        this.unreadNotifCount = Math.max(0, this.unreadNotifCount - 1);
                        fetch(this.apiUrl(`/api/v1/client/notificaciones/${n.id}/read?token=` + encodeURIComponent(this.token)), {
                            method: 'POST',
                            headers: { 
                                'Authorization': 'Bearer ' + this.token, 
                                'X-Client-Token': this.token,
                                'Accept': 'application/json' 
                            }
                        });
                    }

                    // Si la notificación pertenece a un trámite, abrirlo
                    if (n.tramite_tipo && n.tramite_id) {
                        const found = this.tramites.find(t => t.categoria === n.tramite_tipo && String(t.id) === String(n.tramite_id));
                        if (found) {
                            this.openTramiteDetail(found);
                        }
                    }
                },

                async markAllAsRead() {
                    this.notificaciones.forEach(n => n.leido = true);
                    this.unreadNotifCount = 0;
                    fetch(this.apiUrl('/api/v1/client/notificaciones/all/read?token=' + encodeURIComponent(this.token)), {
                        method: 'POST',
                        headers: { 
                            'Authorization': 'Bearer ' + this.token, 
                            'X-Client-Token': this.token,
                            'Accept': 'application/json' 
                        }
                    });
                },

                async handleChangePassword() {
                    if (!this.pwdForm.actual || !this.pwdForm.nueva) {
                        return alert('Por favor llena todos los campos.');
                    }
                    try {
                        const res = await fetch(this.apiUrl('/api/v1/client/update-password?token=' + encodeURIComponent(this.token)), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + this.token,
                                'X-Client-Token': this.token,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                password_actual: this.pwdForm.actual,
                                password_nuevo: this.pwdForm.nueva,
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            alert('¡Contraseña actualizada con éxito!');
                            this.showPasswordModal = false;
                            this.pwdForm.actual = '';
                            this.pwdForm.nueva = '';
                        } else {
                            alert(data.message || 'Error al cambiar contraseña.');
                        }
                    } catch (e) {
                        alert('Error de conexión.');
                    }
                },

                handleLogout() {
                    if (this.token) {
                        fetch(this.apiUrl('/api/v1/client/logout?token=' + encodeURIComponent(this.token)), {
                            method: 'POST',
                            headers: { 'Authorization': 'Bearer ' + this.token, 'X-Client-Token': this.token, 'Accept': 'application/json' }
                        }).catch(() => {});
                    }
                    if (window.AndroidBridge && typeof window.AndroidBridge.clearClientToken === 'function') {
                        try { window.AndroidBridge.clearClientToken(); } catch(e) {}
                    }
                    this.token = '';
                    this.cliente = {};
                    this.tramites = [];
                    this.notificaciones = [];
                    this.unreadNotifCount = 0;
                    this.stats = { total: 0, en_proceso: 0, listos: 0, en_revision: 0 };
                    this.isAuthenticated = false;
                    this.loginForm.password = '';
                    localStorage.removeItem('nesistema_client_token');
                },

                async openChat() {
                    this.chatOpen = true;
                    this.chatUnreadCount = 0;
                    await this.fetchChatMessages(true);
                    setTimeout(() => {
                        const input = document.getElementById('clientChatInputField');
                        if (input) input.focus();
                    }, 200);
                },

                closeChat() {
                    this.chatOpen = false;
                },

                async fetchChatMessages(forceScroll = false) {
                    if (!this.token) return;
                    if (this.chatMessages.length === 0) {
                        this.chatLoading = true;
                    }
                    try {
                        const res = await fetch(this.apiUrl('/api/v1/client/chat?token=' + encodeURIComponent(this.token)), {
                            headers: {
                                'Authorization': 'Bearer ' + this.token,
                                'X-Client-Token': this.token,
                                'Accept': 'application/json',
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            const prevLength = this.chatMessages.length;
                            this.chatMessages = data.mensajes || [];
                            this.chatUnreadCount = 0;
                            if (forceScroll || this.chatMessages.length > prevLength) {
                                this.scrollChatToBottom();
                            }
                        }
                    } catch (e) {
                        console.error('Error al cargar chat:', e);
                    } finally {
                        this.chatLoading = false;
                    }
                },

                async sendChatMessage() {
                    if (!this.token || !this.chatInputText.trim() || this.chatSending) return;
                    const text = this.chatInputText.trim();
                    this.chatInputText = '';
                    this.chatSending = true;

                    // Optimistic update
                    const tempId = 'temp_' + Date.now();
                    const now = new Date();
                    const tempMsg = {
                        id: tempId,
                        remitente_tipo: 'cliente',
                        nombre_remitente: this.cliente.nombre || 'Yo',
                        mensaje: text,
                        hora: now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }),
                        leido: false
                    };
                    this.chatMessages.push(tempMsg);
                    this.scrollChatToBottom();

                    try {
                        const res = await fetch(this.apiUrl('/api/v1/client/chat/enviar?token=' + encodeURIComponent(this.token)), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + this.token,
                                'X-Client-Token': this.token,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ mensaje: text, token: this.token })
                        });
                        const data = await res.json();
                        if (data.success) {
                            const idx = this.chatMessages.findIndex(m => m.id === tempId);
                            if (idx !== -1) {
                                this.chatMessages[idx] = data.mensaje;
                            }
                        }
                    } catch (e) {
                        console.error('Error al enviar mensaje:', e);
                    } finally {
                        this.chatSending = false;
                    }
                },

                async fetchChatUnread() {
                    if (!this.token) return;
                    try {
                        const res = await fetch(this.apiUrl('/api/v1/client/chat/unread?token=' + encodeURIComponent(this.token)), {
                            headers: {
                                'Authorization': 'Bearer ' + this.token,
                                'X-Client-Token': this.token,
                                'Accept': 'application/json',
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            const prevUnread = this.chatUnreadCount;
                            this.chatUnreadCount = data.unread_count || 0;
                            if (this.chatUnreadCount > prevUnread && !this.chatOpen && prevUnread !== 0) {
                                this.showLocalNotification('💬 Nuevo Mensaje de Asesoría', 'La Notaría NESISTEMA te ha respondido en el chat.', { tipo: 'chat' });
                            }
                        }
                    } catch (e) {}
                },

                insertClientQuickMessage(text) {
                    this.chatInputText = text;
                    const input = document.getElementById('clientChatInputField');
                    if (input) input.focus();
                },

                scrollChatToBottom() {
                    setTimeout(() => {
                        const stream = document.getElementById('clientChatMessagesStream');
                        if (stream) {
                            stream.scrollTop = stream.scrollHeight;
                        }
                    }, 50);
                },

                openReviewModal(t) {
                    if (!t) return;
                    this.reviewModal.tramite = t;
                    this.reviewModal.submitting = false;
                    this.reviewModal.success = false;
                    this.reviewModal.error = '';
                    
                    if (t.resena) {
                        this.reviewModal.form = {
                            calificacion: t.resena.calificacion || 5,
                            atencion_personal: t.resena.atencion_personal || 5,
                            rapidez: t.resena.rapidez || 5,
                            recomendaria: t.resena.recomendaria !== undefined ? (t.resena.recomendaria ? 1 : 0) : 1,
                            comentario: t.resena.comentario || ''
                        };
                    } else {
                        this.reviewModal.form = {
                            calificacion: 5,
                            atencion_personal: 5,
                            rapidez: 5,
                            recomendaria: 1,
                            comentario: ''
                        };
                    }
                    this.reviewModal.visible = true;
                },

                closeReviewModal() {
                    this.reviewModal.visible = false;
                },

                async submitReview() {
                    const t = this.reviewModal.tramite;
                    if (!t || !this.token || this.reviewModal.submitting) return;

                    this.reviewModal.submitting = true;
                    this.reviewModal.error = '';

                    try {
                        const url = this.apiUrl(`/api/v1/client/tramites/${t.categoria}/${t.id}/resena?token=` + encodeURIComponent(this.token));
                        const payload = {
                            calificacion: this.reviewModal.form.calificacion,
                            atencion_personal: this.reviewModal.form.atencion_personal,
                            rapidez: this.reviewModal.form.rapidez,
                            recomendaria: this.reviewModal.form.recomendaria === 1,
                            comentario: this.reviewModal.form.comentario,
                            tramite_nombre: t.nombre || 'Trámite Notarial',
                            token: this.token
                        };

                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + this.token,
                                'X-Client-Token': this.token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await res.json();
                        if (data.success) {
                            this.reviewModal.success = true;
                            // Actualizar el estado en tiempo real en la app
                            t.calificado = true;
                            t.resena = data.resena;

                            const foundInList = this.tramites.find(item => item.categoria === t.categoria && item.id === t.id);
                            if (foundInList) {
                                foundInList.calificado = true;
                                foundInList.resena = data.resena;
                            }

                            if (this.selectedTramite && this.selectedTramite.categoria === t.categoria && this.selectedTramite.id === t.id) {
                                this.selectedTramite.calificado = true;
                                this.selectedTramite.resena = data.resena;
                            }

                            setTimeout(() => {
                                this.closeReviewModal();
                            }, 2200);
                        } else {
                            this.reviewModal.error = data.message || 'No se pudo guardar la calificación. Intenta de nuevo.';
                        }
                    } catch (err) {
                        console.error('Error al guardar reseña:', err);
                        this.reviewModal.error = 'Error de conexión con el servidor. Revisa tu red.';
                    } finally {
                        this.reviewModal.submitting = false;
                    }
                },

                formatNumber(val) {
                    return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
                }
            }
        }
    </script>
</body>
</html>
