<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoGenerado extends Model
{
    protected $table = 'documentos_generados';

    protected $fillable = [
        'id_cliente',
        'plantilla_id',
        'titulo_documento',
        'contenido_html',
        'usuario_creador',
        'estado',
        'documento_pdf',
        'fecha_completado',
        'notificado_at',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(Plantilla::class, 'plantilla_id', 'id_plantilla');
    }
}
