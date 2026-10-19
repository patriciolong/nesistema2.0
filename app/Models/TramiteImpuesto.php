<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TramiteImpuesto extends Model
{
    use HasFactory;

    protected $table = 'tramite_impuestos';
    protected $primaryKey = 'id_tram_impuestos';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'ti_fecha',
        'ti_itin',
        'ti_fechain',
        'ti_nitin',
        'ti_ecivil',
        'ti_dependientes',
        'ti_mpago',
        'ti_banco',
        'ti_ncuenta',
        'ti_nruta',
        'ti_observacion',
        'id_usuario',
        'ti_profesion',
        'ti_anio_reporte',
        'ti_oficina',
        'ti_costo_tramite',
        'ti_abono_tramite',
        'ti_saldo',
        'ti_firmar_en',
        'estado',
        'documento_pdf',
        'fecha_completado',
        'notificado_at',
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
