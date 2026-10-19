<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TramitePersonalizado extends Model
{
    protected $table = 'tramites_personalizados';

    protected $fillable = [
        'tipo_tramite_id',
        'id_cliente',
        'datos_formulario',
        'valor_tramite',
        'abono_tramite',
        'saldo',
        'observaciones',
        'fecha',
        'usuario',
        'oficina',
        'estado',
        'documento_pdf',
        'fecha_completado',
        'notificado_at',
    ];

    protected $casts = [
        'datos_formulario' => 'array',
        'valor_tramite' => 'decimal:2',
        'abono_tramite' => 'decimal:2',
        'saldo' => 'decimal:2',
        'fecha' => 'date',
    ];

    public function tipoTramite(): BelongsTo
    {
        return $this->belongsTo(TipoTramite::class, 'tipo_tramite_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
