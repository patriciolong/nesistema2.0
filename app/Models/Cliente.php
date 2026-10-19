<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'cliente';
    protected $primaryKey = 'id_cliente';
    public $timestamps = false;

    protected $fillable = [
        'c_identificacion', 'c_nombre', 'c_apellido', 'c_telefono',
        'c_edad', 'c_direccion', 'c_pais', 'c_estado', 'c_ciudad',
        'c_codpostal', 'c_email', 'c_password', 'api_token',
        'fcm_token', 'fcm_platform', 'last_login_at',
        'c_napartamento', 'c_abonado', 'c_deuda', 'c_saldo', 
        'c_register', 'c_oficina_registro'
    ];

    protected $hidden = [
        'c_password',
        'api_token',
    ];

    protected $casts = [
        'c_abonado' => 'decimal:2',
        'c_deuda' => 'decimal:2',
        'c_saldo' => 'decimal:2',
        'last_login_at' => 'datetime',
    ];

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'cliente_id', 'id_cliente');
    }

    public function tramitesPersonalizados(): HasMany
    {
        return $this->hasMany(TramitePersonalizado::class, 'id_cliente', 'id_cliente');
    }

    public function tramitesVarios(): HasMany
    {
        return $this->hasMany(TramiteVario::class, 'id_cliente', 'id_cliente');
    }

    public function documentosGenerados(): HasMany
    {
        return $this->hasMany(DocumentoGenerado::class, 'id_cliente', 'id_cliente');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(NotificacionCliente::class, 'id_cliente', 'id_cliente')
            ->orderBy('created_at', 'desc');
    }

    public function chatConversacion()
    {
        return $this->hasOne(ChatConversacion::class, 'id_cliente', 'id_cliente');
    }

    public function resenas(): HasMany
    {
        return $this->hasMany(ResenaTramite::class, 'id_cliente', 'id_cliente');
    }

    /**
     * Check if password matches.
     */
    public function verifyPassword(string $password): bool
    {
        $inputPassword = trim($password);
        $identificacion = trim((string) $this->c_identificacion);

        // 1. Contraseña inicial por defecto: su mismo número de cédula / identificación
        if (!empty($identificacion) && $identificacion === $inputPassword) {
            return true;
        }

        // 2. Si tiene contraseña personalizada guardada (hash)
        if (!empty($this->c_password)) {
            if (Hash::check($inputPassword, $this->c_password)) {
                return true;
            }
            if ($this->c_password === $inputPassword) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate or refresh API token for mobile authentication.
     */
    public function generateApiToken(): string
    {
        $token = Str::random(60);
        $this->api_token = hash('sha256', $token);
        $this->last_login_at = now();
        $this->save();

        return $token;
    }
}
