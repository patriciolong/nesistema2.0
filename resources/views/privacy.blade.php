<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidad y Términos de la App Móvil | NESISTEMA 2.0</title>
    <meta name="description" content="Política de Privacidad, Protección de Datos y Términos de Uso de la Aplicación Móvil y Plataforma NESISTEMA de Notaría Ecuador Inc.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col">

    <!-- Header / Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-blue-600 flex items-center justify-center text-white font-black text-lg shadow-md shadow-indigo-200">
                    N
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-slate-900 tracking-tight leading-none">NESISTEMA 2.0</h1>
                    <span class="text-[11px] font-semibold text-slate-500">Notaría Ecuador Inc.</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="text-xs font-bold text-slate-600 hover:text-indigo-600 px-3 py-2 rounded-lg transition-colors">
                    Iniciar Sesión
                </a>
                <a href="https://notariaecuador.com" target="_blank" class="text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white px-3.5 py-2 rounded-xl shadow-sm transition-all">
                    Sitio Web Oficial ➔
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            
            <!-- Hero Banner -->
            <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-8 sm:p-10 text-white shadow-xl mb-8 border border-slate-800">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-bold tracking-wide uppercase mb-4 border border-indigo-500/30">
                    <span>🛡️</span> Transparencia y Seguridad Legal
                </div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight mb-3">
                    Política de Privacidad y Tratamiento de Datos
                </h2>
                <p class="text-sm sm:text-base text-slate-300 max-w-2xl leading-relaxed">
                    Normas de privacidad, seguridad de la información y declaración de permisos para la <strong>Aplicación Móvil NESISTEMA</strong> y el Portal Notarial de Clientes.
                </p>
                <div class="mt-6 flex flex-wrap items-center gap-4 text-xs text-slate-400">
                    <span>📅 <strong>Última actualización:</strong> Septiembre 2026</span>
                    <span>•</span>
                    <span>🏢 <strong>Responsable:</strong> Notaría Ecuador Inc. (New York, USA)</span>
                </div>
            </div>

            <!-- Content Card -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-10 space-y-8 text-sm leading-relaxed text-slate-600">
                
                <!-- Section 1 -->
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900 mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-black flex items-center justify-center">1</span>
                        Introducción y Responsable del Tratamiento
                    </h3>
                    <p>
                        <strong>Notaría Ecuador Inc.</strong> (con sede principal en el Estado de New York, Estados Unidos), en adelante denominada como "la Notaría" o "NESISTEMA", pone a disposición de sus clientes y usuarios el sistema de gestión <strong>NESISTEMA 2.0</strong> y la aplicación móvil para dispositivos Android e iOS.
                    </p>
                    <p class="mt-2">
                        Nos tomamos muy en serio la privacidad y protección de los datos de nuestros usuarios, cumpliendo rigurosamente con los estándares internacionales de confidencialidad, secreto profesional notarial y las directrices de seguridad de <em>Google Play</em> y <em>Apple App Store</em>.
                    </p>
                </div>

                <!-- Section 2 -->
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900 mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-black flex items-center justify-center">2</span>
                        Datos que Recopilamos y Finalidad
                    </h3>
                    <p>Para la debida tramitación de instrumentos notariales y la consulta en tiempo real, se recopilan los siguientes datos:</p>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                            <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-1">📋 Datos de Identificación</h4>
                            <p class="text-xs text-slate-500">Nombres completos, número de cédula/pasaporte, fecha de nacimiento, estado civil y domicilio para la validez legal del acto notarial.</p>
                        </div>
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                            <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-1">📞 Datos de Contacto</h4>
                            <p class="text-xs text-slate-500">Teléfono, WhatsApp y correo electrónico para el envío de notificaciones y coordinación del trámite.</p>
                        </div>
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                            <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-1">📁 Documentación de Trámites</h4>
                            <p class="text-xs text-slate-500">Poderes, apostillas, traducciones, certificaciones y expedientes cargados por el cliente o redactados por la notaría.</p>
                        </div>
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                            <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-1">💳 Información Financiera / Saldo</h4>
                            <p class="text-xs text-slate-500">Montos abonados, saldos pendientes y recibos de pago para el control de cartera del cliente.</p>
                        </div>
                    </div>
                </div>

                <!-- Section 3: App Permissions -->
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900 mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-black flex items-center justify-center">3</span>
                        Declaración de Permisos de la App Móvil (Google Play Console)
                    </h3>
                    <p>La aplicación móvil <strong>NESISTEMA</strong> requiere únicamente los siguientes permisos para su funcionamiento:</p>
                    
                    <div class="mt-4 space-y-3">
                        <div class="flex items-start gap-3 p-3.5 bg-indigo-50/50 border border-indigo-100 rounded-xl">
                            <span class="text-lg shrink-0">🔔</span>
                            <div>
                                <strong class="text-indigo-950 font-bold text-xs">Notificaciones Push (POST_NOTIFICATIONS):</strong>
                                <p class="text-xs text-slate-600 mt-0.5">Utilizado para alertar al usuario cuando su trámite haya sido redactado, apostillado, esté listo para retiro o se registre un abono de pago.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3.5 bg-indigo-50/50 border border-indigo-100 rounded-xl">
                            <span class="text-lg shrink-0">📷</span>
                            <div>
                                <strong class="text-indigo-950 font-bold text-xs">Cámara y Galería / Archivos:</strong>
                                <p class="text-xs text-slate-600 mt-0.5">Se solicita únicamente cuando el cliente decide adjuntar voluntariamente una foto de su cédula o comprobante de pago. La app <strong>no</strong> accede a archivos personales sin consentimiento.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3.5 bg-indigo-50/50 border border-indigo-100 rounded-xl">
                            <span class="text-lg shrink-0">🔒</span>
                            <div>
                                <strong class="text-indigo-950 font-bold text-xs">Almacenamiento Seguro Local (Tokens Cifrados):</strong>
                                <p class="text-xs text-slate-600 mt-0.5">Guarda de forma encriptada el token de sesión en el dispositivo para mantener el acceso seguro del usuario.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4 -->
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900 mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-black flex items-center justify-center">4</span>
                        Seguridad y Confidencialidad de la Información
                    </h3>
                    <p>
                        Todos los canales de comunicación entre la app móvil, el portal web y nuestros servidores operan bajo <strong>cifrado SSL/TLS de 256 bits</strong>. Las contraseñas se almacenan mediante funciones hash irreversibles. <strong>Nunca compartimos ni vendemos datos personales a terceros</strong> con propósitos comerciales.
                    </p>
                </div>

                <!-- Section 5 -->
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900 mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-black flex items-center justify-center">5</span>
                        Solicitud de Eliminación de Cuenta y Datos (Google Play Policy)
                    </h3>
                    <p>
                        En cumplimiento de los requisitos de eliminación de datos de Google Play, cualquier usuario que desee eliminar su cuenta y registros de la aplicación móvil puede hacerlo en cualquier momento:
                    </p>
                    <div class="mt-3 p-4 bg-amber-50 border border-amber-200 rounded-2xl text-amber-900 text-xs leading-relaxed">
                        <p class="font-bold mb-1">🗑️ Procedimiento de Baja de Cuenta:</p>
                        <p>1. Envía un correo a <a href="mailto:privacidad@notariaecuador.com" class="font-extrabold underline">privacidad@notariaecuador.com</a> con el asunto <em>"Eliminación de Cuenta NESISTEMA"</em>.</p>
                        <p>2. Indica tu número de identificación / cédula registrada.</p>
                        <p>3. Nuestro equipo procesará la desvinculación y eliminación de credenciales en un plazo máximo de 48 a 72 horas hábiles.</p>
                    </div>
                </div>

                <!-- Section 6 -->
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900 mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-black flex items-center justify-center">6</span>
                        Contacto Legal
                    </h3>
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl text-xs space-y-1">
                        <p><strong>Notaría Ecuador Inc.</strong></p>
                        <p>📍 New York, Estados Unidos</p>
                        <p>📞 Teléfono: (212) 810-7721 | WhatsApp: +1 (212) 810-7721</p>
                        <p>✉️ Correo: <a href="mailto:info@notariaecuador.com" class="text-indigo-600 font-bold">info@notariaecuador.com</a></p>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <div class="max-w-5xl mx-auto px-4">
            <p>© {{ date('Y') }} Notaría Ecuador Inc. | NESISTEMA 2.0. Todos los derechos reservados.</p>
        </div>
    </footer>

</body>
</html>
