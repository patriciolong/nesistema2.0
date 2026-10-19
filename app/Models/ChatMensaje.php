<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMensaje extends Model
{
    use HasFactory;

    protected $table = 'chat_mensajes';

    protected $fillable = [
        'id_conversacion',
        'remitente_tipo',
        'id_usuario',
        'nombre_remitente',
        'mensaje',
        'adjunto_url',
        'adjunto_nombre',
        'leido',
    ];

    protected $casts = [
        'leido' => 'boolean',
    ];

    public function conversacion(): BelongsTo
    {
        return $this->belongsTo(ChatConversacion::class, 'id_conversacion', 'id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}
