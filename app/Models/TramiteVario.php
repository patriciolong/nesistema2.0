<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TramiteVario extends Model
{
    use HasFactory;

    protected $table = 'tramites_varios';
    protected $primaryKey = 'id_tramite_varios';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente', 'tv_motivo', 'tv_oenvio', 'tv_nom_envio',
        'tv_ciudad', 'tv_provincia', 'tv_telefono', 'id_usuario', 'tv_tip_documento',
        'tv_traducciones', 'tv_notarizacion', 'tv_certificacion', 'tv_apostilla',
        'tv_valor_tramite', 'tv_abono_tramite', 'tv_saldo', 'tv_observaciones',
        'tv_oficina', 'tv_fecha', 'tv_razon_t', 'tv_firmar_en',
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
