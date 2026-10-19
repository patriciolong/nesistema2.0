<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TramitePoder extends Model
{
    use HasFactory;

    protected $table = 'tramite_poderes';
    protected $primaryKey = 'id_tram_poderes';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente', 'tp_oficina', 'tp_fecha', 'tp_estado_civil',
        'tp_firmar_en', 'tp_nombres_otorga_poder', 'tp_cedulla_otorga_poder',
        'tp_nombres_otorga_poder2', 'tp_cedulla_otorga_poder2', 'tp_razon_otorga_poder',
        'tp_opcion_envio_poder', 'tp_enviar_nombrede', 'tp_ciudad_enviar',
        'tp_provincia', 'tp_telefonos_enviar', 'id_usuario', 'tp_observaciones',
        'tp_costo_tramite', 'tp_abono_tramite', 'tp_saldo', 'tp_nombre2',
        'tp_apellido2', 'tp_identificacion2', 'tp_telefono2',
        'estado', 'documento_pdf', 'fecha_completado', 'notificado_at'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}
