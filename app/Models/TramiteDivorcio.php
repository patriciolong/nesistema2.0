<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TramiteDivorcio extends Model
{
    use HasFactory;

    protected $table = 'tramite_divorcio';
    protected $primaryKey = 'id_tram_div';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente', 'td_controvertido', 'td_consensual', 'td_notarial',
        'td_identificacion_c', 'td_nombre_c', 'td_direccion_c', 'td_telefono_c',
        'td_estado_c', 'td_ciudad_c', 'td_apt_c', 'td_cpostal_c',
        'td_lugar_matrimonio', 'td_fecha_matrimonio', 'td_separados', 'td_noseparados',
        'td_tiempo_separacion', 'td_hijos', 'td_ep_matrimonio', 'td_ep_nacimiento',
        'td_estado_contac_ecuador', 'td_tel_ecuador', 'td_observaciones', 'td_mpago',
        'td_valor', 'td_abono', 'td_saldo', 'id_usuario', 'td_oficina', 'td_fecha',
        'td_motivo_divorcio', 'td_con_quien_vive', 'td_firmar_en',
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
