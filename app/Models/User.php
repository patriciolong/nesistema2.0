<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'username', 'role', 'status', 'office', 'permissions'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Catálogo maestro de permisos por módulo del sistema con los 4 puntos (Ver, Crear, Editar, Eliminar) y acciones especiales.
     */
    public const AVAILABLE_PERMISSIONS = [
        'dashboard' => [
            'nombre' => 'Dashboard & Métricas',
            'icono' => '📊',
            'color' => 'indigo',
            'permisos' => [
                'dashboard.view' => [
                    'nombre' => 'Acceso al Dashboard',
                    'accion' => 'view',
                    'descripcion' => 'Permite ver el panel principal, métricas globales y accesos rápidos.',
                ],
                'dashboard.export' => [
                    'nombre' => 'Exportar Métricas en Excel',
                    'accion' => 'export',
                    'descripcion' => 'Permite exportar el libro multi-hoja de métricas a formato .xlsx.',
                ],
                'dashboard.ia' => [
                    'nombre' => 'Diagnóstico Inteligente con IA',
                    'accion' => 'ia',
                    'descripcion' => 'Permite consultar sugerencias, cuellos de botella y análisis predictivo.',
                ],
            ],
        ],
        'clientes' => [
            'nombre' => 'Clientes',
            'icono' => '👥',
            'color' => 'emerald',
            'permisos' => [
                'clientes.view' => [
                    'nombre' => 'Ver Clientes',
                    'accion' => 'view',
                    'descripcion' => 'Permite consultar el listado y expediente de clientes.',
                ],
                'clientes.create' => [
                    'nombre' => 'Crear Clientes',
                    'accion' => 'create',
                    'descripcion' => 'Permite registrar nuevos clientes en el sistema.',
                ],
                'clientes.edit' => [
                    'nombre' => 'Editar Clientes',
                    'accion' => 'edit',
                    'descripcion' => 'Permite modificar la información de clientes existentes.',
                ],
                'clientes.delete' => [
                    'nombre' => 'Eliminar Clientes',
                    'accion' => 'delete',
                    'descripcion' => 'Permite eliminar clientes de la base de datos.',
                ],
                'clientes.abonar' => [
                    'nombre' => 'Registrar Abonos y Cobros',
                    'accion' => 'abonar',
                    'descripcion' => 'Permite recibir pagos, registrar abonos y emitir recibos oficiales.',
                ],
                'clientes.export' => [
                    'nombre' => 'Exportar Clientes a Excel',
                    'accion' => 'export',
                    'descripcion' => 'Permite descargar el padrón completo de clientes en formato Excel.',
                ],
            ],
        ],
        'cartera' => [
            'nombre' => 'Cartera y Créditos',
            'icono' => '💳',
            'color' => 'orange',
            'permisos' => [
                'cartera.view' => [
                    'nombre' => 'Ver Cartera / Cuentas por Cobrar',
                    'accion' => 'view',
                    'descripcion' => 'Permite consultar las cuentas por cobrar, deuda y saldos pendientes.',
                ],
                'cartera.create' => [
                    'nombre' => 'Registrar Crédito / Deuda',
                    'accion' => 'create',
                    'descripcion' => 'Permite registrar saldos iniciales o cargos a clientes.',
                ],
                'cartera.edit' => [
                    'nombre' => 'Editar Cuotas y Saldos',
                    'accion' => 'edit',
                    'descripcion' => 'Permite modificar saldos pendientes y acuerdos de pago.',
                ],
                'cartera.delete' => [
                    'nombre' => 'Eliminar / Anular Deudas',
                    'accion' => 'delete',
                    'descripcion' => 'Permite anular o cancelar registros de cuentas por cobrar.',
                ],
                'cartera.cobrar' => [
                    'nombre' => 'Gestionar Cobros y Liquidaciones',
                    'accion' => 'cobrar',
                    'descripcion' => 'Permite procesar cobros directos de cartera con emisión de recibo.',
                ],
            ],
        ],
        'cajas' => [
            'nombre' => 'Operación de Caja',
            'icono' => '💵',
            'color' => 'teal',
            'permisos' => [
                'cajas.view' => [
                    'nombre' => 'Ver Cajas y Sesiones',
                    'accion' => 'view',
                    'descripcion' => 'Permite ver el estado actual de las cajas y balance en vivo.',
                ],
                'cajas.create' => [
                    'nombre' => 'Abrir Caja / Nuevo Movimiento',
                    'accion' => 'create',
                    'descripcion' => 'Permite abrir turno de caja e ingresar movimientos manuales.',
                ],
                'cajas.edit' => [
                    'nombre' => 'Editar Movimientos de Caja',
                    'accion' => 'edit',
                    'descripcion' => 'Permite rectificar ingresos o egresos registrados en caja.',
                ],
                'cajas.delete' => [
                    'nombre' => 'Eliminar Movimientos de Caja',
                    'accion' => 'delete',
                    'descripcion' => 'Permite anular movimientos o transacciones de caja.',
                ],
                'cajas.operar' => [
                    'nombre' => 'Operar Caja de Turno (Arqueo / Cierre)',
                    'accion' => 'operar',
                    'descripcion' => 'Permite registrar gastos menores, arqueos y cierres de turno.',
                ],
                'cajas.historial' => [
                    'nombre' => 'Historial de Cajas Propias',
                    'accion' => 'historial',
                    'descripcion' => 'Permite consultar y reimprimir actas de turnos cerrados del propio usuario.',
                ],
            ],
        ],
        'tramites_global' => [
            'nombre' => 'Historial Global de Trámites',
            'icono' => '📁',
            'color' => 'blue',
            'permisos' => [
                'tramites_global.view' => [
                    'nombre' => 'Ver Trámites Realizados (Historial)',
                    'accion' => 'view',
                    'descripcion' => 'Permite consultar y filtrar todos los trámites realizados a nivel general.',
                ],
                'tramites_global.create' => [
                    'nombre' => 'Iniciar Trámites Globales',
                    'accion' => 'create',
                    'descripcion' => 'Permite iniciar trámites desde el módulo general.',
                ],
                'tramites_global.edit' => [
                    'nombre' => 'Editar Estados y Documentos',
                    'accion' => 'edit',
                    'descripcion' => 'Permite actualizar datos, cambiar estado o subir archivos finales.',
                ],
                'tramites_global.delete' => [
                    'nombre' => 'Eliminar Trámites Realizados',
                    'accion' => 'delete',
                    'descripcion' => 'Permite anular o eliminar trámites registrados.',
                ],
                'tramites_global.export' => [
                    'nombre' => 'Exportar Historial de Trámites',
                    'accion' => 'export',
                    'descripcion' => 'Permite exportar el listado completo de trámites a Excel.',
                ],
            ],
        ],
        'poderes' => [
            'nombre' => 'Poderes Notariales',
            'icono' => '📜',
            'color' => 'amber',
            'permisos' => [
                'poderes.view' => [
                    'nombre' => 'Ver Poderes Notariales',
                    'accion' => 'view',
                    'descripcion' => 'Permite consultar los expedientes de poderes notariales.',
                ],
                'poderes.create' => [
                    'nombre' => 'Crear Poderes Notariales',
                    'accion' => 'create',
                    'descripcion' => 'Permite redactar e iniciar un nuevo trámite de poder.',
                ],
                'poderes.edit' => [
                    'nombre' => 'Editar Poderes Notariales',
                    'accion' => 'edit',
                    'descripcion' => 'Permite modificar poderdantes, apoderados y cláusulas.',
                ],
                'poderes.delete' => [
                    'nombre' => 'Eliminar Poderes Notariales',
                    'accion' => 'delete',
                    'descripcion' => 'Permite anular o borrar un trámite de poder notarial.',
                ],
            ],
        ],
        'divorcios' => [
            'nombre' => 'Trámites de Divorcio',
            'icono' => '💔',
            'color' => 'rose',
            'permisos' => [
                'divorcios.view' => [
                    'nombre' => 'Ver Trámites de Divorcio',
                    'accion' => 'view',
                    'descripcion' => 'Permite consultar expedientes de trámites de divorcio.',
                ],
                'divorcios.create' => [
                    'nombre' => 'Crear Trámites de Divorcio',
                    'accion' => 'create',
                    'descripcion' => 'Permite iniciar nuevos trámites de divorcio notarial.',
                ],
                'divorcios.edit' => [
                    'nombre' => 'Editar Trámites de Divorcio',
                    'accion' => 'edit',
                    'descripcion' => 'Permite modificar cónyuges, acuerdos y datos del divorcio.',
                ],
                'divorcios.delete' => [
                    'nombre' => 'Eliminar Trámites de Divorcio',
                    'accion' => 'delete',
                    'descripcion' => 'Permite anular o borrar un trámite de divorcio.',
                ],
            ],
        ],
        'impuestos' => [
            'nombre' => 'Trámites de Impuestos',
            'icono' => '📑',
            'color' => 'cyan',
            'permisos' => [
                'impuestos.view' => [
                    'nombre' => 'Ver Trámites de Impuestos',
                    'accion' => 'view',
                    'descripcion' => 'Permite consultar expedientes de declaraciones e impuestos.',
                ],
                'impuestos.create' => [
                    'nombre' => 'Crear Trámites de Impuestos',
                    'accion' => 'create',
                    'descripcion' => 'Permite redactar e ingresar nuevos trámites de impuestos.',
                ],
                'impuestos.edit' => [
                    'nombre' => 'Editar Trámites de Impuestos',
                    'accion' => 'edit',
                    'descripcion' => 'Permite modificar montos, formularios y datos de impuestos.',
                ],
                'impuestos.delete' => [
                    'nombre' => 'Eliminar Trámites de Impuestos',
                    'accion' => 'delete',
                    'descripcion' => 'Permite anular o eliminar trámites de impuestos.',
                ],
            ],
        ],
        'tramites_varios' => [
            'nombre' => 'Trámites Varios',
            'icono' => '📝',
            'color' => 'violet',
            'permisos' => [
                'tramites_varios.view' => [
                    'nombre' => 'Ver Trámites Varios',
                    'accion' => 'view',
                    'descripcion' => 'Permite consultar solicitudes y registros de trámites varios.',
                ],
                'tramites_varios.create' => [
                    'nombre' => 'Crear Trámites Varios',
                    'accion' => 'create',
                    'descripcion' => 'Permite ingresar nuevas solicitudes de trámites varios.',
                ],
                'tramites_varios.edit' => [
                    'nombre' => 'Editar Trámites Varios',
                    'accion' => 'edit',
                    'descripcion' => 'Permite modificar precios, descripciones y estados.',
                ],
                'tramites_varios.delete' => [
                    'nombre' => 'Eliminar Trámites Varios',
                    'accion' => 'delete',
                    'descripcion' => 'Permite anular o eliminar registros de trámites varios.',
                ],
            ],
        ],
        'tramites_personalizados' => [
            'nombre' => 'Trámites Personalizados',
            'icono' => '🧩',
            'color' => 'fuchsia',
            'permisos' => [
                'tramites_personalizados.view' => [
                    'nombre' => 'Ver Trámites Personalizados',
                    'accion' => 'view',
                    'descripcion' => 'Permite consultar trámites basados en formularios dinámicos.',
                ],
                'tramites_personalizados.create' => [
                    'nombre' => 'Crear Trámites Personalizados',
                    'accion' => 'create',
                    'descripcion' => 'Permite registrar nuevos trámites personalizados.',
                ],
                'tramites_personalizados.edit' => [
                    'nombre' => 'Editar Trámites Personalizados',
                    'accion' => 'edit',
                    'descripcion' => 'Permite modificar campos dinámicos y estados de trámite.',
                ],
                'tramites_personalizados.delete' => [
                    'nombre' => 'Eliminar Trámites Personalizados',
                    'accion' => 'delete',
                    'descripcion' => 'Permite borrar registros de trámites personalizados.',
                ],
            ],
        ],
        'plantillas' => [
            'nombre' => 'Plantillas de Documentos',
            'icono' => '📄',
            'color' => 'yellow',
            'permisos' => [
                'plantillas.view' => [
                    'nombre' => 'Ver Plantillas',
                    'accion' => 'view',
                    'descripcion' => 'Permite consultar el catálogo y modelos de plantillas notariales.',
                ],
                'plantillas.create' => [
                    'nombre' => 'Crear Plantillas',
                    'accion' => 'create',
                    'descripcion' => 'Permite diseñar y registrar nuevas plantillas de documentos.',
                ],
                'plantillas.edit' => [
                    'nombre' => 'Editar Plantillas',
                    'accion' => 'edit',
                    'descripcion' => 'Permite modificar modelos, variables y textos en el editor.',
                ],
                'plantillas.delete' => [
                    'nombre' => 'Eliminar Plantillas',
                    'accion' => 'delete',
                    'descripcion' => 'Permite eliminar plantillas del sistema.',
                ],
                'plantillas.generar' => [
                    'nombre' => 'Generar y Emitir Documentos',
                    'accion' => 'generar',
                    'descripcion' => 'Permite rellenar, emitir y descargar documentos notariales oficiales.',
                ],
            ],
        ],
        'membretes' => [
            'nombre' => 'Membretes Notariales',
            'icono' => '🖼️',
            'color' => 'pink',
            'permisos' => [
                'membretes.view' => [
                    'nombre' => 'Ver Membretes',
                    'accion' => 'view',
                    'descripcion' => 'Permite consultar encabezados y pies de página notariales.',
                ],
                'membretes.create' => [
                    'nombre' => 'Crear / Subir Membrete',
                    'accion' => 'create',
                    'descripcion' => 'Permite subir nuevos diseños de membrete.',
                ],
                'membretes.edit' => [
                    'nombre' => 'Editar Membrete',
                    'accion' => 'edit',
                    'descripcion' => 'Permite modificar dimensiones y configuración de membretes.',
                ],
                'membretes.delete' => [
                    'nombre' => 'Eliminar Membrete',
                    'accion' => 'delete',
                    'descripcion' => 'Permite borrar membretes registrados.',
                ],
            ],
        ],
        'bancos' => [
            'nombre' => 'Bancos & Cuentas',
            'icono' => '🏦',
            'color' => 'sky',
            'permisos' => [
                'bancos.view' => [
                    'nombre' => 'Ver Bancos',
                    'accion' => 'view',
                    'descripcion' => 'Permite consultar entidades financieras y cuentas registradas.',
                ],
                'bancos.create' => [
                    'nombre' => 'Crear Bancos',
                    'accion' => 'create',
                    'descripcion' => 'Permite registrar nuevos bancos y cuentas en el sistema.',
                ],
                'bancos.edit' => [
                    'nombre' => 'Editar Bancos',
                    'accion' => 'edit',
                    'descripcion' => 'Permite modificar nombres, números de cuenta y saldos.',
                ],
                'bancos.delete' => [
                    'nombre' => 'Eliminar Bancos',
                    'accion' => 'delete',
                    'descripcion' => 'Permite desactivar o eliminar cuentas bancarias.',
                ],
            ],
        ],
        'tarjetas' => [
            'nombre' => 'Tarjetas & Datáfonos POS',
            'icono' => '💳',
            'color' => 'rose',
            'permisos' => [
                'tarjetas.view' => [
                    'nombre' => 'Ver Tarjetas / POS',
                    'accion' => 'view',
                    'descripcion' => 'Permite consultar terminales de cobro y redes de pago.',
                ],
                'tarjetas.create' => [
                    'nombre' => 'Crear Tarjetas / POS',
                    'accion' => 'create',
                    'descripcion' => 'Permite dar de alta nuevos datáfonos y terminales.',
                ],
                'tarjetas.edit' => [
                    'nombre' => 'Editar Tarjetas / POS',
                    'accion' => 'edit',
                    'descripcion' => 'Permite modificar datos de datáfonos y cuentas asociadas.',
                ],
                'tarjetas.delete' => [
                    'nombre' => 'Eliminar Tarjetas / POS',
                    'accion' => 'delete',
                    'descripcion' => 'Permite dar de baja terminales POS.',
                ],
            ],
        ],
        'oficinas' => [
            'nombre' => 'Oficinas & Sucursales',
            'icono' => '🏢',
            'color' => 'amber',
            'permisos' => [
                'oficinas.view' => [
                    'nombre' => 'Ver Oficinas',
                    'accion' => 'view',
                    'descripcion' => 'Permite consultar el listado de oficinas y sucursales.',
                ],
                'oficinas.create' => [
                    'nombre' => 'Crear Oficinas',
                    'accion' => 'create',
                    'descripcion' => 'Permite registrar nuevas sucursales y puntos de atención.',
                ],
                'oficinas.edit' => [
                    'nombre' => 'Editar Oficinas',
                    'accion' => 'edit',
                    'descripcion' => 'Permite modificar dirección, teléfono y datos de la oficina.',
                ],
                'oficinas.delete' => [
                    'nombre' => 'Eliminar Oficinas',
                    'accion' => 'delete',
                    'descripcion' => 'Permite suspender o eliminar oficinas.',
                ],
            ],
        ],
        'tipo_tramites' => [
            'nombre' => 'Constructor de Tipos de Trámites',
            'icono' => '⚙️',
            'color' => 'purple',
            'permisos' => [
                'tipo_tramites.view' => [
                    'nombre' => 'Ver Tipos de Trámites',
                    'accion' => 'view',
                    'descripcion' => 'Permite ver los esquemas y formularios dinámicos creados.',
                ],
                'tipo_tramites.create' => [
                    'nombre' => 'Crear Tipos de Trámites',
                    'accion' => 'create',
                    'descripcion' => 'Permite diseñar nuevos tipos de trámites dinámicos.',
                ],
                'tipo_tramites.edit' => [
                    'nombre' => 'Editar Tipos de Trámites',
                    'accion' => 'edit',
                    'descripcion' => 'Permite modificar campos dinámicos, tarifas y requisitos.',
                ],
                'tipo_tramites.delete' => [
                    'nombre' => 'Eliminar Tipos de Trámites',
                    'accion' => 'delete',
                    'descripcion' => 'Permite anular o eliminar esquemas de trámites.',
                ],
            ],
        ],
        'users' => [
            'nombre' => 'Gestión de Usuarios',
            'icono' => '👤',
            'color' => 'indigo',
            'permisos' => [
                'users.view' => [
                    'nombre' => 'Ver Usuarios',
                    'accion' => 'view',
                    'descripcion' => 'Permite ver la nómina de usuarios registrados.',
                ],
                'users.create' => [
                    'nombre' => 'Crear Usuarios',
                    'accion' => 'create',
                    'descripcion' => 'Permite crear nuevas cuentas de acceso al sistema.',
                ],
                'users.edit' => [
                    'nombre' => 'Editar Usuarios',
                    'accion' => 'edit',
                    'descripcion' => 'Permite actualizar datos, oficinas, contraseñas y roles.',
                ],
                'users.delete' => [
                    'nombre' => 'Eliminar Usuarios',
                    'accion' => 'delete',
                    'descripcion' => 'Permite dar de baja o eliminar usuarios del sistema.',
                ],
            ],
        ],
        'permisos' => [
            'nombre' => 'Permisos & Seguridad',
            'icono' => '🛡️',
            'color' => 'red',
            'permisos' => [
                'permisos.view' => [
                    'nombre' => 'Ver Matriz de Permisos',
                    'accion' => 'view',
                    'descripcion' => 'Permite consultar el estado de permisos de todos los usuarios.',
                ],
                'permisos.edit' => [
                    'nombre' => 'Configurar Permisos',
                    'accion' => 'edit',
                    'descripcion' => 'Permite asignar o revocar módulos y accesos específicos.',
                ],
                'permisos.manage' => [
                    'nombre' => 'Gestión Total de Seguridad',
                    'accion' => 'manage',
                    'descripcion' => 'Permite restablecer roles, plantillas y auditoría de permisos.',
                ],
            ],
        ],
        'reportes' => [
            'nombre' => 'Reportes & Auditoría Financiera',
            'icono' => '📈',
            'color' => 'teal',
            'permisos' => [
                'reportes.cajas' => [
                    'nombre' => 'Auditoría Global de Cajas',
                    'accion' => 'view',
                    'descripcion' => 'Permite consultar arqueos, cierres y actas de todas las cajas.',
                ],
                'reportes.desempeno' => [
                    'nombre' => 'Desempeño de Cajeros',
                    'accion' => 'view',
                    'descripcion' => 'Permite ver estadísticas de cobros, diferencias y exactitud por usuario.',
                ],
                'reportes.cartera' => [
                    'nombre' => 'Reportería de Cartera',
                    'accion' => 'view',
                    'descripcion' => 'Permite consultar morosidad y exportar reportes de deudores.',
                ],
                'reportes.control_precios' => [
                    'nombre' => 'Control de Precios (±5%)',
                    'accion' => 'view',
                    'descripcion' => 'Permite auditar trámites facturados con desviaciones de tarifas.',
                ],
            ],
        ],
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'permissions' => 'array',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    /**
     * Accessor for id_usuario attribute compatibility.
     */
    public function getIdUsuarioAttribute()
    {
        return $this->attributes['id'] ?? $this->id;
    }

    public function cajaSesiones()
    {
        return $this->hasMany(CajaSesion::class, 'user_id');
    }

    public function cajaMovimientos()
    {
        return $this->hasMany(CajaMovimiento::class, 'user_id');
    }

    /**
     * Obtains the currently active open cash session for this user, if any.
     */
    public function cajaAbierta()
    {
        return $this->cajaSesiones()->where('estado', 'abierta')->latest()->first();
    }

    public function logins()
    {
        return $this->hasMany(LoginData::class, 'id_usuario');
    }

    /**
     * Returns true if user has an open cash session.
     */
    public function hasCajaAbierta(): bool
    {
        return $this->cajaSesiones()->where('estado', 'abierta')->exists();
    }

    /**
     * Obtener los permisos por defecto según el rol del usuario.
     */
    public static function getRoleDefaultPermissions(string $role): array
    {
        if ($role === 'Administrador') {
            $all = [];
            foreach (self::AVAILABLE_PERMISSIONS as $group) {
                foreach (array_keys($group['permisos']) as $key) {
                    $all[] = $key;
                }
            }
            return $all;
        }

        if ($role === 'Supervisor') {
            return [
                'dashboard.view', 'dashboard.export', 'dashboard.ia',
                'clientes.view', 'clientes.create', 'clientes.edit', 'clientes.abonar', 'clientes.export',
                'cartera.view', 'cartera.create', 'cartera.edit', 'cartera.cobrar',
                'cajas.view', 'cajas.create', 'cajas.edit', 'cajas.operar', 'cajas.historial',
                'tramites_global.view', 'tramites_global.create', 'tramites_global.edit', 'tramites_global.export',
                'poderes.view', 'poderes.create', 'poderes.edit',
                'divorcios.view', 'divorcios.create', 'divorcios.edit',
                'impuestos.view', 'impuestos.create', 'impuestos.edit',
                'tramites_varios.view', 'tramites_varios.create', 'tramites_varios.edit',
                'tramites_personalizados.view', 'tramites_personalizados.create', 'tramites_personalizados.edit',
                'plantillas.view', 'plantillas.create', 'plantillas.edit', 'plantillas.generar',
                'membretes.view', 'membretes.create', 'membretes.edit',
                'bancos.view', 'bancos.create', 'bancos.edit',
                'tarjetas.view', 'tarjetas.create', 'tarjetas.edit',
                'oficinas.view',
                'tipo_tramites.view', 'tipo_tramites.create', 'tipo_tramites.edit',
                'reportes.cajas', 'reportes.desempeno', 'reportes.cartera', 'reportes.control_precios',
            ];
        }

        // Empleado / Asesor / Cajero por defecto
        return [
            'dashboard.view',
            'clientes.view', 'clientes.create', 'clientes.edit', 'clientes.abonar',
            'cartera.view', 'cartera.cobrar',
            'cajas.view', 'cajas.create', 'cajas.operar', 'cajas.historial',
            'tramites_global.view', 'tramites_global.create',
            'poderes.view', 'poderes.create',
            'divorcios.view', 'divorcios.create',
            'impuestos.view', 'impuestos.create',
            'tramites_varios.view', 'tramites_varios.create',
            'tramites_personalizados.view', 'tramites_personalizados.create',
            'plantillas.view', 'plantillas.generar',
        ];
    }

    /**
     * Retorna la lista efectiva de claves de permisos que tiene el usuario.
     * Si el usuario tiene una matriz explícitamente guardada (array), se respeta esa matriz.
     * Si no tiene matriz personalizada guardada (null), se usan los permisos por defecto de su rol.
     */
    public function getEffectivePermissions(): array
    {
        $perms = $this->permissions;

        // Si viene como string JSON (ej. si no pasó por cast o raw query), decodificarlo
        if (is_string($perms)) {
            $decoded = json_decode($perms, true);
            if (is_array($decoded)) {
                $perms = $decoded;
            }
        }

        if (is_array($perms)) {
            return $perms;
        }

        return self::getRoleDefaultPermissions($this->role ?? 'Empleado');
    }

    /**
     * Verifica si el usuario tiene permiso para una clave específica.
     */
    public function hasPermission(string|array $permission): bool
    {
        // Si el usuario es Administrador y NO tiene matriz personalizada restrictiva, tiene acceso a todo
        if ($this->role === 'Administrador' && $this->permissions === null) {
            return true;
        }

        $effective = $this->getEffectivePermissions();

        // Wildcard superadmin
        if (in_array('*', $effective, true)) {
            return true;
        }

        if (is_array($permission)) {
            foreach ($permission as $p) {
                if ($this->checkSinglePermission($p, $effective)) {
                    return true;
                }
            }
            return false;
        }

        return $this->checkSinglePermission($permission, $effective);
    }

    /**
     * Comprueba un permiso individual con soporte para aliases y comodines (.all, .manage, etc.)
     */
    protected function checkSinglePermission(string $perm, array $effective): bool
    {
        // Coincidencia exacta
        if (in_array($perm, $effective, true)) {
            return true;
        }

        // Mapeo / soporte de comodín [modulo].all o [modulo].*
        $parts = explode('.', $perm, 2);
        $module = $parts[0] ?? '';
        $action = $parts[1] ?? '';

        if ($module && (in_array("{$module}.all", $effective, true) || in_array("{$module}.*", $effective, true))) {
            return true;
        }

        // Aliases para compatibilidad con código existente:
        // 1. *.manage alias
        if ($action === 'manage') {
            if (in_array("{$module}.edit", $effective, true) || in_array("{$module}.create", $effective, true) || in_array("{$module}.view", $effective, true)) {
                return true;
            }
        }
        if ($perm === 'tramites.view_all' && (in_array('tramites_global.view', $effective, true) || in_array('tramites.view_all', $effective, true))) {
            return true;
        }
        if ($perm === 'tramites.create' && (
            in_array('tramites_global.create', $effective, true) || 
            in_array('tramites.create', $effective, true) || 
            in_array('poderes.create', $effective, true) || 
            in_array('divorcios.create', $effective, true) || 
            in_array('impuestos.create', $effective, true) || 
            in_array('tramites_varios.create', $effective, true) || 
            in_array('tramites_personalizados.create', $effective, true)
        )) {
            return true;
        }
        if ($perm === 'plantillas.manage' && (in_array('plantillas.edit', $effective, true) || in_array('plantillas.create', $effective, true))) {
            return true;
        }
        if ($perm === 'permisos.manage' && (in_array('permisos.edit', $effective, true) || in_array('permisos.manage', $effective, true))) {
            return true;
        }

        return false;
    }
}
